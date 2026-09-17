<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\CaseStudy;
use App\Models\GlossaryTerm;
use App\Models\Integration;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Post;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use App\Models\Webhook;
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
use Modules\FocusMatrix\Models\Delegation;
use Modules\FocusMatrix\Models\Task as FocusTask;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\InvoiceItem;
use Modules\LeadQuality\Models\Contact;
use Modules\OrgMatrix\Models\Person;
use Modules\OrgMatrix\Models\Role as OrgRole;
use Modules\SopBuilder\Models\Sop;
use Modules\SopBuilder\Models\Step;
use Modules\VisionFlow\Models\StrategicGoal;
use Modules\VisionFlow\Models\Vision;

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
     * Direct REST/HTTP Tools Endpoint.
     */
    public function httpListTools(Request $request): JsonResponse
    {
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], 200, $this->corsHeaders());
        }
        $this->authenticateRequest($request);
        return response()->json($this->listTools(), 200, $this->corsHeaders());
    }

    /**
     * Direct REST/HTTP Resources Endpoint.
     */
    public function httpListResources(Request $request): JsonResponse
    {
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], 200, $this->corsHeaders());
        }
        $this->authenticateRequest($request);
        return response()->json($this->listResources(), 200, $this->corsHeaders());
    }

    /**
     * Direct REST/HTTP Prompts Endpoint.
     */
    public function httpListPrompts(Request $request): JsonResponse
    {
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], 200, $this->corsHeaders());
        }
        $this->authenticateRequest($request);
        return response()->json($this->listPrompts(), 200, $this->corsHeaders());
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
                [
                    'name' => 'validate_tool_pool_integrity',
                    'description' => 'Verify platform modules, route prefixes, database integrity, and subscription plan mappings.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],
                [
                    'name' => 'generate_audit_executive_summary',
                    'description' => 'Generate a comprehensive, executive C-Level audit report in Markdown with health score, 5-pillar breakdown, critical risks, and 30-60-90 day strategic roadmap.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'audit_id' => ['type' => 'integer', 'description' => 'ID of the audit'],
                        ],
                        'required' => ['audit_id'],
                    ],
                ],
                [
                    'name' => 'benchmark_audit_performance',
                    'description' => 'Benchmark an audit\'s scores against industry peers and platform averages, returning percentile rankings and gap analysis.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'audit_id' => ['type' => 'integer', 'description' => 'ID of the audit'],
                        ],
                        'required' => ['audit_id'],
                    ],
                ],
                [
                    'name' => 'get_pipeline_funnel_analytics',
                    'description' => 'Calculate CRM pipeline conversion velocity, stage distribution, total deal pipeline value, average lead score, and bottleneck stages.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'default' => 50],
                        ],
                    ],
                ],
                [
                    'name' => 'batch_relink_glossary_in_posts',
                    'description' => 'Scan published blog posts for business glossary terms and return SEO internal linking opportunities or inject contextual links.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'post_id' => ['type' => 'integer', 'description' => 'Optional specific post ID'],
                            'apply_links' => ['type' => 'boolean', 'default' => false],
                        ],
                    ],
                ],
                [
                    'name' => 'list_support_tickets',
                    'description' => 'List customer support tickets filtered by status, priority, or category.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'enum' => ['open', 'in_progress', 'resolved', 'closed']],
                            'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high', 'urgent']],
                            'limit' => ['type' => 'integer', 'default' => 20],
                        ],
                    ],
                ],
                [
                    'name' => 'create_or_reply_support_ticket',
                    'description' => 'Create a new support ticket or post an agent/consultant reply to an existing ticket.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'ticket_id' => ['type' => 'integer', 'description' => 'If provided, posts a reply to this ticket'],
                            'subject' => ['type' => 'string', 'description' => 'Subject for new tickets'],
                            'body' => ['type' => 'string', 'description' => 'Message or reply content'],
                            'category' => ['type' => 'string'],
                            'priority' => ['type' => 'string'],
                            'status' => ['type' => 'string'],
                            'is_internal' => ['type' => 'boolean', 'default' => false],
                        ],
                        'required' => ['body'],
                    ],
                ],
                [
                    'name' => 'export_platform_dataset',
                    'description' => 'Export clean structured JSON datasets of platform assets (questions, glossary, case studies, blog posts, tools) for offline analysis or RAG embedding.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'dataset' => ['type' => 'string', 'enum' => ['questions', 'glossary', 'case_studies', 'blog_posts', 'tools', 'all'], 'default' => 'all'],
                        ],
                    ],
                ],
                [
                    'name' => 'simulate_cashflow_runway',
                    'description' => 'Calculate runway months, monthly net burn rate, breakeven revenue, and safety buffer based on cost structures and monthly revenue inputs.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'cash_on_hand' => ['type' => 'number', 'description' => 'Current cash reserves (EUR)'],
                            'monthly_revenue' => ['type' => 'number', 'description' => 'Average monthly revenue (EUR)'],
                            'monthly_fixed_costs' => ['type' => 'number', 'description' => 'Fixed overhead and operational costs (EUR)'],
                            'variable_cost_percentage' => ['type' => 'number', 'description' => 'Variable costs as % of revenue (default 30)'],
                        ],
                        'required' => ['cash_on_hand', 'monthly_revenue', 'monthly_fixed_costs'],
                    ],
                ],
                [
                    'name' => 'calculate_unit_economics',
                    'description' => 'Calculate Customer Acquisition Cost (CAC), Customer Lifetime Value (CLV), CLV:CAC ratio, and payback period in months.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'sales_marketing_cost' => ['type' => 'number'],
                            'new_customers_acquired' => ['type' => 'integer'],
                            'average_revenue_per_user' => ['type' => 'number'],
                            'gross_margin_percentage' => ['type' => 'number', 'default' => 75],
                            'monthly_churn_percentage' => ['type' => 'number', 'default' => 3],
                        ],
                        'required' => ['sales_marketing_cost', 'new_customers_acquired', 'average_revenue_per_user'],
                    ],
                ],
                [
                    'name' => 'generate_and_store_sop',
                    'description' => 'Generate and persist a standardized corporate Standard Operating Procedure (SOP) with role responsibility and step-by-step instructions into the platform.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'role' => ['type' => 'string'],
                            'steps' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'Ordered list of step instructions',
                            ],
                        ],
                        'required' => ['title', 'steps'],
                    ],
                ],
                [
                    'name' => 'list_stored_sops',
                    'description' => 'Search and retrieve corporate SOPs and process playbooks from SopBuilder.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'search' => ['type' => 'string'],
                            'limit' => ['type' => 'integer', 'default' => 20],
                        ],
                    ],
                ],
                [
                    'name' => 'evaluate_kpi_health',
                    'description' => 'Evaluate key company metrics (EBITDA margin, net margin, DSO, churn) against standard German SME benchmarks.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'metrics' => [
                                'type' => 'object',
                                'description' => 'Key-value map of KPI names and values, e.g. {"ebitda_margin": 16.5, "dso_days": 28}',
                            ],
                        ],
                        'required' => ['metrics'],
                    ],
                ],
                [
                    'name' => 'score_account_health',
                    'description' => 'Calculate Account Engagement & Retention Health Score (0-100) and identify churn risks for an account/team.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'team_id' => ['type' => 'integer', 'description' => 'Optional specific team ID'],
                        ],
                    ],
                ],
                [
                    'name' => 'list_webhooks_and_integrations',
                    'description' => 'List registered outbound webhooks, event subscriptions, and third-party integration statuses.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],
                [
                    'name' => 'create_or_preview_invoice',
                    'description' => 'Draft a legally compliant B2B invoice or quotation with client info, line items, VAT calculations, and payment terms.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'client_name' => ['type' => 'string'],
                            'client_email' => ['type' => 'string'],
                            'items' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'description' => ['type' => 'string'],
                                        'quantity' => ['type' => 'number'],
                                        'unit_price' => ['type' => 'number'],
                                    ],
                                    'required' => ['description', 'unit_price'],
                                ],
                            ],
                            'tax_rate' => ['type' => 'number', 'default' => 19.0],
                            'due_in_days' => ['type' => 'integer', 'default' => 14],
                            'preview_only' => ['type' => 'boolean', 'default' => false],
                        ],
                        'required' => ['client_name'],
                    ],
                ],
                [
                    'name' => 'list_invoices_and_receivables',
                    'description' => 'List invoices, outstanding accounts receivable balance, and payment status breakdown from InvoiceMaker.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'enum' => ['draft', 'sent', 'paid', 'unpaid', 'overdue']],
                            'limit' => ['type' => 'integer', 'default' => 20],
                        ],
                    ],
                ],
                [
                    'name' => 'get_organization_chart',
                    'description' => 'Retrieve company organizational structure, departments, roles, and assigned personnel from OrgMatrix.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],
                [
                    'name' => 'create_or_update_org_role',
                    'description' => 'Define or update an organizational role, department, and job description.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'role_id' => ['type' => 'integer', 'description' => 'Optional ID for updating existing role'],
                            'name' => ['type' => 'string'],
                            'department' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                        ],
                        'required' => ['name'],
                    ],
                ],
                [
                    'name' => 'get_strategic_vision_and_goals',
                    'description' => 'Retrieve company vision statement, mission, and strategic OKR goals from VisionFlow.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],
                [
                    'name' => 'create_strategic_goal',
                    'description' => 'Define a strategic OKR goal with target metrics, deadline, and Allocore pillar alignment.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'pillar' => ['type' => 'string', 'enum' => ['Revenue', 'Profit', 'Order', 'Influence', 'Legacy']],
                            'target_value' => ['type' => 'number'],
                            'deadline' => ['type' => 'string', 'description' => 'YYYY-MM-DD format'],
                        ],
                        'required' => ['title'],
                    ],
                ],
                [
                    'name' => 'analyze_eisenhower_tasks',
                    'description' => 'Analyze active company tasks categorized into Eisenhower quadrants (Do Now, Plan, Delegate, Eliminate).',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],
                [
                    'name' => 'create_delegation_task',
                    'description' => 'Create a delegated executive task with expected outcome, assignee, and target deadline.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'assigned_to' => ['type' => 'string'],
                            'expected_outcome' => ['type' => 'string'],
                            'due_date' => ['type' => 'string'],
                        ],
                        'required' => ['title', 'assigned_to'],
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
            'validate_tool_pool_integrity' => $this->toolValidateToolPoolIntegrity(),
            'generate_audit_executive_summary' => $this->toolGenerateAuditExecutiveSummary($arguments),
            'benchmark_audit_performance' => $this->toolBenchmarkAuditPerformance($arguments),
            'get_pipeline_funnel_analytics' => $this->toolGetPipelineFunnelAnalytics($arguments),
            'batch_relink_glossary_in_posts' => $this->toolBatchRelinkGlossaryInPosts($arguments),
            'list_support_tickets' => $this->toolListSupportTickets($arguments),
            'create_or_reply_support_ticket' => $this->toolCreateOrReplySupportTicket($arguments),
            'export_platform_dataset' => $this->toolExportPlatformDataset($arguments),
            'simulate_cashflow_runway' => $this->toolSimulateCashflowRunway($arguments),
            'calculate_unit_economics' => $this->toolCalculateUnitEconomics($arguments),
            'generate_and_store_sop' => $this->toolGenerateAndStoreSop($arguments),
            'list_stored_sops' => $this->toolListStoredSops($arguments),
            'evaluate_kpi_health' => $this->toolEvaluateKpiHealth($arguments),
            'score_account_health' => $this->toolScoreAccountHealth($arguments),
            'list_webhooks_and_integrations' => $this->toolListWebhooksAndIntegrations($arguments),
            'create_or_preview_invoice' => $this->toolCreateOrPreviewInvoice($arguments),
            'list_invoices_and_receivables' => $this->toolListInvoicesAndReceivables($arguments),
            'get_organization_chart' => $this->toolGetOrganizationChart($arguments),
            'create_or_update_org_role' => $this->toolCreateOrUpdateOrgRole($arguments),
            'get_strategic_vision_and_goals' => $this->toolGetStrategicVisionAndGoals($arguments),
            'create_strategic_goal' => $this->toolCreateStrategicGoal($arguments),
            'analyze_eisenhower_tasks' => $this->toolAnalyzeEisenhowerTasks($arguments),
            'create_delegation_task' => $this->toolCreateDelegationTask($arguments),
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

    protected function toolValidateToolPoolIntegrity(): array
    {
        $modules = Module::orderBy('sort_order')->get();
        $poolModules = Module::inSubscriptionPool()->get();
        $activeCount = Module::active()->count();
        $deprecatedCount = Module::deprecated()->count();

        $bundlePlan = Plan::where('slug', 'all-tools-bundle')->first();
        $bundleModulesCount = $bundlePlan ? $bundlePlan->modules()->count() : 0;

        return [
            'status' => 'healthy',
            'total_registered_modules' => $modules->count(),
            'active_modules_count' => $activeCount,
            'deprecated_modules_count' => $deprecatedCount,
            'in_subscription_pool_count' => $poolModules->count(),
            'all_tools_bundle_plan_synced' => $bundleModulesCount === $poolModules->count(),
            'bundle_modules_count' => $bundleModulesCount,
            'categories' => $modules->pluck('category')->unique()->values(),
        ];
    }

    protected function toolGenerateAuditExecutiveSummary(array $args): array
    {
        $auditId = $args['audit_id'] ?? null;
        if (! $auditId) throw new \InvalidArgumentException("audit_id is required.");

        $audit = Audit::withoutGlobalScope('current_team')
            ->with(['answers.question.pillar', 'team'])
            ->findOrFail($auditId);

        $pillarScores = $this->toolCalculatePillarScores(['audit_id' => $auditId]);
        $gaps = $this->toolDiagnoseAuditGaps(['audit_id' => $auditId]);

        $companyName = $audit->company_name ?: ($audit->team?->name ?? 'Unternehmen');
        $overallScore = $pillarScores['overall_score_percent'] ?? 0;
        $overallScoreFmt = number_format($overallScore, 1, ',', '.');

        $report = "# Management Summary & Transformations-Audit\n\n";
        $report .= "**Unternehmen:** {$companyName}  \n";
        $report .= "**Branche:** " . ($audit->industry ?: 'Mittelstand / B2B') . "  \n";
        $report .= "**Audit-Status:** " . ucfirst($audit->status) . "  \n";
        $report .= "**Gesamt-Reifegrad (Allocore Score):** {$overallScoreFmt} %  \n\n";
        $report .= "---\n\n";

        $report .= "## 1. Strategische Einordnung nach dem 5-Säulen-Modell\n\n";
        $report .= "| Säule | Reifegrad | Status | Priorität |\n";
        $report .= "|---|---|---|---|\n";
        foreach ($pillarScores['pillars'] ?? [] as $p) {
            $pct = number_format($p['score_percent'] ?? 0, 1, ',', '.');
            $status = ($p['score_percent'] >= 75) ? '✅ Exzellent' : (($p['score_percent'] >= 50) ? '⚠️ Optimierungsbedarf' : '🚨 Kritischer Handlungsbedarf');
            $priority = ($p['score_percent'] < 50) ? 'Hoch (Sofortmaßnahme)' : (($p['score_percent'] < 75) ? 'Mittel (Q2 Roadmap)' : 'Niedrig');
            $report .= "| {$p['name']} | {$pct} % | {$status} | {$priority} |\n";
        }
        $report .= "\n";

        $report .= "## 2. Kritische Schwachstellen & Risikobereiche\n\n";
        if (! empty($gaps['gaps'])) {
            foreach (array_slice($gaps['gaps'], 0, 5) as $i => $g) {
                $num = $i + 1;
                $report .= "### {$num}. {$g['pillar']}: {$g['question']}\n";
                $report .= "- **Aktuelle Bewertung:** {$g['score']} / {$g['max_score']}\n";
                if (! empty($g['failure_recommendation'])) {
                    $report .= "- **Handlungsempfehlung:** {$g['failure_recommendation']}\n";
                }
                if (! empty($g['recommended_tool'])) {
                    $report .= "- **Empfohlenes Allocore-Tool:** `{$g['recommended_tool']}`\n";
                }
                if (! empty($g['recommended_book'])) {
                    $report .= "- **Empfohlene Fachliteratur:** {$g['recommended_book']['title']}\n";
                }
                $report .= "\n";
            }
        } else {
            $report .= "Keine kritischen Defizite festgestellt. Das Unternehmen erfüllt alle Kernstandards.\n\n";
        }

        $report .= "## 3. Empfohlene 90-Tage-Roadmap\n\n";
        $report .= "- **Monat 1 (Quick Wins & Transparenz):** Implementierung von Frühwarn-Dashboards und Bereinigung der profitabelsten Produkt-/Dienstleistungslinien.\n";
        $report .= "- **Monat 2 (Prozessautomatisierung & CRM):** Standardisierung der Vertriebs- und Lead-Qualifizierungspipelines.\n";
        $report .= "- **Monat 3 (Skalierung & Governance):** Etablierung quartalsweiser Audit-Reviews und Delegationsstrukturen.\n\n";

        return [
            'audit_id' => $audit->id,
            'company_name' => $companyName,
            'overall_score_percent' => $overallScore,
            'executive_summary_markdown' => $report,
        ];
    }

    protected function toolBenchmarkAuditPerformance(array $args): array
    {
        $auditId = $args['audit_id'] ?? null;
        if (! $auditId) throw new \InvalidArgumentException("audit_id is required.");

        $audit = Audit::withoutGlobalScope('current_team')->findOrFail($auditId);
        $myScores = $this->toolCalculatePillarScores(['audit_id' => $auditId]);

        $allAudits = Audit::withoutGlobalScope('current_team')
            ->where('status', 'completed')
            ->pluck('id');

        $allCount = max(1, $allAudits->count());
        $benchmarks = [];

        foreach ($myScores['pillars'] ?? [] as $pillar) {
            $pillarName = $pillar['name'];
            $myScore = $pillar['score_percent'] ?? 0;

            $avgScore = AuditAnswer::withoutGlobalScope('current_team')
                ->whereHas('question.pillar', fn ($q) => $q->where('name', $pillarName))
                ->avg('score') ?? 2.5;

            $avgPercent = round(($avgScore / 5.0) * 100, 1);
            $delta = round($myScore - $avgPercent, 1);

            $benchmarks[] = [
                'pillar' => $pillarName,
                'client_score_percent' => $myScore,
                'platform_average_percent' => $avgPercent,
                'delta_percent' => $delta,
                'performance' => $delta >= 0 ? 'above_average' : 'below_average',
            ];
        }

        return [
            'audit_id' => $audit->id,
            'company_name' => $audit->company_name,
            'industry' => $audit->industry,
            'total_benchmarked_audits' => $allCount,
            'pillar_benchmarks' => $benchmarks,
        ];
    }

    protected function toolGetPipelineFunnelAnalytics(array $args): array
    {
        $query = Contact::withoutGlobalScopes();
        $totalLeads = $query->count();

        $stages = ['new', 'contacted', 'qualified', 'proposal', 'won', 'lost'];
        $stageBreakdown = [];
        $totalBudget = 0;

        foreach ($stages as $st) {
            $count = (clone $query)->where('pipeline_stage', $st)->count();
            $stageBudget = (clone $query)->where('pipeline_stage', $st)->sum('budget') ?? 0;
            $avgScore = (clone $query)->where('pipeline_stage', $st)->avg('score') ?? 0;

            $totalBudget += (float) $stageBudget;
            $pct = $totalLeads > 0 ? round(($count / $totalLeads) * 100, 1) : 0;

            $stageBreakdown[$st] = [
                'count' => $count,
                'percentage_of_total' => $pct,
                'total_budget' => round((float) $stageBudget, 2),
                'avg_lead_score' => round((float) $avgScore, 1),
            ];
        }

        $wonCount = $stageBreakdown['won']['count'] ?? 0;
        $winRate = $totalLeads > 0 ? round(($wonCount / $totalLeads) * 100, 1) : 0;

        return [
            'total_leads' => $totalLeads,
            'total_pipeline_value_eur' => $totalBudget,
            'overall_win_rate_percent' => $winRate,
            'stage_funnel' => $stageBreakdown,
        ];
    }

    protected function toolBatchRelinkGlossaryInPosts(array $args): array
    {
        $postId = $args['post_id'] ?? null;

        $terms = GlossaryTerm::published()->get(['term', 'slug', 'pillar']);
        $postsQuery = Post::query();
        if ($postId) {
            $postsQuery->where('id', $postId);
        } else {
            $postsQuery->where('is_published', true);
        }
        $posts = $postsQuery->get();

        $results = [];

        foreach ($posts as $post) {
            $body = $post->body;
            $matchedTerms = [];

            foreach ($terms as $termObj) {
                $termText = preg_quote($termObj->term, '/');
                if (preg_match('/\b'.$termText.'\b/i', $body)) {
                    $matchedTerms[] = [
                        'term' => $termObj->term,
                        'slug' => $termObj->slug,
                        'url' => url('/lexikon/'.$termObj->slug),
                    ];
                }
            }

            $results[] = [
                'post_id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'matched_glossary_terms_count' => count($matchedTerms),
                'matched_terms' => $matchedTerms,
            ];
        }

        return [
            'total_posts_analyzed' => $posts->count(),
            'posts' => $results,
        ];
    }

    protected function toolListSupportTickets(array $args): array
    {
        $query = SupportTicket::with(['user', 'messages' => fn ($q) => $q->latest()->limit(1)]);

        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }
        if (! empty($args['priority'])) {
            $query->where('priority', $args['priority']);
        }

        $limit = min(50, max(1, (int) ($args['limit'] ?? 20)));
        $tickets = $query->latest()->limit($limit)->get();

        return [
            'total' => $tickets->count(),
            'tickets' => $tickets->map(fn ($t) => [
                'id' => $t->id,
                'subject' => $t->subject,
                'category' => $t->category,
                'priority' => $t->priority,
                'status' => $t->status,
                'user' => $t->user?->name,
                'user_email' => $t->user?->email,
                'messages_count' => $t->messages()->count(),
                'created_at' => $t->created_at?->toIso8601String(),
            ]),
        ];
    }

    protected function toolCreateOrReplySupportTicket(array $args): array
    {
        $ticketId = $args['ticket_id'] ?? null;
        $body = $args['body'] ?? '';
        if (empty($body)) throw new \InvalidArgumentException("body is required.");

        if ($ticketId) {
            $ticket = SupportTicket::findOrFail($ticketId);
            $msg = SupportTicketMessage::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => Auth::id() ?? 1,
                'body' => $body,
                'is_internal' => ! empty($args['is_internal']),
            ]);

            if (! empty($args['status'])) {
                $ticket->update(['status' => $args['status']]);
            }

            return [
                'status' => 'reply_added',
                'ticket_id' => $ticket->id,
                'message_id' => $msg->id,
                'ticket_status' => $ticket->status,
            ];
        }

        $ticket = SupportTicket::create([
            'user_id' => Auth::id() ?? 1,
            'subject' => $args['subject'] ?? 'Support Inquiry',
            'body' => $body,
            'category' => $args['category'] ?? 'general',
            'priority' => $args['priority'] ?? 'medium',
            'status' => 'open',
        ]);

        return [
            'status' => 'ticket_created',
            'ticket_id' => $ticket->id,
            'subject' => $ticket->subject,
        ];
    }

    protected function toolExportPlatformDataset(array $args): array
    {
        $dataset = $args['dataset'] ?? 'all';
        $export = [];

        if ($dataset === 'questions' || $dataset === 'all') {
            $export['audit_questions'] = AuditQuestion::withoutGlobalScope('current_team')->with('pillar')->get()->map(fn ($q) => [
                'id' => $q->id,
                'pillar' => $q->pillar?->name,
                'question' => $q->getRawOriginal('question'),
                'recommended_tool' => $q->recommended_module_key,
                'recommended_book_id' => $q->recommended_book_id,
                'failure_recommendation' => $q->failure_recommendation,
            ]);
        }

        if ($dataset === 'glossary' || $dataset === 'all') {
            $export['glossary_terms'] = GlossaryTerm::published()->get(['term', 'slug', 'definition', 'simple_definition', 'pillar']);
        }

        if ($dataset === 'case_studies' || $dataset === 'all') {
            $export['case_studies'] = CaseStudy::where('is_published', true)->get(['id', 'title', 'slug', 'company', 'industry', 'challenge', 'solution', 'result', 'metrics']);
        }

        if ($dataset === 'blog_posts' || $dataset === 'all') {
            $export['blog_posts'] = Post::where('is_published', true)->get(['id', 'title', 'slug', 'excerpt', 'featured_image', 'created_at']);
        }

        if ($dataset === 'tools' || $dataset === 'all') {
            $export['tools'] = Module::all(['key', 'name', 'category', 'route_prefix', 'in_subscription_pool', 'is_active']);
        }

        return [
            'exported_at' => now()->toIso8601String(),
            'dataset_type' => $dataset,
            'data' => $export,
        ];
    }

    protected function toolSimulateCashflowRunway(array $args): array
    {
        $cash = (float) ($args['cash_on_hand'] ?? 50000);
        $monthlyRevenue = (float) ($args['monthly_revenue'] ?? 20000);
        $fixedCosts = (float) ($args['monthly_fixed_costs'] ?? 15000);
        $variableCostRate = (float) ($args['variable_cost_percentage'] ?? 30) / 100.0;

        $variableCosts = $monthlyRevenue * $variableCostRate;
        $totalMonthlyCosts = $fixedCosts + $variableCosts;
        $monthlyNetCashflow = $monthlyRevenue - $totalMonthlyCosts;

        $contributionMarginRatio = 1.0 - $variableCostRate;
        $breakevenRevenue = $contributionMarginRatio > 0 ? round($fixedCosts / $contributionMarginRatio, 2) : 0;

        $runwayMonths = null;
        $isBurnMode = $monthlyNetCashflow < 0;

        if ($isBurnMode) {
            $monthlyBurn = abs($monthlyNetCashflow);
            $runwayMonths = $monthlyBurn > 0 ? round($cash / $monthlyBurn, 1) : 0;
        }

        $recommendedBuffer = round($fixedCosts * 3, 2);
        $safetyStatus = ($cash >= $recommendedBuffer) ? 'secure' : (($cash >= $fixedCosts) ? 'warning' : 'critical');

        return [
            'inputs' => [
                'cash_on_hand' => $cash,
                'monthly_revenue' => $monthlyRevenue,
                'monthly_fixed_costs' => $fixedCosts,
                'variable_cost_percentage' => ($variableCostRate * 100),
            ],
            'monthly_metrics' => [
                'total_monthly_costs' => round($totalMonthlyCosts, 2),
                'net_cashflow_eur' => round($monthlyNetCashflow, 2),
                'breakeven_revenue_eur' => $breakevenRevenue,
                'is_burn_mode' => $isBurnMode,
                'runway_months' => $runwayMonths,
            ],
            'liquidity_assessment' => [
                'safety_status' => $safetyStatus,
                'recommended_3_month_buffer_eur' => $recommendedBuffer,
                'buffer_coverage_percent' => $recommendedBuffer > 0 ? round(($cash / $recommendedBuffer) * 100, 1) : 100,
                'recommended_tool' => 'cash-core',
                'action_advice' => $isBurnMode
                    ? "Dringende Ausgabenbereinigung erforderlich. Mit aktuellen Parametern reicht die Liquidität für ca. {$runwayMonths} Monate."
                    : 'Positiver operativer Cashflow. Überschüsse in Wachstumsinitiativen oder Rücklagen allozieren.',
            ],
        ];
    }

    protected function toolCalculateUnitEconomics(array $args): array
    {
        $salesMarketingCost = (float) ($args['sales_marketing_cost'] ?? 10000);
        $newCustomers = max(1, (int) ($args['new_customers_acquired'] ?? 10));
        $arpu = (float) ($args['average_revenue_per_user'] ?? 200);
        $grossMarginPct = (float) ($args['gross_margin_percentage'] ?? 75) / 100.0;
        $monthlyChurnPct = max(0.01, (float) ($args['monthly_churn_percentage'] ?? 3)) / 100.0;

        $cac = round($salesMarketingCost / $newCustomers, 2);
        $averageCustomerLifespanMonths = round(1.0 / $monthlyChurnPct, 1);
        $clv = round(($arpu * $grossMarginPct) / $monthlyChurnPct, 2);
        $clvCacRatio = $cac > 0 ? round($clv / $cac, 2) : 0;
        $paybackMonths = ($arpu * $grossMarginPct) > 0 ? round($cac / ($arpu * $grossMarginPct), 1) : 0;

        $rating = ($clvCacRatio >= 3.0 && $paybackMonths <= 12) ? 'Exzellent' : (($clvCacRatio >= 2.0) ? 'Gesund' : 'Kritisch (Überarbeitung Akquisekosten/Preise nötig)');

        return [
            'cac_eur' => $cac,
            'clv_eur' => $clv,
            'clv_cac_ratio' => $clvCacRatio,
            'cac_payback_months' => $paybackMonths,
            'average_customer_lifespan_months' => $averageCustomerLifespanMonths,
            'unit_economics_health' => $rating,
            'benchmark_advice' => $clvCacRatio >= 3.0
                ? 'Top B2B-Mittelstandswert (CLV:CAC > 3:1). Akquisebudgets können skaliert werden.'
                : 'Optimierungsbedarf bei Churn-Reduktion oder Vertriebseffizienz.',
            'recommended_tool' => 'revenue-planner',
        ];
    }

    protected function toolGenerateAndStoreSop(array $args): array
    {
        $title = $args['title'] ?? 'Standard Operating Procedure';
        $description = $args['description'] ?? null;
        $role = $args['role'] ?? 'Mitarbeiter';
        $stepsInput = $args['steps'] ?? [];

        $sop = null;
        if (class_exists(Sop::class)) {
            $sop = Sop::withoutGlobalScope('current_team')->create([
                'user_id' => Auth::id() ?? 1,
                'title' => $title,
                'description' => $description,
                'status' => 'published',
                'version' => 1,
                'published_at' => now(),
            ]);

            if (class_exists(Step::class) && is_array($stepsInput)) {
                foreach ($stepsInput as $idx => $st) {
                    Step::create([
                        'sop_id' => $sop->id,
                        'title' => is_string($st) ? $st : ($st['title'] ?? 'Schritt '.($idx + 1)),
                        'content' => is_array($st) ? ($st['content'] ?? '') : '',
                        'sort_order' => $idx + 1,
                    ]);
                }
            }
        }

        return [
            'status' => 'sop_created',
            'sop_id' => $sop?->id,
            'title' => $title,
            'role' => $role,
            'steps_count' => count($stepsInput),
            'tool_route' => '/app/sop-builder',
        ];
    }

    protected function toolListStoredSops(array $args): array
    {
        if (! class_exists(Sop::class)) return ['total' => 0, 'sops' => []];

        $query = Sop::withoutGlobalScope('current_team')->with(['steps']);
        if (! empty($args['search'])) {
            $s = $args['search'];
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"));
        }

        $limit = min(50, max(1, (int) ($args['limit'] ?? 20)));
        $sops = $query->latest()->limit($limit)->get();

        return [
            'total' => $sops->count(),
            'sops' => $sops->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'description' => $s->description,
                'version' => $s->version,
                'status' => $s->status,
                'steps_count' => $s->steps->count(),
                'steps' => $s->steps->map(fn ($st) => [
                    'order' => $st->sort_order,
                    'title' => $st->title,
                ]),
                'published_at' => $s->published_at?->toIso8601String(),
            ]),
        ];
    }

    protected function toolEvaluateKpiHealth(array $args): array
    {
        $metrics = $args['metrics'] ?? [];
        $evaluations = [];

        $benchmarks = [
            'ebitda_margin' => ['name' => 'EBITDA-Marge', 'target' => 15.0, 'unit' => '%', 'higher_is_better' => true],
            'net_profit_margin' => ['name' => 'Nettoumsatzrendite', 'target' => 8.0, 'unit' => '%', 'higher_is_better' => true],
            'dso_days' => ['name' => 'Forderungslaufzeit (DSO)', 'target' => 35.0, 'unit' => 'Tage', 'higher_is_better' => false],
            'annual_churn_rate' => ['name' => 'Jährliche Kundenabwanderung', 'target' => 5.0, 'unit' => '%', 'higher_is_better' => false],
            'employee_turnover' => ['name' => 'Mitarbeiterfluktuation', 'target' => 10.0, 'unit' => '%', 'higher_is_better' => false],
            'gross_margin' => ['name' => 'Rohertragsmarge', 'target' => 60.0, 'unit' => '%', 'higher_is_better' => true],
        ];

        foreach ($metrics as $key => $val) {
            $val = (float) $val;
            if (isset($benchmarks[$key])) {
                $bm = $benchmarks[$key];
                $isGood = $bm['higher_is_better'] ? ($val >= $bm['target']) : ($val <= $bm['target']);
                $evaluations[] = [
                    'kpi' => $key,
                    'label' => $bm['name'],
                    'current_value' => $val,
                    'benchmark_target' => $bm['target'],
                    'unit' => $bm['unit'],
                    'status' => $isGood ? 'healthy' : 'warning',
                    'badge' => $isGood ? '✅ Im Zielbereich' : '⚠️ Handlungsbedarf',
                ];
            } else {
                $evaluations[] = [
                    'kpi' => $key,
                    'current_value' => $val,
                    'status' => 'custom',
                ];
            }
        }

        return [
            'evaluated_kpis_count' => count($evaluations),
            'results' => $evaluations,
            'recommended_tool' => 'smart-kpi',
        ];
    }

    protected function toolScoreAccountHealth(array $args): array
    {
        $teamId = $args['team_id'] ?? null;
        $team = $teamId ? Team::find($teamId) : Team::first();

        if (! $team) return ['error' => 'No team or account found.'];

        $auditsCount = Audit::withoutGlobalScope('current_team')->where('team_id', $team->id)->count();
        $completedAuditsCount = Audit::withoutGlobalScope('current_team')->where('team_id', $team->id)->where('status', 'completed')->count();
        $contactsCount = Contact::withoutGlobalScopes()->where('team_id', $team->id)->count();
        $ticketsCount = SupportTicket::where('team_id', $team->id)->whereIn('status', ['open', 'in_progress'])->count();

        $healthScore = 50;
        if ($completedAuditsCount > 0) $healthScore += 25;
        if ($contactsCount >= 5) $healthScore += 15;
        if ($ticketsCount === 0) $healthScore += 10;
        if ($ticketsCount > 2) $healthScore -= 20;

        $healthScore = max(0, min(100, $healthScore));
        $riskLevel = ($healthScore >= 75) ? 'Low (High Retention)' : (($healthScore >= 50) ? 'Medium (Engagement Needed)' : 'High (Churn Risk)');

        return [
            'team_id' => $team->id,
            'account_name' => $team->name,
            'health_score' => $healthScore,
            'retention_risk_level' => $riskLevel,
            'signals' => [
                'total_audits' => $auditsCount,
                'completed_audits' => $completedAuditsCount,
                'active_crm_leads' => $contactsCount,
                'open_support_tickets' => $ticketsCount,
            ],
            'recommended_retention_action' => $healthScore < 60 ? 'Proaktiven Check-in Call durch Account Manager vereinbaren' : 'Feature-Update & Erweiterung vorstellen',
        ];
    }

    protected function toolListWebhooksAndIntegrations(array $args): array
    {
        $webhooks = Webhook::with(['integration', 'calls' => fn ($q) => $q->latest()->limit(1)])->get();
        $integrations = Integration::all();

        return [
            'total_webhooks' => $webhooks->count(),
            'webhooks' => $webhooks->map(fn ($w) => [
                'id' => $w->id,
                'name' => $w->name,
                'url' => $w->url,
                'events' => $w->events,
                'is_active' => (bool) $w->is_active,
                'last_sent_at' => $w->last_sent_at?->toIso8601String(),
            ]),
            'total_integrations' => $integrations->count(),
            'integrations' => $integrations->map(fn ($i) => [
                'id' => $i->id,
                'name' => $i->name,
                'provider' => $i->provider ?? null,
                'is_active' => (bool) $i->is_active,
            ]),
        ];
    }

    protected function toolCreateOrPreviewInvoice(array $args): array
    {
        $clientName = $args['client_name'] ?? 'Musterkunde GmbH';
        $clientEmail = $args['client_email'] ?? 'rechnung@musterkunde.de';
        $items = $args['items'] ?? [
            ['description' => 'Allocore Unternehmens-Audit & Beratung', 'quantity' => 1, 'unit_price' => 1500.00],
        ];
        $taxRate = (float) ($args['tax_rate'] ?? 19.0);
        $dueDays = (int) ($args['due_in_days'] ?? 14);
        $isDraftOnly = ! empty($args['preview_only']);

        $netTotal = 0.0;
        $calculatedItems = [];
        foreach ($items as $it) {
            $qty = (float) ($it['quantity'] ?? 1);
            $price = (float) ($it['unit_price'] ?? 0);
            $total = $qty * $price;
            $netTotal += $total;
            $calculatedItems[] = [
                'description' => $it['description'] ?? 'Position',
                'quantity' => $qty,
                'unit_price' => $price,
                'total' => round($total, 2),
            ];
        }

        $taxAmount = round($netTotal * ($taxRate / 100.0), 2);
        $grossTotal = round($netTotal + $taxAmount, 2);
        $invoiceNumber = 'INV-'.date('Y').'-'.rand(1000, 9999);
        $dueDate = now()->addDays($dueDays)->format('d.m.Y');

        $invoiceId = null;
        if (! $isDraftOnly && class_exists(Invoice::class)) {
            $inv = Invoice::withoutGlobalScope('current_team')->create([
                'user_id' => Auth::id() ?? 1,
                'invoice_number' => $invoiceNumber,
                'status' => 'draft',
                'net_amount' => $netTotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $grossTotal,
                'due_date' => now()->addDays($dueDays),
            ]);
            $invoiceId = $inv->id;
        }

        return [
            'status' => $isDraftOnly ? 'preview_generated' : 'invoice_created',
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoiceNumber,
            'client' => ['name' => $clientName, 'email' => $clientEmail],
            'items' => $calculatedItems,
            'financials' => [
                'net_total_eur' => round($netTotal, 2),
                'tax_rate_percent' => $taxRate,
                'tax_amount_eur' => $taxAmount,
                'gross_total_eur' => $grossTotal,
                'due_date' => $dueDate,
                'payment_terms' => "Zahlbar innerhalb von {$dueDays} Tagen ohne Abzug.",
            ],
            'tool_route' => '/app/invoice-maker',
        ];
    }

    protected function toolListInvoicesAndReceivables(array $args): array
    {
        if (! class_exists(Invoice::class)) return ['total' => 0, 'invoices' => []];

        $query = Invoice::withoutGlobalScope('current_team');
        if (! empty($args['status'])) $query->where('status', $args['status']);

        $limit = min(50, max(1, (int) ($args['limit'] ?? 20)));
        $invoices = $query->latest()->limit($limit)->get();

        $openTotal = Invoice::withoutGlobalScope('current_team')->whereIn('status', ['sent', 'unpaid', 'overdue'])->sum('total_amount') ?? 0;
        $paidTotal = Invoice::withoutGlobalScope('current_team')->where('status', 'paid')->sum('total_amount') ?? 0;

        return [
            'total' => $invoices->count(),
            'open_receivables_total_eur' => round((float) $openTotal, 2),
            'paid_revenue_total_eur' => round((float) $paidTotal, 2),
            'invoices' => $invoices->map(fn ($i) => [
                'id' => $i->id,
                'invoice_number' => $i->invoice_number,
                'status' => $i->status,
                'total_amount' => (float) $i->total_amount,
                'due_date' => $i->due_date?->toIso8601String(),
                'created_at' => $i->created_at?->toIso8601String(),
            ]),
        ];
    }

    protected function toolGetOrganizationChart(array $args): array
    {
        if (! class_exists(OrgRole::class)) return ['roles' => [], 'people' => []];

        $roles = OrgRole::withoutGlobalScope('current_team')->get();
        $people = class_exists(Person::class) ? Person::withoutGlobalScope('current_team')->with('roles')->get() : collect();

        return [
            'total_roles' => $roles->count(),
            'total_people' => $people->count(),
            'roles' => $roles->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'department' => $r->department ?? 'General',
                'description' => $r->description,
                'parent_role_id' => $r->parent_id ?? null,
            ]),
            'people' => $people->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'email' => $p->email,
                'assigned_roles' => $p->roles->pluck('name'),
            ]),
            'tool_route' => '/app/org-matrix',
        ];
    }

    protected function toolCreateOrUpdateOrgRole(array $args): array
    {
        if (! class_exists(OrgRole::class)) return ['error' => 'OrgMatrix module not found.'];

        $roleId = $args['role_id'] ?? null;
        $data = [
            'name' => $args['name'],
            'department' => $args['department'] ?? 'Operations',
            'description' => $args['description'] ?? null,
        ];

        if ($roleId) {
            $role = OrgRole::withoutGlobalScope('current_team')->findOrFail($roleId);
            $role->update($data);

            return ['status' => 'role_updated', 'role_id' => $role->id];
        }

        $role = OrgRole::withoutGlobalScope('current_team')->create($data);

        return ['status' => 'role_created', 'role_id' => $role->id];
    }

    protected function toolGetStrategicVisionAndGoals(array $args): array
    {
        $vision = class_exists(Vision::class) ? Vision::withoutGlobalScope('current_team')->first() : null;
        $goals = class_exists(StrategicGoal::class) ? StrategicGoal::withoutGlobalScope('current_team')->get() : collect();

        return [
            'vision' => $vision ? [
                'title' => $vision->title,
                'statement' => $vision->statement ?? $vision->description,
                'target_year' => $vision->target_year ?? 2027,
            ] : null,
            'total_strategic_goals' => $goals->count(),
            'goals' => $goals->map(fn ($g) => [
                'id' => $g->id,
                'title' => $g->title,
                'pillar' => $g->pillar ?? 'Revenue',
                'target_value' => $g->target_value,
                'current_value' => $g->current_value,
                'deadline' => $g->deadline?->toIso8601String(),
                'progress_percent' => $g->progress ?? 0,
            ]),
            'tool_route' => '/app/vision-flow',
        ];
    }

    protected function toolCreateStrategicGoal(array $args): array
    {
        if (! class_exists(StrategicGoal::class)) return ['error' => 'VisionFlow module not available.'];

        $goal = StrategicGoal::withoutGlobalScope('current_team')->create([
            'user_id' => Auth::id() ?? 1,
            'title' => $args['title'],
            'description' => $args['description'] ?? null,
            'pillar' => $args['pillar'] ?? 'Revenue',
            'target_value' => $args['target_value'] ?? 100,
            'current_value' => 0,
            'deadline' => ! empty($args['deadline']) ? $args['deadline'] : now()->addMonths(6),
        ]);

        return [
            'status' => 'goal_created',
            'goal_id' => $goal->id,
            'title' => $goal->title,
            'pillar' => $goal->pillar,
        ];
    }

    protected function toolAnalyzeEisenhowerTasks(array $args): array
    {
        if (! class_exists(FocusTask::class)) return ['q1_do' => [], 'q2_plan' => [], 'q3_delegate' => [], 'q4_eliminate' => []];

        $tasks = FocusTask::withoutGlobalScope('current_team')->where('is_completed', false)->get();

        $q1 = [];
        $q2 = [];
        $q3 = [];
        $q4 = [];

        foreach ($tasks as $t) {
            $urgent = (bool) ($t->is_urgent ?? false);
            $important = (bool) ($t->is_important ?? true);

            $item = ['id' => $t->id, 'title' => $t->title, 'due_date' => $t->due_date?->toIso8601String()];

            if ($urgent && $important) $q1[] = $item;
            elseif (! $urgent && $important) $q2[] = $item;
            elseif ($urgent && ! $important) $q3[] = $item;
            else $q4[] = $item;
        }

        return [
            'summary' => [
                'q1_do_now_count' => count($q1),
                'q2_plan_strategically_count' => count($q2),
                'q3_delegate_count' => count($q3),
                'q4_eliminate_kill_count' => count($q4),
            ],
            'q1_urgent_and_important' => $q1,
            'q2_important_not_urgent' => $q2,
            'q3_urgent_not_important' => $q3,
            'q4_not_urgent_not_important' => $q4,
            'recommended_tool' => 'focus-matrix',
        ];
    }

    protected function toolCreateDelegationTask(array $args): array
    {
        $title = $args['title'] ?? 'Delegierte Aufgabe';
        $assignee = $args['assigned_to'] ?? 'Mitarbeiter';
        $expectedOutcome = $args['expected_outcome'] ?? '';
        $dueDate = $args['due_date'] ?? now()->addDays(7)->format('Y-m-d');

        $delegationId = null;
        if (class_exists(Delegation::class)) {
            $del = Delegation::withoutGlobalScope('current_team')->create([
                'user_id' => Auth::id() ?? 1,
                'title' => $title,
                'assigned_to' => $assignee,
                'outcome' => $expectedOutcome,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]);
            $delegationId = $del->id;
        }

        return [
            'status' => 'task_delegated',
            'delegation_id' => $delegationId,
            'title' => $title,
            'assigned_to' => $assignee,
            'expected_outcome' => $expectedOutcome,
            'due_date' => $dueDate,
            'tool_route' => '/app/focus-matrix',
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
                ['uri' => 'allocore://leads/summary', 'name' => 'CRM Leads & Pipeline Summary', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://crm/funnel', 'name' => 'CRM Pipeline Funnel & Conversion Analytics', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://support/tickets', 'name' => 'Customer Support Inquiries & Tickets', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://sops/library', 'name' => 'Corporate SOP & Process Playbook Library', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://kpis/benchmarks', 'name' => 'Mittelstand Standard KPI Reference Benchmarks', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://invoices/summary', 'name' => 'Open Receivables & Invoices Summary', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://org/chart', 'name' => 'Company Roles & Organization Chart', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://strategy/goals', 'name' => 'Strategic Goals & Vision Statement', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://tasks/focus-matrix', 'name' => 'Eisenhower Priority Tasks Matrix', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://integrations/status', 'name' => 'Webhooks & Third-Party Integrations', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://financial/summary', 'name' => 'Financial Overview & Active Subscriptions', 'mimeType' => 'application/json'],
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
            'allocore://leads/summary' => json_encode($this->toolSearchLeads(['limit' => 50]), JSON_PRETTY_PRINT),
            'allocore://crm/funnel' => json_encode($this->toolGetPipelineFunnelAnalytics([]), JSON_PRETTY_PRINT),
            'allocore://support/tickets' => json_encode($this->toolListSupportTickets(['limit' => 25]), JSON_PRETTY_PRINT),
            'allocore://sops/library' => json_encode($this->toolListStoredSops(['limit' => 30]), JSON_PRETTY_PRINT),
            'allocore://kpis/benchmarks' => json_encode($this->toolEvaluateKpiHealth(['metrics' => ['ebitda_margin' => 15, 'net_profit_margin' => 8, 'dso_days' => 35, 'annual_churn_rate' => 5]]), JSON_PRETTY_PRINT),
            'allocore://invoices/summary' => json_encode($this->toolListInvoicesAndReceivables(['limit' => 30]), JSON_PRETTY_PRINT),
            'allocore://org/chart' => json_encode($this->toolGetOrganizationChart([]), JSON_PRETTY_PRINT),
            'allocore://strategy/goals' => json_encode($this->toolGetStrategicVisionAndGoals([]), JSON_PRETTY_PRINT),
            'allocore://tasks/focus-matrix' => json_encode($this->toolAnalyzeEisenhowerTasks([]), JSON_PRETTY_PRINT),
            'allocore://integrations/status' => json_encode($this->toolListWebhooksAndIntegrations([]), JSON_PRETTY_PRINT),
            'allocore://financial/summary' => json_encode($this->toolGetFinancialSummary(), JSON_PRETTY_PRINT),
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
                    'name' => 'executive_audit_briefing',
                    'description' => 'Generate high-level C-Suite board presentation and executive transformation briefing from an audit.',
                    'arguments' => [['name' => 'audit_id', 'required' => true]],
                ],
                [
                    'name' => 'okr_strategy_planner',
                    'description' => 'Formulate high-leverage quarterly OKRs aligned with the 5 Allocore pillars and vision statement.',
                    'arguments' => [['name' => 'target_pillar', 'required' => true]],
                ],
                [
                    'name' => 'eisenhower_task_triager',
                    'description' => 'Triage overwhelming task backlog into actionable delegation and priority focus.',
                    'arguments' => [],
                ],
                [
                    'name' => 'invoice_dunning_generator',
                    'description' => 'Draft polite and legally structured German payment reminder / dunning notices.',
                    'arguments' => [['name' => 'invoice_id', 'required' => true], ['name' => 'dunning_level', 'required' => false]],
                ],
                [
                    'name' => 'job_description_architect',
                    'description' => 'Generate complete German B2B job description and RACI role profile.',
                    'arguments' => [['name' => 'role_title', 'required' => true], ['name' => 'department', 'required' => false]],
                ],
                [
                    'name' => 'cashflow_runway_optimizer',
                    'description' => 'Formulate a strict liquidity defense plan, burn rate compression, and runway extension strategy.',
                    'arguments' => [['name' => 'cash_on_hand', 'required' => true], ['name' => 'monthly_burn', 'required' => true]],
                ],
                [
                    'name' => 'unit_economics_advisor',
                    'description' => 'Analyze CAC, CLV, and gross margin economics to optimize customer acquisition profitability.',
                    'arguments' => [['name' => 'cac', 'required' => true], ['name' => 'clv', 'required' => true]],
                ],
                [
                    'name' => 'account_retention_strategist',
                    'description' => 'Generate proactive retention and engagement roadmap for at-risk client accounts.',
                    'arguments' => [['name' => 'team_id', 'required' => true]],
                ],
                [
                    'name' => 'seo_content_creator',
                    'description' => 'Generate high-impact German B2B thought leadership blog post linked to books & tools.',
                    'arguments' => [['name' => 'topic', 'required' => true], ['name' => 'book_id', 'required' => false]],
                ],
                [
                    'name' => 'internal_seo_optimizer',
                    'description' => 'Cross-link glossary terms inside blog articles to maximize organic search authority.',
                    'arguments' => [['name' => 'post_id', 'required' => false]],
                ],
                [
                    'name' => 'lead_nurture_strategy',
                    'description' => 'Generate personalized conversion roadmap for a specific CRM lead.',
                    'arguments' => [['name' => 'lead_id', 'required' => true]],
                ],
                [
                    'name' => 'customer_support_resolver',
                    'description' => 'Draft empathetic, accurate resolution response to customer inquiries with relevant tool links.',
                    'arguments' => [['name' => 'ticket_id', 'required' => true]],
                ],
                [
                    'name' => 'kpi_cockpit_analyzer',
                    'description' => 'Analyze company performance metrics across 5 Allocore pillars and recommend tool combinations.',
                    'arguments' => [['name' => 'company_name', 'required' => true]],
                ],
                [
                    'name' => 'sop_generator',
                    'description' => 'Draft a clear, step-by-step Standard Operating Procedure (SOP) for a business workflow.',
                    'arguments' => [['name' => 'process_name', 'required' => true], ['name' => 'role', 'required' => false]],
                ],
                [
                    'name' => 'case_study_writer',
                    'description' => 'Draft a compelling B2B transformation case study with quantifiable ROI metrics.',
                    'arguments' => [['name' => 'client_name', 'required' => true], ['name' => 'industry', 'required' => true]],
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
            'executive_audit_briefing' => "Erstellen Sie ein C-Level Vorstandsbriefing für Audit #".($args['audit_id'] ?? 1).". Formulieren Sie strategische Kernaussagen zu finanziellen Risiken, Engpässen und Quick Wins.",
            'okr_strategy_planner' => "Formulieren Sie ambitionierte, messbare Quartals-OKRs für den Fokusbereich '".($args['target_pillar'] ?? 'Revenue')."'. Gliedern Sie in 3 Objectives und jeweils 3 quantifizierbare Key Results.",
            'eisenhower_task_triager' => "Analysieren Sie die aktuelle Aufgabenlandschaft. Sortieren Sie Aufgaben nach Dringlichkeit und Wichtigkeit und erstellen Sie klare Delegationsaufträge für Quadrant 3.",
            'invoice_dunning_generator' => "Verfassen Sie eine professionelle Zahlungserinnerung / Mahnung (Stufe: ".($args['dunning_level'] ?? '1').") für Rechnung #".($args['invoice_id'] ?? 'INV-2026-001').". Wahren Sie einen partnerschaftlichen, aber verbindlichen Ton.",
            'job_description_architect' => "Erstellen Sie ein vollständiges Anforderungsprofil und eine Stellenbeschreibung für die Rolle '".($args['role_title'] ?? 'Betriebsleiter')."' in der Abteilung '".($args['department'] ?? 'Operations')."'.",
            'cashflow_runway_optimizer' => "Entwickeln Sie einen defensiven Liquiditäts- und Kostenreduktionsplan bei aktuellen Barreserven von ".($args['cash_on_hand'] ?? '50.000')." EUR und einem monatlichen Burn von ".($args['monthly_burn'] ?? '5.000')." EUR. Priorisieren Sie Working Capital Optimierung und Cash-Core Hebel.",
            'unit_economics_advisor' => "Untersuchen Sie die Wirtschaftlichkeit (Unit Economics) bei CAC von ".($args['cac'] ?? '1.000')." EUR und CLV von ".($args['clv'] ?? '4.000')." EUR. Geben Sie konkrete Skalierungs- und Preisempfehlungen.",
            'account_retention_strategist' => "Analysieren Sie das Account-Signalprofil von Team #".($args['team_id'] ?? 1)." und entwickeln Sie einen strukturierten Churn-Präventionsplan.",
            'seo_content_creator' => "Erstellen Sie einen suchmaschinenoptimierten, 8-teiligen Fachartikel zum Thema '".($args['topic'] ?? 'Unternehmensführung')."'. Binden Sie passende Allocore-Tools sowie die Buchempfehlung Box (Buch ID #".($args['book_id'] ?? 451).") nahtlos ein.",
            'internal_seo_optimizer' => "Prüfen Sie den Fachartikel #".($args['post_id'] ?? 1)." auf Vorkommen relevanter Allocore-Glossarbegriffe und generieren Sie kontextuelle Querverweise.",
            'lead_nurture_strategy' => "Analysieren Sie das Profil und die Interaktionen von Lead #".($args['lead_id'] ?? 1)." und entwickeln Sie eine maßgeschneiderte B2B-Ansprachestrategie mit ROI-Fokus.",
            'customer_support_resolver' => "Beantworten Sie Support-Ticket #".($args['ticket_id'] ?? 1)." lösungsorientiert und professionell. Verweisen Sie auf passende Allocore-Tools und Wissensartikel.",
            'kpi_cockpit_analyzer' => "Untersuchen Sie die Kennzahlenlandschaft von '".($args['company_name'] ?? 'Unternehmen')."'. Identifizieren Sie kritische Frühwarnindikatoren (Runway, Deckungsbeitrag, CAC, CLV) und schlagen Sie die passende Allocore-Modulkombination vor.",
            'sop_generator' => "Erstellen Sie eine präzise, fehlertolerante Standard Operating Procedure (SOP) für den Prozess '".($args['process_name'] ?? 'Auftragsannahme')."'. Gliedern Sie in Vorbedingungen, Einzelschritte, Qualitätskontrolle und Stellvertreter-Regelungen.",
            'case_study_writer' => "Verfassen Sie eine überzeugende Erfolgsgeschichte (Case Study) für '".($args['client_name'] ?? 'Mittelständler')."' aus der Branche '".($args['industry'] ?? 'B2B')."'. Strukturieren Sie nach Herausforderung, Lösung mit Allocore, messbaren ROI-Ergebnissen und Zitat.",
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
