<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\GlossaryTerm;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Post;
use App\Models\Team;
use App\Models\User;
use App\Services\QuestionRecommendationService;
use App\Services\QuestionToolGuesser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditAnswer;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\AuditPro\Models\AuditTemplate;
use Modules\BookIntelligence\Models\Author;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\QuestionMapping;

class McpController extends Controller
{
    /**
     * Handle incoming MCP Requests (GET SSE/discovery, OPTIONS CORS, POST JSON-RPC).
     */
    public function handle(Request $request): JsonResponse|Response|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        // 1. Handle CORS Preflight
        if ($request->isMethod('OPTIONS')) {
            return response('', 200, $this->corsHeaders());
        }

        // 2. Authenticate optional/provided token
        $this->authenticateRequest($request);

        // 3. Handle GET request (Default to SSE Stream for Claude / MCP clients)
        if ($request->isMethod('GET')) {
            if ($request->query('format') === 'json' || ($request->expectsJson() && ! str_contains($request->header('Accept', ''), 'text/event-stream'))) {
                return response()->json([
                    'name' => 'Allocore Enterprise MCP Server',
                    'version' => '1.0.0',
                    'protocolVersion' => '2024-11-05',
                    'status' => 'operational',
                    'authenticated' => Auth::check(),
                    'user' => Auth::user()?->name,
                    'capabilities' => [
                        'tools' => ['listChanged' => false],
                        'resources' => ['subscribe' => false, 'listChanged' => false],
                        'prompts' => ['listChanged' => false],
                    ],
                    'endpoints' => [
                        'rpc' => url('/api/mcp/rpc'),
                        'sse' => url('/api/mcp'),
                        'tools' => url('/api/mcp/tools'),
                    ],
                ], 200, $this->corsHeaders());
            }

            return $this->handleSseStream($request);
        }

