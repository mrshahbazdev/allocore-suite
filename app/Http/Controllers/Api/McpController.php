<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\CaseStudy;
use App\Models\GlossaryTerm;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Post;
use App\Models\Team;
use App\Models\ToolSubscription;
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
use Modules\LeadQuality\Models\Contact;

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

                // 9. Client Audits & Answers
                [
                    'name' => 'list_recent_audits',
                    'description' => 'List completed and in-progress client audits with scores, company name, industry, and status.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'enum' => ['draft', 'in_progress', 'completed', 'archived']],
                            'industry' => ['type' => 'string'],
                            'search' => ['type' => 'string', 'description' => 'Search by company name or focus pillar'],
                            'limit' => ['type' => 'integer', 'default' => 20],
                        ],
                    ],
                ],
                [
                    'name' => 'get_audit_full_answers',
                    'description' => 'Retrieve all question responses, scores, user comments, and failure recommendations for an audit.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'audit_id' => ['type' => 'integer', 'description' => 'ID of the audit'],
                        ],
                        'required' => ['audit_id'],
                    ],
                ],

                // 10. Leads & CRM (LeadQuality)
                [
                    'name' => 'search_leads',
                    'description' => 'Search and filter CRM leads, diagnostic contacts, and company prospects.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Search by name, email, or company'],
                            'status' => ['type' => 'string'],
                            'pipeline_stage' => ['type' => 'string'],
                            'limit' => ['type' => 'integer', 'default' => 20],
                        ],
                    ],
                ],
                [
                    'name' => 'get_lead_details',
                    'description' => 'Retrieve full lead profile, contact info, notes, score, and interaction history.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'lead_id' => ['type' => 'integer'],
                        ],
                        'required' => ['lead_id'],
                    ],
                ],
                [
                    'name' => 'create_or_update_lead',
                    'description' => 'Create a new CRM lead or update status, pipeline stage, notes, and score.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'lead_id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'email' => ['type' => 'string'],
                            'company' => ['type' => 'string'],
                            'position' => ['type' => 'string'],
                            'status' => ['type' => 'string'],
                            'pipeline_stage' => ['type' => 'string'],
                            'notes' => ['type' => 'string'],
                            'score' => ['type' => 'integer'],
                            'budget' => ['type' => 'number'],
                        ],
                        'required' => ['name', 'email'],
                    ],
                ],

                // 11. Case Studies
                [
                    'name' => 'search_case_studies',
                    'description' => 'Search published and draft client case studies and transformation stories.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string'],
                            'industry' => ['type' => 'string'],
                            'limit' => ['type' => 'integer', 'default' => 20],
                        ],
                    ],
                ],
                [
                    'name' => 'get_case_study_details',
                    'description' => 'Retrieve complete case study content, challenge, solution, result, and metric KPIs.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'slug' => ['type' => 'string'],
                        ],
                    ],
                ],
                [
                    'name' => 'create_or_update_case_study',
                    'description' => 'Create or update a case study with challenge, solution, quantifiable results, and metrics.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'title' => ['type' => 'string'],
                            'slug' => ['type' => 'string'],
                            'company' => ['type' => 'string'],
                            'industry' => ['type' => 'string'],
                            'challenge' => ['type' => 'string'],
                            'solution' => ['type' => 'string'],
                            'result' => ['type' => 'string'],
                            'metrics' => ['type' => 'array'],
                            'image' => ['type' => 'string'],
                            'is_published' => ['type' => 'boolean'],
                            'sort_order' => ['type' => 'integer'],
                        ],
                        'required' => ['title'],
                    ],
                ],

                // 12. Financial & Revenue
                [
                    'name' => 'get_financial_summary',
                    'description' => 'Retrieve financial overview, active subscription plans breakdown, and customer metrics.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],

                // 13. Advanced AI Automation & Live Audit Execution
                [
                    'name' => 'create_client_audit',
                    'description' => 'Initialize a new client audit assessment for a company.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'company_name' => ['type' => 'string', 'description' => 'Name of the company/client'],
                            'industry' => ['type' => 'string'],
                            'template_id' => ['type' => 'integer', 'description' => 'Optional template ID (defaults to 1)'],
                            'size' => ['type' => 'string', 'description' => 'e.g. 1-10, 11-50, 51-250'],
                            'focus_pillar' => ['type' => 'string', 'description' => 'e.g. Revenue, Profit, Order'],
                        ],
                        'required' => ['company_name'],
                    ],
                ],
                [
                    'name' => 'submit_audit_answers',
                    'description' => 'Submit answers for an audit, calculate 5-pillar scores, and complete the assessment.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'audit_id' => ['type' => 'integer'],
                            'answers' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'question_id' => ['type' => 'integer'],
                                        'value' => ['type' => 'number', 'description' => 'Rating scale (1-5) or 1/0 for yes/no'],
                                        'comment' => ['type' => 'string'],
                                    ],
                                    'required' => ['question_id', 'value'],
                                ],
                            ],
                            'mark_completed' => ['type' => 'boolean', 'default' => true],
                        ],
                        'required' => ['audit_id', 'answers'],
                    ],
                ],
                [
                    'name' => 'score_lead_with_ai',
                    'description' => 'Run AI qualification heuristics on a CRM lead and update its priority and pipeline stage.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'lead_id' => ['type' => 'integer'],
                        ],
                        'required' => ['lead_id'],
                    ],
                ],
                [
                    'name' => 'repurpose_book_to_blog',
                    'description' => 'Extract business book core principles from library and generate a draft thought leadership blog post.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'book_id' => ['type' => 'integer'],
                            'target_pillar' => ['type' => 'string'],
                        ],
                        'required' => ['book_id'],
                    ],
                ],
                [
                    'name' => 'list_activity_logs',
                    'description' => 'Retrieve recent platform activity logs and audit trails.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'default' => 20],
                        ],
                    ],
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
            'list_recent_audits' => $this->toolListRecentAudits($arguments),
            'get_audit_full_answers' => $this->toolGetAuditFullAnswers($arguments),
            'search_leads' => $this->toolSearchLeads($arguments),
            'get_lead_details' => $this->toolGetLeadDetails($arguments),
            'create_or_update_lead' => $this->toolCreateOrUpdateLead($arguments),
            'search_case_studies' => $this->toolSearchCaseStudies($arguments),
            'get_case_study_details' => $this->toolGetCaseStudyDetails($arguments),
            'create_or_update_case_study' => $this->toolCreateOrUpdateCaseStudy($arguments),
            'get_financial_summary' => $this->toolGetFinancialSummary(),
            'create_client_audit' => $this->toolCreateClientAudit($arguments),
            'submit_audit_answers' => $this->toolSubmitAuditAnswers($arguments),
            'score_lead_with_ai' => $this->toolScoreLeadWithAi($arguments),
            'repurpose_book_to_blog' => $this->toolRepurposeBookToBlog($arguments),
            'list_activity_logs' => $this->toolListActivityLogs($arguments),
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
        $audit = Audit::withoutGlobalScope('current_team')->with(['answers', 'template.pillars.questions', 'creator'])->findOrFail($args['audit_id']);
        $service = app(QuestionRecommendationService::class);
        $score = \App\Models\AllocoreScore::where('audit_id', $audit->id)->first() ?: new \App\Models\AllocoreScore(['audit_id' => $audit->id]);

        $gaps = $service->gapsForScore($score, $audit->creator ?? new User);

        return [
            'audit_id' => $audit->id,
            'company_name' => $audit->company_name,
            'user' => $audit->creator?->name,
            'total_gaps' => count($gaps),
            'top_gap' => $gaps[0] ?? null,
            'all_gaps' => array_slice($gaps, 0, 10),
        ];
    }

    protected function toolCalculatePillarScores(array $args): array
    {
        $audit = Audit::withoutGlobalScope('current_team')->with(['answers', 'template.pillars.questions'])->findOrFail($args['audit_id']);
        $scores = [];
        foreach ($audit->template?->pillars ?? [] as $pillar) {
            $totalQ = $pillar->questions->count();
            $answers = $audit->answers->whereIn('question_id', $pillar->questions->pluck('id'));
            $pts = $answers->sum(fn ($a) => is_numeric($a->value['answer'] ?? ($a->value ?? 0)) ? (float) ($a->value['answer'] ?? $a->value) : 0);
            $maxPts = $totalQ * 4;
            $pct = $maxPts > 0 ? round(($pts / $maxPts) * 100, 1) : 0;
            $scores[$pillar->name] = [
                'score_percentage' => $pct,
                'status' => $pct >= 75 ? 'Stark' : ($pct >= 40 ? 'Ausbaufähig' : 'Kritische Schwachstelle'),
            ];
        }

        return ['audit_id' => $audit->id, 'company_name' => $audit->company_name, 'pillar_scores' => $scores];
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

    protected function toolListRecentAudits(array $args): array
    {
        $query = Audit::withoutGlobalScope('current_team')->with(['template', 'creator']);
        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }
        if (! empty($args['industry'])) {
            $query->where('industry', 'like', "%{$args['industry']}%");
        }
        if (! empty($args['search'])) {
            $s = $args['search'];
            $query->where(fn ($q) => $q->where('company_name', 'like', "%{$s}%")->orWhere('focus_pillar', 'like', "%{$s}%"));
        }
        $limit = min(100, max(1, (int) ($args['limit'] ?? 20)));
        $audits = $query->latest()->limit($limit)->get();

        return [
            'total' => $audits->count(),
            'audits' => $audits->map(fn ($a) => [
                'id' => $a->id,
                'company_name' => $a->company_name,
                'industry' => $a->industry,
                'size' => $a->size,
                'status' => $a->status,
                'template' => $a->template?->name,
                'creator' => $a->creator?->name,
                'answers_count' => $a->answers()->count(),
                'completed_at' => $a->completed_at?->toIso8601String(),
                'created_at' => $a->created_at?->toIso8601String(),
            ]),
        ];
    }

    protected function toolGetAuditFullAnswers(array $args): array
    {
        $audit = Audit::withoutGlobalScope('current_team')->with(['template', 'results.pillar'])->findOrFail($args['audit_id']);
        $answers = AuditAnswer::withoutGlobalScope('current_team')
            ->with(['question.pillar'])
            ->where('audit_id', $audit->id)
            ->get();

        return [
            'audit_id' => $audit->id,
            'company_name' => $audit->company_name,
            'industry' => $audit->industry,
            'status' => $audit->status,
            'template' => $audit->template?->name,
            'pillar_results' => $audit->results->map(fn ($r) => [
                'pillar' => $r->pillar?->name,
                'score' => $r->average_score,
                'maturity_level' => $r->maturity_level,
            ]),
            'total_answers' => $answers->count(),
            'answers' => $answers->map(fn ($ans) => [
                'question_id' => $ans->question_id,
                'pillar' => $ans->question?->pillar?->name,
                'question' => $ans->question?->getRawOriginal('question'),
                'type' => $ans->question?->question_type,
                'value' => $ans->value,
                'comment' => $ans->comment,
                'failure_recommendation' => $ans->question?->failure_recommendation,
                'recommended_tool' => $ans->question?->recommended_module_key,
                'recommended_book_id' => $ans->question?->recommended_book_id,
            ]),
        ];
    }

    protected function toolSearchLeads(array $args): array
    {
        $leadClass = class_exists(Contact::class) ? Contact::class : (class_exists(\Modules\FinancialPlatform\Models\Lead::class) ? \Modules\FinancialPlatform\Models\Lead::class : null);
        if (! $leadClass) {
            return ['total' => 0, 'leads' => [], 'note' => 'Lead/CRM module tables not present.'];
        }

        $q = $leadClass::withoutGlobalScope('current_team');
        if (! empty($args['query'])) {
            $s = $args['query'];
            $q->where(fn ($sub) => $sub->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('company', 'like', "%{$s}%"));
        }
        if (! empty($args['status'])) {
            $q->where('status', $args['status']);
        }
        if (! empty($args['pipeline_stage'])) {
            $q->where('pipeline_stage', $args['pipeline_stage']);
        }

        $limit = min(100, max(1, (int) ($args['limit'] ?? 20)));
        $leads = $q->latest()->limit($limit)->get();

        return [
            'total' => $leads->count(),
            'leads' => $leads->map(fn ($l) => [
                'id' => $l->id,
                'name' => $l->name,
                'email' => $l->email,
                'company' => $l->company ?? $l->company_name ?? null,
                'position' => $l->position,
                'status' => $l->status,
                'pipeline_stage' => $l->pipeline_stage ?? 'new',
                'score' => $l->score ?? null,
                'created_at' => $l->created_at?->toIso8601String(),
            ]),
        ];
    }

    protected function toolGetLeadDetails(array $args): array
    {
        $leadClass = class_exists(Contact::class) ? Contact::class : (class_exists(\Modules\FinancialPlatform\Models\Lead::class) ? \Modules\FinancialPlatform\Models\Lead::class : null);
        if (! $leadClass) {
            return ['error' => 'Lead/CRM module tables not present.'];
        }

        $lead = $leadClass::withoutGlobalScope('current_team')->findOrFail($args['lead_id']);

        return [
            'id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone ?? null,
            'company' => $lead->company ?? $lead->company_name ?? null,
            'position' => $lead->position,
            'website' => $lead->website,
            'linkedin' => $lead->linkedin,
            'status' => $lead->status,
            'pipeline_stage' => $lead->pipeline_stage ?? null,
            'score' => $lead->score ?? null,
            'budget' => $lead->budget,
            'notes' => $lead->notes,
            'created_at' => $lead->created_at?->toIso8601String(),
        ];
    }

    protected function toolCreateOrUpdateLead(array $args): array
    {
        $leadClass = class_exists(Contact::class) ? Contact::class : (class_exists(\Modules\FinancialPlatform\Models\Lead::class) ? \Modules\FinancialPlatform\Models\Lead::class : null);
        if (! $leadClass) {
            return ['error' => 'Lead/CRM module tables not present.'];
        }

        $id = $args['lead_id'] ?? null;
        $fields = ['name', 'email', 'company', 'position', 'status', 'pipeline_stage', 'notes', 'score', 'budget'];
        $data = [];
        foreach ($fields as $f) {
            if (array_key_exists($f, $args)) {
                $data[$f] = $args[$f];
            }
        }

        if ($id) {
            $lead = $leadClass::withoutGlobalScope('current_team')->findOrFail($id);
            $lead->update($data);
            return ['status' => 'updated', 'lead_id' => $lead->id];
        }

        $lead = $leadClass::create($data);
        return ['status' => 'created', 'lead_id' => $lead->id];
    }

    protected function toolSearchCaseStudies(array $args): array
    {
        $q = CaseStudy::query();
        if (! empty($args['query'])) {
            $s = $args['query'];
            $q->where(fn ($sub) => $sub->where('title', 'like', "%{$s}%")
                ->orWhere('company', 'like', "%{$s}%")
                ->orWhere('challenge', 'like', "%{$s}%")
                ->orWhere('solution', 'like', "%{$s}%"));
        }
        if (! empty($args['industry'])) {
            $q->where('industry', $args['industry']);
        }

        $limit = min(50, max(1, (int) ($args['limit'] ?? 20)));
        $studies = $q->orderBy('sort_order')->limit($limit)->get();

        return [
            'total' => $studies->count(),
            'case_studies' => $studies->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'slug' => $c->slug,
                'company' => $c->company,
                'industry' => $c->industry,
                'metrics' => $c->metrics,
                'is_published' => (bool) $c->is_published,
            ]),
        ];
    }

    protected function toolGetCaseStudyDetails(array $args): array
    {
        $cs = ! empty($args['id']) ? CaseStudy::findOrFail($args['id']) : CaseStudy::where('slug', $args['slug'])->firstOrFail();

        return [
            'id' => $cs->id,
            'title' => $cs->title,
            'slug' => $cs->slug,
            'company' => $cs->company,
            'industry' => $cs->industry,
            'challenge' => $cs->challenge,
            'solution' => $cs->solution,
            'result' => $cs->result,
            'metrics' => $cs->metrics,
            'image' => $cs->image,
            'is_published' => (bool) $cs->is_published,
        ];
    }

    protected function toolCreateOrUpdateCaseStudy(array $args): array
    {
        $id = $args['id'] ?? null;
        $fields = ['title', 'slug', 'company', 'industry', 'challenge', 'solution', 'result', 'metrics', 'image', 'is_published', 'sort_order'];
        $data = [];
        foreach ($fields as $f) {
            if (array_key_exists($f, $args)) {
                $data[$f] = $args[$f];
            }
        }
        if (empty($data['slug']) && ! empty($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if ($id) {
            $cs = CaseStudy::findOrFail($id);
            $cs->update($data);
            return ['status' => 'updated', 'id' => $cs->id, 'slug' => $cs->slug];
        }

        $cs = CaseStudy::create($data);
        return ['status' => 'created', 'id' => $cs->id, 'slug' => $cs->slug];
    }

    protected function toolGetFinancialSummary(): array
    {
        $totalUsers = User::count();
        $totalTeams = Team::count();
        $activeSubscriptions = class_exists(ToolSubscription::class) ? ToolSubscription::where('status', 'active')->count() : 0;
        $plans = Plan::withCount('subscriptions')->get();

        return [
            'total_registered_users' => $totalUsers,
            'total_teams' => $totalTeams,
            'active_subscriptions_count' => $activeSubscriptions,
            'plans_breakdown' => $plans->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
                'billing_period' => $p->billing_period,
                'active_subscribers' => $p->subscriptions_count,
            ]),
        ];
    }

    protected function toolCreateClientAudit(array $args): array
    {
        $templateId = $args['template_id'] ?? 1;
        $audit = Audit::create([
            'team_id' => Auth::user()?->current_team_id ?? Team::first()?->id,
            'created_by' => Auth::id(),
            'template_id' => $templateId,
            'company_name' => $args['company_name'],
            'industry' => $args['industry'] ?? null,
            'size' => $args['size'] ?? '11-50',
            'focus_pillar' => $args['focus_pillar'] ?? 'Revenue',
            'status' => 'in_progress',
        ]);

        return [
            'status' => 'created',
            'audit_id' => $audit->id,
            'company_name' => $audit->company_name,
            'template_id' => $audit->template_id,
            'audit_status' => $audit->status,
        ];
    }

    protected function toolSubmitAuditAnswers(array $args): array
    {
        $audit = Audit::withoutGlobalScope('current_team')->findOrFail($args['audit_id']);
        $savedCount = 0;

        foreach ($args['answers'] as $ans) {
            $qId = (int) $ans['question_id'];
            $val = $ans['value'];
            $comment = $ans['comment'] ?? null;

            AuditAnswer::withoutGlobalScope('current_team')->updateOrCreate(
                ['audit_id' => $audit->id, 'question_id' => $qId],
                [
                    'team_id' => $audit->team_id,
                    'value' => is_array($val) ? $val : ['answer' => $val],
                    'comment' => $comment,
                ]
            );
            $savedCount++;
        }

        if (! empty($args['mark_completed'])) {
            $audit->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        $scores = $this->toolCalculatePillarScores(['audit_id' => $audit->id]);

        return [
            'status' => 'success',
            'audit_id' => $audit->id,
            'saved_answers_count' => $savedCount,
            'audit_status' => $audit->status,
            'pillar_scores' => $scores['pillar_scores'] ?? [],
        ];
    }

    protected function toolScoreLeadWithAi(array $args): array
    {
        $leadClass = class_exists(Contact::class) ? Contact::class : (class_exists(\Modules\FinancialPlatform\Models\Lead::class) ? \Modules\FinancialPlatform\Models\Lead::class : null);
        if (! $leadClass) {
            return ['error' => 'Lead/CRM module tables not present.'];
        }

        $lead = $leadClass::withoutGlobalScope('current_team')->findOrFail($args['lead_id']);

        $score = 50;
        if (! empty($lead->email) && ! str_contains($lead->email, 'gmail.com') && ! str_contains($lead->email, 'yahoo.com')) {
            $score += 15;
        }
        if (! empty($lead->position) && (str_contains(strtolower($lead->position), 'ceo') || str_contains(strtolower($lead->position), 'geschäftsführer') || str_contains(strtolower($lead->position), 'leiter') || str_contains(strtolower($lead->position), 'head'))) {
            $score += 20;
        }
        if (! empty($lead->budget) && (float) $lead->budget > 5000) {
            $score += 15;
        }

        $priority = $score >= 80 ? 1 : ($score >= 60 ? 2 : 3);
        $stage = $score >= 80 ? 'qualified' : ($score >= 60 ? 'contacted' : 'nurturing');

        $lead->update([
            'score' => min(100, $score),
            'priority' => $priority,
            'pipeline_stage' => $stage,
        ]);

        return [
            'status' => 'scored',
            'lead_id' => $lead->id,
            'name' => $lead->name,
            'company' => $lead->company ?? $lead->company_name ?? null,
            'calculated_score' => $score,
            'priority' => $priority,
            'pipeline_stage' => $stage,
            'recommended_action' => $score >= 80 ? 'Direkten Strategie-Call vereinbaren' : 'Allocore Reifegrad-Audit Beispiel zusenden',
        ];
    }

    protected function toolRepurposeBookToBlog(array $args): array
    {
        if (! class_exists(Book::class)) return ['error' => 'Book module not installed.'];
        $b = Book::with(['author', 'questionMappings.question'])->findOrFail($args['book_id']);

        $authorName = $b->author?->name ?? 'Fachautor';
        $title = "Erfolgsfaktor {$b->title}: Was mittelständische Entscheider von {$authorName} lernen können";
        $slug = Str::slug($b->title.'-praxisleitfaden');

        $outline = [
            'title' => $title,
            'slug' => $slug,
            'suggested_category' => 'Methodik & Strategie',
            'suggested_tags' => ['Buchtipp', 'Unternehmensführung', 'Mittelstand', 'Wachstum'],
            'book' => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $authorName,
                'affiliate_link' => $b->affiliate_link,
            ],
            'sections' => [
                '1. Die zentrale Herausforderung in der Praxis',
                '2. Kernprinzipien aus dem Buch: '.$b->title,
                '3. Typische Umsetzungsfallen im Betrieb',
                '4. Schritt-für-Schritt-Implementierung mit passenden Plattform-Tools',
                '5. Exklusive Buchempfehlung (Widget Section 8)',
                '6. Fazit und nächste Handlungsschritte',
            ],
        ];

        return [
            'status' => 'draft_outline_generated',
            'book_id' => $b->id,
            'article_plan' => $outline,
        ];
    }

    protected function toolListActivityLogs(array $args): array
    {
        $limit = min(50, max(1, (int) ($args['limit'] ?? 20)));
        $logs = \App\Models\ActivityLog::latest()->limit($limit)->get();

        return [
            'total' => $logs->count(),
            'logs' => $logs->map(fn ($l) => [
                'id' => $l->id,
                'log_name' => $l->log_name,
                'description' => $l->description,
                'created_at' => $l->created_at?->toIso8601String(),
                'properties' => $l->properties,
            ]),
        ];
    }

    /**
     * MCP Resources.
     */
    public function listResources(): array
    {
        return [
            'resources' => [
                ['uri' => 'allocore://platform-overview', 'name' => 'Platform Metrics & Live Counts', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://audit/questions', 'name' => 'All Platform Audit Questions', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://audit/templates', 'name' => 'Audit Templates & Pillars', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://recent-audits', 'name' => 'Recent Completed Audits', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://modules/pool', 'name' => 'Subscription Tool Pool', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://knowledge/terms', 'name' => 'Business Glossary Terms', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://books/catalog', 'name' => 'BookIntelligence Catalog', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://blog/posts', 'name' => 'Published Blog Posts', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://case-studies', 'name' => 'Client Case Studies', 'mimeType' => 'application/json'],
            ],
        ];
    }

    public function readResource(string $uri): array
    {
        $content = match ($uri) {
            'allocore://platform-overview' => json_encode($this->toolGetPlatformMetrics(), JSON_PRETTY_PRINT),
            'allocore://audit/questions' => AuditQuestion::withoutGlobalScope('current_team')->get(['id', 'question', 'recommended_module_key', 'recommended_book_id'])->toJson(JSON_PRETTY_PRINT),
            'allocore://audit/templates' => AuditTemplate::withoutGlobalScope('current_team')->with('pillars')->get()->toJson(JSON_PRETTY_PRINT),
            'allocore://recent-audits' => Audit::withoutGlobalScope('current_team')->latest()->limit(15)->get(['id', 'company_name', 'industry', 'status', 'created_at'])->toJson(JSON_PRETTY_PRINT),
            'allocore://modules/pool' => Module::inSubscriptionPool()->get(['key', 'name', 'category', 'route_prefix'])->toJson(JSON_PRETTY_PRINT),
            'allocore://knowledge/terms' => GlossaryTerm::published()->get(['term', 'slug', 'pillar'])->toJson(JSON_PRETTY_PRINT),
            'allocore://books/catalog' => class_exists(Book::class) ? Book::get(['id', 'title', 'cover_url', 'affiliate_link'])->toJson(JSON_PRETTY_PRINT) : '[]',
            'allocore://blog/posts' => Post::where('is_published', true)->get(['id', 'title', 'slug', 'featured_image'])->toJson(JSON_PRETTY_PRINT),
            'allocore://case-studies' => CaseStudy::where('is_published', true)->get(['id', 'title', 'slug', 'company', 'industry'])->toJson(JSON_PRETTY_PRINT),
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
                    'name' => 'audit_consultant',
                    'description' => 'Run comprehensive AI audit diagnosis and 90-day action plan for a client audit.',
                    'arguments' => [['name' => 'audit_id', 'required' => true]],
                ],
                [
                    'name' => 'seo_content_creator',
                    'description' => 'Generate high-impact German B2B thought leadership blog post linked to books & tools.',
                    'arguments' => [['name' => 'topic', 'required' => true], ['name' => 'book_id', 'required' => false]],
                ],
                [
                    'name' => 'lead_nurture_strategy',
                    'description' => 'Generate personalized conversion roadmap for a specific CRM lead.',
                    'arguments' => [['name' => 'lead_id', 'required' => true]],
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
            'audit_consultant' => "Sie sind der Allocore Senior Executive Coach. Analysieren Sie die Ergebnisse von Audit #".($args['audit_id'] ?? 1)." über die 5 Säulen (Revenue, Profit, Order, Influence, Legacy) und erstellen Sie eine priorisierte 90-Tage-Transformations-Roadmap mit konkreten Tool- und Buchempfehlungen.",
            'seo_content_creator' => "Erstellen Sie einen suchmaschinenoptimierten, 8-teiligen Fachartikel zum Thema '".($args['topic'] ?? 'Unternehmensführung')."'. Binden Sie passende Allocore-Tools sowie die Buchempfehlung Box (Buch ID #".($args['book_id'] ?? 451).") nahtlos ein.",
            'lead_nurture_strategy' => "Analysieren Sie das Profil und die Interaktionen von Lead #".($args['lead_id'] ?? 1)." und entwickeln Sie eine maßgeschneiderte B2B-Ansprachestrategie mit ROI-Fokus.",
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