        // 4. Handle POST JSON-RPC Request
        return $this->handleRpc($request);
    }

    /**
     * JSON-RPC 2.0 / MCP Protocol Handler.
     */
    public function handleRpc(Request $request): JsonResponse|Response
    {
        if ($request->isMethod('OPTIONS')) {
            return response('', 200, $this->corsHeaders());
        }

        $this->authenticateRequest($request);

        $payload = $request->json()->all();
        $method = $payload['method'] ?? $request->input('method');
        $params = $payload['params'] ?? $request->input('params', []);
        $id = $payload['id'] ?? $request->input('id', 1);

        // Notifications (methods without id)
        if ($id === null && ($method === 'notifications/initialized' || str_starts_with($method ?? '', 'notifications/'))) {
            return response()->json(['status' => 'ok'], 200, $this->corsHeaders());
        }

        try {
            $result = match ($method) {
                // MCP Standard Handshake
                'initialize' => $this->handleInitialize($params),
                'ping' => ['status' => 'pong'],

                // MCP Protocol Core
                'tools/list' => $this->listTools(),
                'tools/call' => $this->formatToolCallResponse($this->callTool($params['name'] ?? '', $params['arguments'] ?? [])),
                'resources/list' => $this->listResources(),
                'resources/read' => $this->readResource($params['uri'] ?? ''),
                'prompts/list' => $this->listPrompts(),
                'prompts/get' => $this->getPrompt($params['name'] ?? '', $params['arguments'] ?? []),

                // Direct tool execution fallback
                default => $this->formatToolCallResponse($this->callTool($method ?? '', $params)),
            };

            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result,
            ], 200, $this->corsHeaders());
        } catch (\Throwable $e) {
            if ($method === 'tools/call') {
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Error executing tool: '.$e->getMessage(),
                            ],
                        ],
                        'isError' => true,
                    ],
                ], 200, $this->corsHeaders());
            }

            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32603,
                    'message' => $e->getMessage(),
                ],
            ], 200, $this->corsHeaders());
        }
    }

    /**
     * Format tool execution output into official MCP standard content envelope.
     */
    protected function formatToolCallResponse(mixed $output): array
    {
        $text = is_string($output) ? $output : json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => $text,
                ],
            ],
            'isError' => false,
        ];
    }

    /**
     * MCP Initialize Handshake Response.
     */
    protected function handleInitialize(array $params): array
    {
        return [
            'protocolVersion' => $params['protocolVersion'] ?? '2024-11-05',
            'capabilities' => [
                'tools' => [
                    'listChanged' => false,
                ],
                'resources' => [
                    'subscribe' => false,
                    'listChanged' => false,
                ],
                'prompts' => [
                    'listChanged' => false,
                ],
            ],
            'serverInfo' => [
                'name' => 'Allocore Enterprise Server',
                'version' => '1.0.0',
            ],
        ];
    }

    /**
     * Authenticate via Bearer, X-Api-Key, X-Allocore-Token, or query token.
     */
    protected function authenticateRequest(Request $request): void
    {
        if (Auth::check()) {
            return;
        }

        $tokenStr = null;

        // 1. Header: Authorization: Bearer <token>
        $header = $request->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            $tokenStr = substr($header, 7);
        }

        // 2. Header: X-Api-Key or X-Allocore-Token
        if (! $tokenStr) {
            $tokenStr = $request->header('X-Api-Key') ?: $request->header('X-Allocore-Token');
        }

        // 3. Query Param: ?token=... or ?api_token=...
        if (! $tokenStr) {
            $tokenStr = $request->query('token') ?: $request->query('api_token');
        }

        if ($tokenStr) {
            $apiToken = ApiToken::with('user')->get()->first(function ($t) use ($tokenStr) {
                return Hash::check($tokenStr, $t->token);
            });

            if ($apiToken && ! $apiToken->isExpired() && $apiToken->user) {
                $apiToken->markAsUsed();
                Auth::login($apiToken->user);
                $request->attributes->set('api_token', $apiToken);
            }
        }
    }

    /**
     * CORS Headers for Remote MCP Clients (Claude, Cursor).
     */
    protected function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Api-Key, X-Allocore-Token, Accept, Origin, X-Requested-With, Cache-Control',
            'Access-Control-Max-Age' => '86400',
        ];
    }

    /**
     * Handle SSE Stream for Claude / MCP Remote Connectors.
     */
    protected function handleSseStream(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $queryString = $request->getQueryString();
        $rpcUrl = url('/api/mcp/rpc').($queryString ? '?'.$queryString : '');

        $headers = array_merge($this->corsHeaders(), [
            'Content-Type' => 'text/event-stream; charset=utf-8',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);

        return response()->stream(function () use ($rpcUrl) {
            // 1. Emit MCP endpoint event immediately
            echo "event: endpoint\n";
            echo "data: ".$rpcUrl."\n\n";

            if (ob_get_level() > 0) {
                @ob_flush();
            }
            @flush();

            // 2. Keep stream open for Claude Web / MCP SSE listener
            $start = time();
            while (time() - $start < 25) {
                if (connection_aborted()) {
                    break;
                }
                sleep(2);
                echo ": keepalive\n\n";

                if (ob_get_level() > 0) {
                    @ob_flush();
                }
                @flush();
            }
        }, 200, $headers);
    }

    /**
     * List all available MCP tools with schemas.
     */
    public function listTools(): array
    {
        return [
            'tools' => [
                // 1. Audit & Questions
                [
                    'name' => 'list_audit_questions',
                    'description' => 'List and filter audit questions by pillar, keyword, or missing solutions.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'pillar' => ['type' => 'string', 'description' => 'Filter by pillar name (e.g. Revenue, Profit, Order)'],
                            'search' => ['type' => 'string', 'description' => 'Search query in question text'],
                            'missing_solution_only' => ['type' => 'boolean', 'description' => 'Only return questions without assigned tools/books'],
                            'limit' => ['type' => 'integer', 'default' => 50],
                        ],
                    ],
                ],
                [
                    'name' => 'get_question_details',
                    'description' => 'Retrieve complete details, assigned tools, books, articles, and glossary terms for a question.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'question_id' => ['type' => 'integer', 'description' => 'Audit question ID'],
                        ],
                        'required' => ['question_id'],
                    ],
                ],
                [
                    'name' => 'assign_question_solution',
                    'description' => 'Assign or update recommended platform tool, book, article, or glossary term for an audit question.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'question_id' => ['type' => 'integer'],
                            'module_key' => ['type' => 'string', 'description' => 'e.g. revenue-planner, cash-core, lead-quality'],
                            'book_id' => ['type' => 'integer'],
                            'post_id' => ['type' => 'integer'],
                            'knowledge_slug' => ['type' => 'string', 'description' => 'e.g. fixkosten, cash-flow'],
                            'failure_recommendation' => ['type' => 'string'],
                        ],
                        'required' => ['question_id'],
                    ],
                ],
                [
                    'name' => 'batch_auto_match_questions',
                    'description' => 'AI heuristic mass scanner that auto-assigns optimal tools, books, and terms to all unassigned questions.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'dry_run' => ['type' => 'boolean', 'default' => false],
                        ],
                    ],
                ],
                [
                    'name' => 'list_audit_templates',
                    'description' => 'List all audit templates with their pillars and question counts.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],
                [
                    'name' => 'create_or_update_question',
                    'description' => 'Create a new audit question or update an existing one.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'question_id' => ['type' => 'integer'],
                            'pillar_id' => ['type' => 'integer'],
                            'question' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'question_type' => ['type' => 'string', 'enum' => ['yes_no', 'scale_1_to_5', 'radio', 'checkbox']],
                            'failure_recommendation' => ['type' => 'string'],
                        ],
                        'required' => ['question', 'pillar_id'],
                    ],
                ],

                // 2. Module & Tool Pool Governance
                [
                    'name' => 'list_all_modules',
                    'description' => 'List all platform modules with tool pool status, categories, icons, and route prefixes.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'category' => ['type' => 'string'],
                            'only_pool' => ['type' => 'boolean'],
                            'only_active' => ['type' => 'boolean'],
                        ],
                    ],
                ],
                [
                    'name' => 'manage_tool_pool',
                    'description' => 'Add or remove a module from the customer subscription tool pool.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'module_key' => ['type' => 'string'],
                            'in_pool' => ['type' => 'boolean'],
                        ],
                        'required' => ['module_key', 'in_pool'],
                    ],
                ],
                [
                    'name' => 'deprecate_module',
                    'description' => 'Mark a module as deprecated/archived or restore it to active status.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'module_key' => ['type' => 'string'],
                            'is_deprecated' => ['type' => 'boolean', 'default' => true],
                        ],
                        'required' => ['module_key'],
                    ],
                ],
                [
                    'name' => 'update_module_metadata',
                    'description' => 'Update display name, description, category, modern icon, badge, or sort order of a tool.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'module_key' => ['type' => 'string'],
                            'name' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'category' => ['type' => 'string'],
                            'icon' => ['type' => 'string'],
                            'badge_text' => ['type' => 'string'],
                            'sort_order' => ['type' => 'integer'],
                        ],
                        'required' => ['module_key'],
                    ],
                ],
                [
                    'name' => 'sync_subscription_plans',
                    'description' => 'Synchronize all active pool tools to the All Tools Bundle plan.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],

                // 3. Allocore Coach & Diagnostics
                [
                    'name' => 'diagnose_audit_gaps',
                    'description' => 'Diagnose a completed client audit and extract prioritized gaps based on the 5-pillar pyramid.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'audit_id' => ['type' => 'integer'],
                        ],
                        'required' => ['audit_id'],
                    ],
                ],
                [
                    'name' => 'calculate_pillar_scores',
                    'description' => 'Calculate percentage score breakdown for Revenue, Profit, Order, Influence, and Legacy.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'audit_id' => ['type' => 'integer'],
                        ],
                        'required' => ['audit_id'],
                    ],
                ],
                [
                    'name' => 'generate_action_plan',
                    'description' => 'Generate a structured 90-day step-by-step master action plan for a client.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'audit_id' => ['type' => 'integer'],
                        ],
                        'required' => ['audit_id'],
                    ],
                ],

                // 4. Books & Library
                [
                    'name' => 'search_books',
                    'description' => 'Search business books in the BookIntelligence library by title, author, or keyword.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string'],
                            'limit' => ['type' => 'integer', 'default' => 20],
                        ],
                    ],
                ],
                [
                    'name' => 'get_book_details',
                    'description' => 'Retrieve full information, cover URL, author, and audit mappings for a book.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'book_id' => ['type' => 'integer'],
                        ],
                        'required' => ['book_id'],
                    ],
                ],
                [
                    'name' => 'create_or_update_book',
                    'description' => 'Add or update a book in the BookIntelligence library.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'book_id' => ['type' => 'integer'],
                            'author_name' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'cover_url' => ['type' => 'string'],
                            'affiliate_link' => ['type' => 'string'],
                        ],
                        'required' => ['title'],
                    ],
                ],

                // 5. Knowledge & Glossary
                [
                    'name' => 'search_glossary_terms',
                    'description' => 'Search business terms and explanations in the Allocore Glossary.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string'],
                            'pillar' => ['type' => 'string'],
                            'limit' => ['type' => 'integer', 'default' => 30],
                        ],
                    ],
                ],
                [
                    'name' => 'create_or_update_glossary_term',
                    'description' => 'Create or update a business glossary term.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'term' => ['type' => 'string'],
                            'slug' => ['type' => 'string'],
                            'definition' => ['type' => 'string'],
                            'simple_definition' => ['type' => 'string'],
                            'pillar' => ['type' => 'string', 'default' => 'Revenue'],
                        ],
                        'required' => ['term', 'slug', 'definition'],
                    ],
                ],

                // 6. Blog & CMS Content
                [
                    'name' => 'search_blog_posts',
                    'description' => 'Search published blog and expert articles.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string'],
                            'limit' => ['type' => 'integer', 'default' => 20],
                        ],
                    ],
                ],
                [
                    'name' => 'get_post_details',
                    'description' => 'Retrieve full content body, featured image, excerpt, and metadata of a specific blog post.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'post_id' => ['type' => 'integer', 'description' => 'ID of the blog post'],
                        ],
                        'required' => ['post_id'],
                    ],
                ],
                [
                    'name' => 'create_or_update_post',
                    'description' => 'Create a new blog post or safely update specific fields of an existing post (e.g. featured_image, title, slug, body, excerpt) without wiping content.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'post_id' => ['type' => 'integer', 'description' => 'If provided, performs a safe partial update on this post'],
                            'title' => ['type' => 'string'],
                            'slug' => ['type' => 'string'],
                            'body' => ['type' => 'string'],
                            'excerpt' => ['type' => 'string'],
                            'featured_image' => ['type' => 'string', 'description' => 'URL or path to featured image graphic'],
                            'category' => ['type' => 'string', 'description' => 'Category name, e.g. Methodik & Strategie, Performance Marketing, SEO'],
                            'tags' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Array of tag strings e.g. ["SEO", "SEA", "Growth"]'],
                            'meta_title' => ['type' => 'string'],
                            'meta_description' => ['type' => 'string'],
                            'is_published' => ['type' => 'boolean', 'default' => true],
                            'is_featured' => ['type' => 'boolean'],
                        ],
                    ],
                ],

                // 7. User & Team Management
                [
                    'name' => 'search_users_and_teams',
                    'description' => 'Search registered users, accounts, and teams.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string'],
                            'limit' => ['type' => 'integer', 'default' => 20],
                        ],
                    ],
                ],
                [
                    'name' => 'get_user_subscription_status',
                    'description' => 'Inspect active plans, subscription status, and module permissions for a user.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer'],
                        ],
                        'required' => ['user_id'],
                    ],
                ],

                // 8. Analytics & DevOps
                [
                    'name' => 'get_platform_metrics',
                    'description' => 'Retrieve platform-wide live statistics (users, audits, active tools, books).',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],
                [
                    'name' => 'run_allocore_artisan',
                    'description' => 'Execute safe maintenance commands (cache:clear, view:clear, route:clear, migrate:status).',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'command' => ['type' => 'string', 'description' => 'cache:clear, view:clear, route:clear, optimize:clear, migrate:status'],
                        ],
                        'required' => ['command'],
                    ],
                ],
                [
                    'name' => 'get_system_health',
                    'description' => 'Check system health, database connection, and PHP runtime environment.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],
            ],
        ];
    }

    /**
     * Dispatch tool call by name.
     */
    public function callTool(string $name, array $arguments): array
    {
        return match ($name) {
            'list_audit_questions' => $this->toolListAuditQuestions($arguments),
            'get_question_details' => $this->toolGetQuestionDetails($arguments),
            'assign_question_solution' => $this->toolAssignQuestionSolution($arguments),
            'batch_auto_match_questions' => $this->toolBatchAutoMatchQuestions($arguments),
            'list_audit_templates' => $this->toolListAuditTemplates(),
            'create_or_update_question' => $this->toolCreateOrUpdateQuestion($arguments),
            'list_all_modules' => $this->toolListAllModules($arguments),
            'manage_tool_pool' => $this->toolManageToolPool($arguments),
            'deprecate_module' => $this->toolDeprecateModule($arguments),
            'update_module_metadata' => $this->toolUpdateModuleMetadata($arguments),
            'sync_subscription_plans' => $this->toolSyncSubscriptionPlans(),
            'diagnose_audit_gaps' => $this->toolDiagnoseAuditGaps($arguments),
            'calculate_pillar_scores' => $this->toolCalculatePillarScores($arguments),
            'generate_action_plan' => $this->toolGenerateActionPlan($arguments),
            'search_books' => $this->toolSearchBooks($arguments),
            'get_book_details' => $this->toolGetBookDetails($arguments),
            'create_or_update_book' => $this->toolCreateOrUpdateBook($arguments),
            'search_glossary_terms' => $this->toolSearchGlossaryTerms($arguments),
            'create_or_update_glossary_term' => $this->toolCreateOrUpdateGlossaryTerm($arguments),
            'search_blog_posts' => $this->toolSearchBlogPosts($arguments),
            'get_post_details' => $this->toolGetPostDetails($arguments),
            'create_or_update_post' => $this->toolCreateOrUpdatePost($arguments),
            'search_users_and_teams' => $this->toolSearchUsersAndTeams($arguments),
            'get_user_subscription_status' => $this->toolGetUserSubscriptionStatus($arguments),
            'get_platform_metrics' => $this->toolGetPlatformMetrics(),
            'run_allocore_artisan' => $this->toolRunAllocoreArtisan($arguments),
            'get_system_health' => $this->toolGetSystemHealth(),
            default => throw new \InvalidArgumentException("Tool '{$name}' is not recognized."),
        };
    }

    // --- Individual Tool Implementations ---

    protected function toolListAuditQuestions(array $args): array
    {
        $query = AuditQuestion::withoutGlobalScope('current_team')->with(['pillar']);

        if (! empty($args['pillar'])) {
            $query->whereHas('pillar', fn ($q) => $q->where('name', 'like', "%{$args['pillar']}%"));
        }
        if (! empty($args['search'])) {
            $s = $args['search'];
            $query->where(fn ($q) => $q->where('question', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"));
        }
        if (! empty($args['missing_solution_only'])) {
            $query->where(fn ($q) => $q->whereNull('recommended_module_key')->orWhere('recommended_module_key', ''))
                  ->whereNull('recommended_book_id');
        }

        $limit = min(500, max(1, (int) ($args['limit'] ?? 50)));
        $questions = $query->orderBy('id')->limit($limit)->get();

        return [
            'total' => $questions->count(),
            'questions' => $questions->map(fn ($q) => [
                'id' => $q->id,
                'pillar' => $q->pillar?->name,
                'question' => $q->getRawOriginal('question'),
                'description' => $q->getRawOriginal('description'),
                'question_type' => $q->question_type,
                'recommended_module_key' => $q->recommended_module_key,
                'recommended_book_id' => $q->recommended_book_id,
                'recommended_post_id' => $q->recommended_post_id,
                'knowledge_slug' => $q->knowledge_slug,
                'failure_recommendation' => $q->failure_recommendation,
                'is_active' => $q->is_active !== null ? (bool) $q->is_active : true,
            ]),
        ];
    }

    protected function toolGetQuestionDetails(array $args): array
    {
        $q = AuditQuestion::withoutGlobalScope('current_team')
            ->with(['pillar.template'])
            ->findOrFail($args['question_id']);

        $book = $q->recommended_book_id && class_exists(Book::class) ? Book::find($q->recommended_book_id) : null;
        $post = $q->recommended_post_id ? Post::find($q->recommended_post_id) : null;
        $term = $q->knowledge_slug ? GlossaryTerm::where('slug', $q->knowledge_slug)->first() : null;
        $tool = $q->recommended_module_key ? Module::byKey($q->recommended_module_key) : null;

        return [
            'question' => [
                'id' => $q->id,
                'pillar' => $q->pillar?->name,
                'template' => $q->pillar?->template?->name,
                'question' => $q->getRawOriginal('question'),
                'description' => $q->getRawOriginal('description'),
                'options' => $q->options,
                'failure_recommendation' => $q->failure_recommendation,
            ],
            'assigned_tool' => $tool ? ['key' => $tool->key, 'name' => $tool->name, 'route' => '/app/'.$tool->route_prefix] : null,
            'assigned_book' => $book ? ['id' => $book->id, 'title' => $book->title, 'cover_url' => $book->cover_url] : null,
            'assigned_article' => $post ? ['id' => $post->id, 'title' => $post->title, 'slug' => $post->slug] : null,
            'assigned_glossary_term' => $term ? ['slug' => $term->slug, 'term' => $term->term, 'simple_definition' => $term->simple_definition] : null,
        ];
    }

    protected function toolAssignQuestionSolution(array $args): array
    {
        $q = AuditQuestion::withoutGlobalScope('current_team')->findOrFail($args['question_id']);

        $data = [];
        if (array_key_exists('module_key', $args)) $data['recommended_module_key'] = $args['module_key'] ?: null;
        if (array_key_exists('book_id', $args)) $data['recommended_book_id'] = $args['book_id'] ?: null;
        if (array_key_exists('post_id', $args)) $data['recommended_post_id'] = $args['post_id'] ?: null;
        if (array_key_exists('knowledge_slug', $args)) $data['knowledge_slug'] = $args['knowledge_slug'] ?: null;
        if (array_key_exists('failure_recommendation', $args)) $data['failure_recommendation'] = $args['failure_recommendation'] ?: null;

        $q->update($data);

        return [
            'status' => 'success',
            'question_id' => $q->id,
            'updated' => $data,
        ];
    }

    protected function toolBatchAutoMatchQuestions(array $args): array
    {
        $dryRun = ! empty($args['dry_run']);
        $questions = AuditQuestion::withoutGlobalScope('current_team')
            ->with(['pillar'])
            ->where(fn ($q) => $q->whereNull('recommended_module_key')->orWhere('recommended_module_key', '')->orWhereNull('recommended_book_id'))
            ->get();

        $matched = [];
        foreach ($questions as $q) {
            $text = $q->question.' '.$q->description;
            $tool = $q->recommended_module_key ?: QuestionToolGuesser::guess($text, $q->pillar?->name ?? '');

            $bookId = $q->recommended_book_id;
            if (! $bookId && class_exists(Book::class)) {
                if (preg_match('/(umsatz|businessplan|plan|revenue)/iu', $text)) {
                    $bookId = Book::where('title', 'like', '%Business%')->orWhere('title', 'like', '%Plan%')->value('id');
                } elseif (preg_match('/(cash|liquidit|profit)/iu', $text)) {
                    $bookId = Book::where('title', 'like', '%Profit%')->orWhere('title', 'like', '%Cash%')->value('id');
                }
            }

            $matched[] = [
                'id' => $q->id,
                'question' => $q->getRawOriginal('question'),
                'tool' => $tool,
                'book_id' => $bookId,
            ];

            if (! $dryRun) {
                $q->update([
                    'recommended_module_key' => $q->recommended_module_key ?: $tool,
                    'recommended_book_id' => $q->recommended_book_id ?: $bookId,
                ]);
            }
        }

        return [
            'total_analyzed' => $questions->count(),
            'total_matched' => count($matched),
            'dry_run' => $dryRun,
            'samples' => array_slice($matched, 0, 10),
        ];
    }

    protected function toolListAuditTemplates(): array
    {
        $templates = AuditTemplate::with(['pillars.questions'])->get();

        return [
            'templates' => $templates->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'pillars' => $t->pillars->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'questions_count' => $p->questions->count(),
                ]),
            ]),
        ];
    }

    protected function toolCreateOrUpdateQuestion(array $args): array
    {
        $id = $args['question_id'] ?? null;
        $data = [
            'pillar_id' => $args['pillar_id'],
            'question' => $args['question'],
            'description' => $args['description'] ?? null,
            'question_type' => $args['question_type'] ?? 'yes_no',
            'failure_recommendation' => $args['failure_recommendation'] ?? null,
            'is_active' => true,
        ];

        if ($id) {
            $q = AuditQuestion::withoutGlobalScope('current_team')->findOrFail($id);
            $q->update($data);

            return ['status' => 'updated', 'question_id' => $q->id];
        }

        $q = AuditQuestion::create($data);

        return ['status' => 'created', 'question_id' => $q->id];
    }

    protected function toolListAllModules(array $args): array
    {
        $query = Module::query();
        if (! empty($args['category'])) $query->where('category', $args['category']);
        if (! empty($args['only_pool'])) $query->inSubscriptionPool();
        if (! empty($args['only_active'])) $query->active();

        $modules = $query->orderBy('sort_order')->get();

        return [
            'total' => $modules->count(),
            'modules' => $modules->map(fn ($m) => [
                'key' => $m->key,
                'name' => $m->getRawOriginal('name'),
                'description' => $m->getRawOriginal('description'),
                'category' => $m->category,
                'icon' => $m->icon,
                'route_prefix' => $m->route_prefix,
                'in_subscription_pool' => (bool) $m->in_subscription_pool,
                'is_active' => (bool) $m->is_active,
                'is_deprecated' => (bool) $m->is_deprecated,
                'badge_text' => $m->badge_text,
                'sort_order' => $m->sort_order,
            ]),
        ];
    }

    protected function toolManageToolPool(array $args): array
    {
        $module = Module::byKey($args['module_key']);
        if (! $module) throw new \InvalidArgumentException("Module '{$args['module_key']}' not found.");

        $module->update(['in_subscription_pool' => (bool) $args['in_pool']]);
        $this->toolSyncSubscriptionPlans();

        return ['status' => 'success', 'module' => $module->key, 'in_subscription_pool' => $module->in_subscription_pool];
    }

    protected function toolDeprecateModule(array $args): array
    {
        $module = Module::byKey($args['module_key']);
        if (! $module) throw new \InvalidArgumentException("Module '{$args['module_key']}' not found.");

        $module->update(['is_deprecated' => (bool) ($args['is_deprecated'] ?? true)]);
        $this->toolSyncSubscriptionPlans();

        return ['status' => 'success', 'module' => $module->key, 'is_deprecated' => $module->is_deprecated];
    }

    protected function toolUpdateModuleMetadata(array $args): array
    {
        $module = Module::byKey($args['module_key']);
        if (! $module) throw new \InvalidArgumentException("Module '{$args['module_key']}' not found.");

        $fields = ['name', 'description', 'category', 'icon', 'badge_text', 'sort_order'];
        $data = [];
        foreach ($fields as $f) {
            if (array_key_exists($f, $args)) $data[$f] = $args[$f];
        }

        $module->update($data);

        return ['status' => 'success', 'module' => $module->key, 'updated' => $data];
    }

    protected function toolSyncSubscriptionPlans(): array
    {
        $plan = Plan::where('slug', 'all-tools')->orWhere('slug', 'bundle')->first();
        if (! $plan) return ['status' => 'skipped', 'message' => 'Bundle plan not found.'];

        $poolModuleIds = Module::where('in_subscription_pool', true)->where('is_active', true)->where('is_deprecated', false)->pluck('id')->all();
        $plan->modules()->sync($poolModuleIds);

        return ['status' => 'synced', 'plan' => $plan->name, 'synced_modules_count' => count($poolModuleIds)];
    }

    protected function toolDiagnoseAuditGaps(array $args): array
    {
        $audit = Audit::with(['answers', 'template.pillars.questions', 'user'])->findOrFail($args['audit_id']);
        $service = app(QuestionRecommendationService::class);
        $score = $audit->allocoreScore ?: new \App\Models\AllocoreScore(['audit_id' => $audit->id]);

        $gaps = $service->gapsForScore($score, $audit->user ?? new User);

        return [
            'audit_id' => $audit->id,
            'user' => $audit->user?->name,
            'total_gaps' => count($gaps),
            'top_gap' => $gaps[0] ?? null,
            'all_gaps' => array_slice($gaps, 0, 10),
        ];
    }

    protected function toolCalculatePillarScores(array $args): array
    {
        $audit = Audit::with(['answers', 'template.pillars.questions'])->findOrFail($args['audit_id']);
        $scores = [];
        foreach ($audit->template?->pillars ?? [] as $pillar) {
            $totalQ = $pillar->questions->count();
            $answers = $audit->answers->whereIn('question_id', $pillar->questions->pluck('id'));
            $pts = $answers->sum(fn ($a) => is_numeric($a->value['answer'] ?? 0) ? (float) $a->value['answer'] : 0);
            $maxPts = $totalQ * 4;
            $pct = $maxPts > 0 ? round(($pts / $maxPts) * 100, 1) : 0;
            $scores[$pillar->name] = [
                'score_percentage' => $pct,
                'status' => $pct >= 75 ? 'Stark' : ($pct >= 40 ? 'Ausbaufähig' : 'Kritische Schwachstelle'),
            ];
        }

        return ['audit_id' => $audit->id, 'pillar_scores' => $scores];
    }

    protected function toolGenerateActionPlan(array $args): array
    {
        $diag = $this->toolDiagnoseAuditGaps($args);
        $steps = [];
        foreach (array_slice($diag['all_gaps'] ?? [], 0, 5) as $i => $gap) {
            $steps[] = [
                'step' => $i + 1,
                'pillar' => $gap['pillar'] ?? '',
                'question' => $gap['question'] ?? '',
                'tool' => $gap['module_name'] ?? 'Plattform-Tool',
                'book' => $gap['book']['title'] ?? 'Fachbuch',
                'action' => $gap['manual'] ?? 'Maßnahme umsetzen',
            ];
        }

        return ['audit_id' => $args['audit_id'], 'action_plan' => $steps];
    }

    protected function toolSearchBooks(array $args): array
    {
        if (! class_exists(Book::class)) return ['books' => []];
        $q = Book::with('author');
        if (! empty($args['query'])) {
            $s = $args['query'];
            $q->where('title', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%");
        }
        $books = $q->limit($args['limit'] ?? 20)->get();

        return ['books' => $books->map(fn ($b) => [
            'id' => $b->id,
            'title' => $b->title,
            'author' => $b->author?->name,
            'cover_url' => $b->cover_url,
            'affiliate_link' => $b->affiliate_link,
        ])];
    }

    protected function toolGetBookDetails(array $args): array
    {
        if (! class_exists(Book::class)) return ['error' => 'Book module not installed.'];
        $b = Book::with(['author', 'questionMappings.question'])->findOrFail($args['book_id']);

        return [
            'id' => $b->id,
            'title' => $b->title,
            'author' => $b->author?->name,
            'description' => $b->description,
            'cover_url' => $b->cover_url,
            'affiliate_link' => $b->affiliate_link,
            'mappings' => $b->questionMappings->map(fn ($m) => $m->question?->question),
        ];
    }

    protected function toolCreateOrUpdateBook(array $args): array
    {
        if (! class_exists(Book::class)) return ['error' => 'Book module not installed.'];
        $authorId = null;
        if (! empty($args['author_name'])) {
            $author = Author::firstOrCreate(['name' => $args['author_name']]);
            $authorId = $author->id;
        }

        $data = [
            'title' => $args['title'],
            'description' => $args['description'] ?? null,
            'cover_url' => $args['cover_url'] ?? null,
            'affiliate_link' => $args['affiliate_link'] ?? null,
            'status' => 'active',
        ];
        if ($authorId) $data['author_id'] = $authorId;

        if (! empty($args['book_id'])) {
            $b = Book::findOrFail($args['book_id']);
            $b->update($data);

            return ['status' => 'updated', 'book_id' => $b->id];
        }

        $b = Book::create($data);

        return ['status' => 'created', 'book_id' => $b->id];
    }

    protected function toolSearchGlossaryTerms(array $args): array
    {
        $q = GlossaryTerm::published();
        if (! empty($args['query'])) {
            $s = $args['query'];
            $q->where(fn ($sub) => $sub->where('term', 'like', "%{$s}%")->orWhere('definition', 'like', "%{$s}%"));
        }
        if (! empty($args['pillar'])) $q->where('pillar', $args['pillar']);

        $terms = $q->limit($args['limit'] ?? 30)->get();

        return ['terms' => $terms->map(fn ($t) => [
            'slug' => $t->slug,
            'term' => $t->term,
            'pillar' => $t->pillar,
            'simple_definition' => $t->simple_definition,
        ])];
    }

    protected function toolCreateOrUpdateGlossaryTerm(array $args): array
    {
        $t = GlossaryTerm::updateOrCreate(
            ['slug' => $args['slug']],
            [
                'term' => $args['term'],
                'definition' => $args['definition'],
                'simple_definition' => $args['simple_definition'] ?? null,
                'pillar' => $args['pillar'] ?? 'Revenue',
                'is_published' => true,
            ]
        );

        return ['status' => 'success', 'slug' => $t->slug, 'id' => $t->id];
    }

    protected function toolSearchBlogPosts(array $args): array
    {
        $q = Post::where('is_published', true);
        if (! empty($args['query'])) {
            $s = $args['query'];
            $q->where('title', 'like', "%{$s}%")->orWhere('body', 'like', "%{$s}%");
        }
        $posts = $q->limit($args['limit'] ?? 20)->get();

        return ['posts' => $posts->map(fn ($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'excerpt' => $p->excerpt,
            'featured_image' => $p->featured_image,
        ])];
    }

    protected function toolGetPostDetails(array $args): array
    {
        $p = Post::with(['category', 'tags'])->findOrFail($args['post_id']);

        return [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'excerpt' => $p->excerpt,
            'body' => $p->body,
            'featured_image' => $p->featured_image,
            'is_published' => (bool) $p->is_published,
            'is_featured' => (bool) $p->is_featured,
            'meta_title' => $p->meta_title,
            'meta_description' => $p->meta_description,
            'category' => $p->category?->name,
            'tags' => $p->tags->pluck('name'),
            'published_at' => $p->published_at?->toIso8601String(),
        ];
    }

    protected function toolCreateOrUpdatePost(array $args): array
    {
        $id = $args['post_id'] ?? null;

        if ($id) {
            $p = Post::findOrFail($id);
            $fields = ['title', 'slug', 'body', 'excerpt', 'featured_image', 'meta_title', 'meta_description', 'is_published', 'is_featured', 'category_id'];
            $data = [];
            foreach ($fields as $f) {
                if (array_key_exists($f, $args)) {
                    $data[$f] = $args[$f];
                }
            }

            // Handle Category by Name
            if (! empty($args['category']) || ! empty($args['category_name'])) {
                $catName = $args['category'] ?? $args['category_name'];
                $cat = \App\Models\BlogCategory::firstOrCreate(
                    ['slug' => Str::slug($catName)],
                    ['name' => $catName, 'is_active' => true]
                );
                $data['category_id'] = $cat->id;
            }

            // Clean excerpt if present
            if (isset($data['excerpt'])) {
                $data['excerpt'] = trim(preg_replace('/\s+/', ' ', $data['excerpt']));
            }

            // Set published_at if publishing
            if (($args['is_published'] ?? $p->is_published) && empty($p->published_at)) {
                $data['published_at'] = now();
            }

            $p->update($data);

            // Handle Tags with robust trimming and updateOrCreate
            if (isset($args['tags'])) {
                $rawTags = is_array($args['tags']) ? $args['tags'] : explode(',', (string) $args['tags']);
                $tagNames = collect($rawTags)
                    ->map(fn ($t) => trim(html_entity_decode((string) $t), " \t\n\r\0\x0B\"'[]{}"))
                    ->filter(fn ($t) => ! empty($t))
                    ->unique();

                $tagIds = [];
                foreach ($tagNames as $tagName) {
                    $t = \App\Models\BlogTag::updateOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $t->id;
                }
                $p->tags()->sync($tagIds);
            }

            $updatedFields = array_keys($data);
            if (isset($args['tags'])) {
                $updatedFields[] = 'tags';
            }
            if (isset($args['category']) || isset($args['category_name'])) {
                $updatedFields[] = 'category';
            }

            return [
                'status' => 'updated',
                'post_id' => $p->id,
                'title' => $p->title,
                'category' => $p->fresh()->category?->name,
                'tags' => $p->fresh()->tags->pluck('name'),
                'published_at' => $p->published_at?->toIso8601String(),
                'featured_image' => $p->featured_image,
                'updated_fields' => array_values(array_unique($updatedFields)),
            ];
        }

        if (empty($args['title']) || empty($args['body'])) {
            throw new \InvalidArgumentException("Both 'title' and 'body' are required when creating a new post.");
        }

        $slug = $args['slug'] ?? Str::slug($args['title']);
        $catId = null;
        if (! empty($args['category']) || ! empty($args['category_name'])) {
            $catName = $args['category'] ?? $args['category_name'];
            $cat = \App\Models\BlogCategory::firstOrCreate(
                ['slug' => Str::slug($catName)],
                ['name' => $catName, 'is_active' => true]
            );
            $catId = $cat->id;
        }

        $p = Post::create([
            'title' => $args['title'],
            'slug' => $slug,
            'body' => $args['body'],
            'excerpt' => $args['excerpt'] ?? Str::limit(strip_tags($args['body']), 160),
            'featured_image' => $args['featured_image'] ?? null,
            'category_id' => $catId,
            'meta_title' => $args['meta_title'] ?? $args['title'],
            'meta_description' => $args['meta_description'] ?? ($args['excerpt'] ?? null),
            'is_published' => $args['is_published'] ?? true,
            'is_featured' => $args['is_featured'] ?? false,
            'published_at' => ($args['is_published'] ?? true) ? now() : null,
            'user_id' => Auth::id() ?? 1,
        ]);

        if (isset($args['tags'])) {
            $rawTags = is_array($args['tags']) ? $args['tags'] : explode(',', (string) $args['tags']);
            $tagNames = collect($rawTags)
                ->map(fn ($t) => trim(html_entity_decode((string) $t), " \t\n\r\0\x0B\"'[]{}"))
                ->filter(fn ($t) => ! empty($t))
                ->unique();

            $tagIds = [];
            foreach ($tagNames as $tagName) {
                $t = \App\Models\BlogTag::updateOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName]
                );
                $tagIds[] = $t->id;
            }
            $p->tags()->sync($tagIds);
        }

        return ['status' => 'created', 'post_id' => $p->id, 'slug' => $p->slug];
    }

    protected function toolSearchUsersAndTeams(array $args): array
    {
        $limit = $args['limit'] ?? 20;
        $users = User::when(! empty($args['query']), fn ($q) => $q->where('name', 'like', "%{$args['query']}%")->orWhere('email', 'like', "%{$args['query']}%"))->limit($limit)->get();
        $teams = Team::when(! empty($args['query']), fn ($q) => $q->where('name', 'like', "%{$args['query']}%"))->limit($limit)->get();

        return [
            'users' => $users->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]),
            'teams' => $teams->map(fn ($t) => ['id' => $t->id, 'name' => $t->name]),
        ];
    }

    protected function toolGetUserSubscriptionStatus(array $args): array
    {
        $user = User::findOrFail($args['user_id']);
        $poolTools = Module::inSubscriptionPool()->get(['key', 'name']);

        return [
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'active_subscription' => $user->subscribed(),
            'accessible_pool_tools_count' => $poolTools->count(),
            'pool_tools' => $poolTools,
        ];
    }

    protected function toolGetPlatformMetrics(): array
    {
        return [
            'registered_users' => User::count(),
            'completed_audits' => Audit::count(),
            'active_pool_tools' => Module::inSubscriptionPool()->count(),
            'total_library_books' => class_exists(Book::class) ? Book::count() : 0,
            'glossary_terms' => GlossaryTerm::count(),
        ];
    }

    protected function toolRunAllocoreArtisan(array $args): array
    {
        $allowed = ['cache:clear', 'view:clear', 'route:clear', 'config:clear', 'optimize:clear', 'migrate:status'];
        $cmd = trim($args['command']);
        if (! in_array($cmd, $allowed, true)) {
            return ['error' => "Command '{$cmd}' not in whitelist.", 'allowed' => $allowed];
        }

        Artisan::call($cmd);

        return [
            'command' => "php artisan {$cmd}",
            'output' => Artisan::output(),
        ];
    }

    protected function toolGetSystemHealth(): array
    {
        $dbOk = true;
        try {
            DB::select('SELECT 1');
        } catch (\Throwable) {
            $dbOk = false;
        }

        return [
            'database_connected' => $dbOk,
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];
    }

    /**
     * MCP Resources.
     */
    public function listResources(): array
    {
        return [
            'resources' => [
                ['uri' => 'allocore://audit/questions', 'name' => 'All Platform Audit Questions', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://modules/pool', 'name' => 'Subscription Tool Pool', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://knowledge/terms', 'name' => 'Business Glossary Terms', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://books/catalog', 'name' => 'BookIntelligence Catalog', 'mimeType' => 'application/json'],
            ],
        ];
    }

    public function readResource(string $uri): array
    {
        $content = match ($uri) {
            'allocore://audit/questions' => AuditQuestion::withoutGlobalScope('current_team')->get(['id', 'question', 'recommended_module_key', 'recommended_book_id'])->toJson(JSON_PRETTY_PRINT),
            'allocore://modules/pool' => Module::inSubscriptionPool()->get(['key', 'name', 'category', 'route_prefix'])->toJson(JSON_PRETTY_PRINT),
            'allocore://knowledge/terms' => GlossaryTerm::published()->get(['term', 'slug', 'pillar'])->toJson(JSON_PRETTY_PRINT),
            'allocore://books/catalog' => class_exists(Book::class) ? Book::get(['id', 'title', 'cover_url'])->toJson(JSON_PRETTY_PRINT) : '[]',
            default => throw new \InvalidArgumentException("Resource '{$uri}' not found."),
        };

        return [
            'contents' => [
                ['uri' => $uri, 'mimeType' => 'application/json', 'text' => $content],
            ],
        ];
    }

    /**
     * MCP Prompts.
     */
    public function listPrompts(): array
    {
        return [
            'prompts' => [
                [
                    'name' => 'diagnose_audit_gaps',
                    'description' => 'Run comprehensive AI audit diagnosis and 90-day action plan.',
                    'arguments' => [['name' => 'audit_id', 'required' => true]],
                ],
                [
                    'name' => 'auto_link_audit_solutions',
                    'description' => 'Inspect unassigned questions and deduce optimal tools & books.',
                    'arguments' => [],
                ],
            ],
        ];
    }

    public function getPrompt(string $name, array $args): array
    {
        $promptText = match ($name) {
            'diagnose_audit_gaps' => "Sie sind der Allocore Unternehmens-Coach. Bitte analysieren Sie Audit #".($args['audit_id'] ?? 1)." und erstellen Sie einen 5-Säulen-Aktionsplan.",
            'auto_link_audit_solutions' => "Überprüfen Sie alle unvollständigen Fragen und weisen Sie passende Tools, Fachbücher und Fachbegriffe zu.",
            default => throw new \InvalidArgumentException("Prompt '{$name}' not found."),
        };

        return [
            'description' => $name,
            'messages' => [
                ['role' => 'user', 'content' => ['type' => 'text', 'text' => $promptText]],
            ],
        ];
    }
}
