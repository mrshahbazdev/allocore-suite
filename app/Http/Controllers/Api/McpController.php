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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Announcement;
use App\Models\Backup;
use App\Models\Coupon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module as ModuleFacade;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditAnswer;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\AuditPro\Models\AuditTemplate;
use Modules\BookIntelligence\Models\Author;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\QuestionMapping;
use Modules\DentalTrack\Models\Order as DentalOrder;
use Modules\DentalTrack\Models\ScanEvent;
use Modules\FocusMatrix\Models\Delegation;
use Modules\FocusMatrix\Models\Task as FocusTask;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\InvoiceItem;
use Modules\LeadQuality\Models\Contact;
use Modules\OrgMatrix\Models\Person;
use Modules\OrgMatrix\Models\Role as OrgRole;
use Modules\PlanHive\Models\Project as PlanProject;
use Modules\SopBuilder\Models\Sop;
use Modules\SopBuilder\Models\Step;
use Modules\SweetSpot\Models\Customer as SweetSpotCustomer;
use Modules\SweetSpot\Models\CustomerScore;
use Modules\TimeButler\Models\TimeEntry;
use Modules\VisionFlow\Models\StrategicGoal;
use Modules\VisionFlow\Models\Vision;
use Modules\DebtSnowballTracker\Models\Debt as SnowballDebt;
use Modules\DebtSnowballTracker\Models\Payment as SnowballPayment;
use Modules\DebtSnowballTracker\Models\Cashflow as SnowballCashflow;
use Modules\DebtSnowballTracker\Models\DebtSetting as SnowballDebtSetting;
use Modules\DebtSnowballTracker\Services\SnowballCalculatorService;
use Modules\ClusterForge\Models\Project as ClusterForgeProject;
use Modules\ClusterForge\Models\Subtopic as ClusterForgeSubtopic;
use Modules\ClusterForge\Models\Question as ClusterForgeQuestion;
use Modules\ClusterForge\Jobs\GenerateProjectJob as ClusterForgeGenerateProjectJob;
use Modules\DevManager\Models\UserStory as DevUserStory;
use Modules\DevManager\Models\Milestone as DevMilestone;
use Modules\LoopEngine\Models\Process as LoopProcess;
use Modules\LoopEngine\Models\ProcessRun as LoopProcessRun;
use Modules\CustomerSuccess\Models\Inquiry as CustomerSuccessInquiry;

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
            Log::error('MCP RPC Execution Error: '.$e->getMessage(), [
                'method' => $method,
                'params' => $params,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $clientMessage = $this->sanitizeErrorMessage($e);

            if ($method === 'tools/call') {
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Error executing tool: '.$clientMessage,
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
                    'message' => $clientMessage,
                ],
            ], 200, $this->corsHeaders());
        }
    }

    /**
     * Sanitize exception messages to prevent internal database connection / host leakage.
     */
    protected function sanitizeErrorMessage(\Throwable $e): string
    {
        if ($e instanceof \InvalidArgumentException || $e instanceof \Illuminate\Validation\ValidationException) {
            return $e->getMessage();
        }

        if ($e instanceof \Illuminate\Database\QueryException || $e instanceof \PDOException) {
            return 'A database query error occurred while processing the request.';
        }

        $msg = $e->getMessage();
        // Remove internal database connection string, host, user and database name disclosures
        $msg = preg_replace('/\(Connection:.*?\)/i', '', $msg);
        $msg = preg_replace('/Host:\s*\S+/i', '', $msg);
        $msg = preg_replace('/Database:\s*\S+/i', '', $msg);
        $msg = preg_replace('/SQLSTATE\[.*?\]:\s*/i', '', $msg);

        return trim($msg) ?: 'An unexpected error occurred while executing the MCP request.';
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

                // 2. Module & Tool Pool Governance (Admin Only Operations)
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
                    'name' => 'admin_list_modules',
                    'description' => '(Admin Only) List all system tools/modules, including database records, subscription pool status, allowed roles, and available disk modules.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'category' => ['type' => 'string', 'description' => 'Filter by category'],
                            'only_pool' => ['type' => 'boolean', 'description' => 'Filter only subscription pool tools'],
                            'only_active' => ['type' => 'boolean', 'description' => 'Filter only active tools'],
                        ],
                    ],
                ],
                [
                    'name' => 'admin_get_module_details',
                    'description' => '(Admin Only) Get comprehensive configuration, subscription pool status, allowed roles, linked plans, and disk module info for a tool.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'module_key' => ['type' => 'string', 'description' => 'Module key, ID, or disk name (e.g. "invoice-maker", "AuditPro")'],
                        ],
                        'required' => ['module_key'],
                    ],
                ],
                [
                    'name' => 'admin_install_module',
                    'description' => '(Admin Only) Install and activate a disk module, run database migrations and optional seeders, and sync it to the All Tools Bundle plan.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Disk module name (e.g. "InvoiceMaker", "AuditPro") or kebab key'],
                            'category' => ['type' => 'string', 'description' => 'Category e.g. "Produktivität & Prozesse", "Finanzen"'],
                            'icon' => ['type' => 'string', 'description' => 'Icon identifier or SVG name'],
                            'in_subscription_pool' => ['type' => 'boolean', 'default' => true, 'description' => 'Whether to add module to customer subscription pool'],
                            'run_migrations' => ['type' => 'boolean', 'default' => true, 'description' => 'Run module:migrate --force'],
                            'run_seeders' => ['type' => 'boolean', 'default' => false, 'description' => 'Run module:seed --force'],
                        ],
                        'required' => ['name'],
                    ],
                ],
                [
                    'name' => 'admin_update_module',
                    'description' => '(Admin Only) Update full tool metadata: display name, description, category, icon, badge, sort order, route prefix, allowed roles, active status, pool inclusion, or deprecated flag.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'module_key' => ['type' => 'string', 'description' => 'Module key or ID to update'],
                            'name' => ['type' => 'string', 'description' => 'Display name for the module'],
                            'description' => ['type' => 'string', 'description' => 'Detailed description of the tool'],
                            'category' => ['type' => 'string', 'description' => 'Tool category (e.g. Finanzen, Produktivität & Prozesse)'],
                            'icon' => ['type' => 'string', 'description' => 'Icon name or SVG identifier'],
                            'route_prefix' => ['type' => 'string', 'description' => 'Web route prefix for the tool'],
                            'badge_text' => ['type' => 'string', 'description' => 'Badge text e.g. "NEW", "PRO", "BETA"'],
                            'sort_order' => ['type' => 'integer', 'description' => 'Sorting priority order'],
                            'allowed_roles' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'Array of role names permitted to access this module (e.g. ["admin", "manager"])',
                            ],
                            'is_active' => ['type' => 'boolean', 'description' => 'Enable or disable the module'],
                            'in_subscription_pool' => ['type' => 'boolean', 'description' => 'Include in subscription pool bundle'],
                            'is_deprecated' => ['type' => 'boolean', 'description' => 'Mark as deprecated or archived'],
                        ],
                        'required' => ['module_key'],
                    ],
                ],
                [
                    'name' => 'admin_toggle_module',
                    'description' => '(Admin Only) Toggle or explicitly set active/inactive status for a tool/module.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'module_key' => ['type' => 'string', 'description' => 'Module key to toggle'],
                            'is_active' => ['type' => 'boolean', 'description' => 'Explicit active state (optional, toggles if omitted)'],
                        ],
                        'required' => ['module_key'],
                    ],
                ],
                [
                    'name' => 'admin_toggle_module_pool',
                    'description' => '(Admin Only) Toggle or set whether a tool/module is included in the customer subscription pool.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'module_key' => ['type' => 'string', 'description' => 'Module key'],
                            'in_pool' => ['type' => 'boolean', 'description' => 'True to include in pool, false to exclude (optional, toggles if omitted)'],
                        ],
                        'required' => ['module_key'],
                    ],
                ],
                [
                    'name' => 'admin_deprecate_module',
                    'description' => '(Admin Only) Mark a module as deprecated/archived or restore it to active status.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'module_key' => ['type' => 'string', 'description' => 'Module key'],
                            'is_deprecated' => ['type' => 'boolean', 'default' => true, 'description' => 'True to archive/deprecate, false to restore'],
                        ],
                        'required' => ['module_key'],
                    ],
                ],
                [
                    'name' => 'admin_delete_module',
                    'description' => '(Admin Only) Detach plans and remove a tool/module registration record from the system database.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'module_key' => ['type' => 'string', 'description' => 'Module key or ID to delete'],
                            'force' => ['type' => 'boolean', 'default' => false, 'description' => 'Confirm deletion'],
                        ],
                        'required' => ['module_key'],
                    ],
                ],
                [
                    'name' => 'admin_sync_module_plans',
                    'description' => '(Admin Only) Synchronize all active pool tools to the All Tools Bundle plan.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],
                [
                    'name' => 'manage_tool_pool',
                    'description' => '(Admin Only) Add or remove a module from the customer subscription tool pool.',
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
                    'description' => '(Admin Only) Mark a module as deprecated/archived or restore it to active status.',
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
                    'description' => '(Admin Only) Update display name, description, category, modern icon, badge, or sort order of a tool.',
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
                    'description' => '(Admin Only) Synchronize all active pool tools to the All Tools Bundle plan.',
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
                    'description' => 'Search business terms and explanations in the Allocore Glossary by keyword, category, or pillar.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string'],
                            'pillar' => ['type' => 'string', 'description' => 'Filter by pillar/category (e.g. Revenue, Profit, Order, Influence, Legacy)'],
                            'category' => ['type' => 'string', 'description' => 'Filter by category name'],
                            'limit' => ['type' => 'integer', 'default' => 30],
                        ],
                    ],
                ],
                [
                    'name' => 'create_or_update_glossary_term',
                    'description' => 'Create or update a business glossary term with German and simple definition, category/pillar, and published status.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'term' => ['type' => 'string'],
                            'slug' => ['type' => 'string'],
                            'definition' => ['type' => 'string'],
                            'simple_definition' => ['type' => 'string'],
                            'category' => ['type' => 'string', 'description' => 'Category or Pillar name (e.g. Profit, Revenue, Order)'],
                            'pillar' => ['type' => 'string', 'description' => 'Alias for category'],
                            'is_published' => ['type' => 'boolean', 'default' => true],
                        ],
                        'required' => ['term', 'slug', 'definition'],
                    ],
                ],
                [
                    'name' => 'delete_glossary_term',
                    'description' => 'Delete a duplicate or deprecated glossary term by its ID or slug.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'term_id' => ['type' => 'integer', 'description' => 'Glossary term ID to delete'],
                            'slug' => ['type' => 'string', 'description' => 'Glossary term slug to delete'],
                        ],
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
                [
                    'name' => 'upload_post_image',
                    'description' => 'Upload and store a post featured image or graphic (SVG, PNG, JPG, JPEG, WEBP) via base64, with sanitization and optional automatic post attachment.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'filename' => ['type' => 'string', 'description' => 'Target filename with extension (e.g. seo-guide.svg, header.png)'],
                            'content_base64' => ['type' => 'string', 'description' => 'Base64 encoded binary/SVG image data string'],
                            'post_id' => ['type' => 'integer', 'description' => 'Optional post ID to automatically attach this image as featured_image'],
                        ],
                        'required' => ['filename', 'content_base64'],
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
                [
                    'name' => 'admin_create_user',
                    'description' => '(Admin Only) Create a new user account with role, active status, and optional password.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'User full name'],
                            'email' => ['type' => 'string', 'description' => 'User email address'],
                            'password' => ['type' => 'string', 'description' => 'Optional account password (auto-generated if omitted)'],
                            'role' => ['type' => 'string', 'description' => 'Optional role (e.g. admin, user, owner)'],
                            'is_active' => ['type' => 'boolean', 'default' => true],
                            'email_verified' => ['type' => 'boolean', 'default' => true],
                            'current_team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                            'locale' => ['type' => 'string', 'enum' => ['en', 'de'], 'default' => 'de'],
                        ],
                        'required' => ['name', 'email'],
                    ],
                ],
                [
                    'name' => 'admin_update_user',
                    'description' => '(Admin Only) Update an existing user profile, role, status, email verification, or password.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer', 'description' => 'ID of user to update'],
                            'name' => ['type' => 'string'],
                            'email' => ['type' => 'string'],
                            'password' => ['type' => 'string'],
                            'role' => ['type' => 'string'],
                            'is_active' => ['type' => 'boolean'],
                            'email_verified' => ['type' => 'boolean'],
                            'current_team_id' => ['type' => 'integer'],
                            'locale' => ['type' => 'string', 'enum' => ['en', 'de']],
                        ],
                        'required' => ['user_id'],
                    ],
                ],
                [
                    'name' => 'admin_delete_user',
                    'description' => '(Admin Only) Permanently delete a user account from the platform. Self-deletion is guarded.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer', 'description' => 'ID of user to delete'],
                        ],
                        'required' => ['user_id'],
                    ],
                ],
                [
                    'name' => 'admin_get_user_details',
                    'description' => '(Admin Only) Retrieve detailed profile, roles, assigned teams, and active tool subscriptions for a specific user.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer', 'description' => 'ID of user to inspect'],
                        ],
                        'required' => ['user_id'],
                    ],
                ],
                [
                    'name' => 'admin_assign_subscription',
                    'description' => '(Admin Only) Assign or create a subscription plan for a user (e.g. All Tools Bundle, specific plan, custom duration).',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer', 'description' => 'User ID'],
                            'plan_id' => ['type' => 'integer', 'description' => 'Optional specific Plan ID'],
                            'plan_slug' => ['type' => 'string', 'description' => 'Optional Plan slug (e.g. all-tools)'],
                            'billing_interval' => ['type' => 'string', 'enum' => ['monthly', 'yearly'], 'default' => 'monthly'],
                            'payment_method' => ['type' => 'string', 'enum' => ['manual', 'stripe', 'bank', 'free'], 'default' => 'manual'],
                            'status' => ['type' => 'string', 'enum' => ['active', 'pending', 'cancelled'], 'default' => 'active'],
                            'duration_days' => ['type' => 'integer', 'description' => 'Optional validity length in days from now'],
                            'admin_note' => ['type' => 'string', 'description' => 'Internal note or reference'],
                        ],
                        'required' => ['user_id'],
                    ],
                ],
                [
                    'name' => 'admin_update_subscription',
                    'description' => '(Admin Only) Update status, change plan, or extend validity of an existing subscription.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'subscription_id' => ['type' => 'integer', 'description' => 'Tool subscription ID'],
                            'status' => ['type' => 'string', 'enum' => ['active', 'pending', 'cancelled']],
                            'plan_id' => ['type' => 'integer'],
                            'billing_interval' => ['type' => 'string', 'enum' => ['monthly', 'yearly']],
                            'extend_days' => ['type' => 'integer', 'description' => 'Extend subscription expiration by N days'],
                            'admin_note' => ['type' => 'string'],
                        ],
                        'required' => ['subscription_id'],
                    ],
                ],
                [
                    'name' => 'admin_cancel_subscription',
                    'description' => '(Admin Only) Immediately cancel an active user subscription.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'subscription_id' => ['type' => 'integer', 'description' => 'Tool subscription ID to cancel'],
                            'admin_note' => ['type' => 'string', 'description' => 'Optional cancellation reason'],
                        ],
                        'required' => ['subscription_id'],
                    ],
                ],
                [
                    'name' => 'admin_list_plans',
                    'description' => '(Admin Only) List all subscription plans available in the system with pricing and module counts.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => (object) [],
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
                [
                    'name' => 'log_work_time_entry',
                    'description' => 'Log or record a work time entry for a team member in TimeButler.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'date' => ['type' => 'string', 'description' => 'Work date (YYYY-MM-DD)'],
                            'start_time' => ['type' => 'string', 'description' => 'Start time (HH:MM)'],
                            'end_time' => ['type' => 'string', 'description' => 'End time (HH:MM)'],
                            'break_minutes' => ['type' => 'integer', 'default' => 0],
                            'notes' => ['type' => 'string'],
                            'user_id' => ['type' => 'integer', 'description' => 'Optional user ID'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['date', 'start_time', 'end_time'],
                    ],
                ],
                [
                    'name' => 'get_team_workforce_summary',
                    'description' => 'Get workforce time tracking stats, active hours, and employee summary from TimeButler.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'start_date' => ['type' => 'string', 'description' => 'Start date (YYYY-MM-DD)'],
                            'end_date' => ['type' => 'string', 'description' => 'End date (YYYY-MM-DD)'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                    ],
                ],
                [
                    'name' => 'score_customer_sweet_spot',
                    'description' => 'Calculate or register a multi-factor Sweet Spot score for a customer/client.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Client / Customer company name'],
                            'industry' => ['type' => 'string'],
                            'revenue' => ['type' => 'number', 'description' => 'Annual or project revenue (EUR)'],
                            'profit_margin_eur' => ['type' => 'number', 'description' => 'Gross profit contribution (EUR)'],
                            'effort_hours' => ['type' => 'number', 'description' => 'Total hours of effort spent'],
                            'chemistry_score' => ['type' => 'integer', 'description' => 'Working chemistry (1-10)'],
                            'growth_score' => ['type' => 'integer', 'description' => 'Growth & referral potential (1-10)'],
                            'payment_willingness' => ['type' => 'integer', 'description' => 'Payment speed and terms adherence (1-10)'],
                            'team_id' => ['type' => 'integer'],
                        ],
                        'required' => ['name', 'revenue', 'profit_margin_eur', 'effort_hours'],
                    ],
                ],
                [
                    'name' => 'list_sweet_spot_rankings',
                    'description' => 'List and rank clients/customers by profitability, effort, and Sweet Spot score.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'top_only' => ['type' => 'boolean', 'default' => false],
                            'limit' => ['type' => 'integer', 'default' => 20],
                            'team_id' => ['type' => 'integer'],
                        ],
                    ],
                ],
                [
                    'name' => 'create_project_milestone',
                    'description' => 'Create a project goal or key milestone in PlanHive.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'project_id' => ['type' => 'integer'],
                            'title' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'target_date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                            'progress' => ['type' => 'integer', 'default' => 0],
                            'status' => ['type' => 'string', 'default' => 'in_progress'],
                            'team_id' => ['type' => 'integer'],
                        ],
                        'required' => ['project_id', 'title'],
                    ],
                ],
                [
                    'name' => 'get_project_portfolio_overview',
                    'description' => 'Retrieve an executive overview of all projects, statuses, and goal progress from PlanHive.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string'],
                            'limit' => ['type' => 'integer', 'default' => 20],
                            'team_id' => ['type' => 'integer'],
                        ],
                    ],
                ],
                [
                    'name' => 'track_production_order_status',
                    'description' => 'Track dental / lab production order status, step progress, and estimated delivery from DentalTrack.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'order_id' => ['type' => 'integer'],
                            'tracking_code' => ['type' => 'string'],
                            'qr_code' => ['type' => 'string'],
                        ],
                    ],
                ],
                [
                    'name' => 'log_workstation_scan_event',
                    'description' => 'Log a barcode/QR workstation scan event for a production order in DentalTrack.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'order_id' => ['type' => 'integer'],
                            'workstation_id' => ['type' => 'integer'],
                            'order_step_id' => ['type' => 'integer'],
                            'event_type' => ['type' => 'string', 'enum' => ['start', 'complete', 'pause', 'transfer_to_waiting'], 'default' => 'start'],
                            'notes' => ['type' => 'string'],
                            'duration_seconds' => ['type' => 'integer'],
                            'team_id' => ['type' => 'integer'],
                        ],
                        'required' => ['order_id'],
                    ],
                ],
                [
                    'name' => 'list_snowball_debts',
                    'description' => 'List all debts and liabilities for the current team or company, including balance, APR, minimum payments, and payoff status.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'enum' => ['active', 'paid', 'all'], 'default' => 'active'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                            'limit' => ['type' => 'integer', 'default' => 50],
                        ],
                    ],
                ],
                [
                    'name' => 'create_or_update_snowball_debt',
                    'description' => 'Create a new debt entry or update existing balance, APR, minimum payment, or creditor.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'description' => 'Optional debt ID for updates'],
                            'name' => ['type' => 'string', 'description' => 'Debt / loan name'],
                            'creditor' => ['type' => 'string', 'description' => 'Bank / Creditor name'],
                            'original_balance' => ['type' => 'number', 'description' => 'Original debt amount in EUR'],
                            'current_balance' => ['type' => 'number', 'description' => 'Current outstanding balance in EUR'],
                            'interest_rate' => ['type' => 'number', 'description' => 'Annual interest rate APR (%)'],
                            'minimum_payment' => ['type' => 'number', 'description' => 'Minimum monthly payment in EUR'],
                            'due_day' => ['type' => 'integer', 'description' => 'Monthly due day (1-31)'],
                            'category' => ['type' => 'string', 'description' => 'Category (loan, credit_card, tax, supplier, other)'],
                            'notes' => ['type' => 'string'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['name', 'current_balance', 'minimum_payment'],
                    ],
                ],
                [
                    'name' => 'log_snowball_payment',
                    'description' => 'Log an extra or regular debt payment towards a specific debt, automatically recalculating balance and payoff.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'debt_id' => ['type' => 'integer', 'description' => 'Target debt ID'],
                            'amount' => ['type' => 'number', 'description' => 'Payment amount in EUR'],
                            'payment_date' => ['type' => 'string', 'description' => 'Payment date (YYYY-MM-DD)'],
                            'note' => ['type' => 'string'],
                        ],
                        'required' => ['debt_id', 'amount'],
                    ],
                ],
                [
                    'name' => 'calculate_snowball_payoff_plan',
                    'description' => 'Simulate and compare Debt Snowball (lowest balance first) vs Debt Avalanche (highest APR first) payoff schedules with amortization and interest savings.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'strategy' => ['type' => 'string', 'enum' => ['snowball', 'avalanche', 'custom'], 'default' => 'snowball'],
                            'extra_monthly_budget' => ['type' => 'number', 'description' => 'Extra monthly cashflow allocated to accelerated debt payoff in EUR', 'default' => 0],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                    ],
                ],
                [
                    'name' => 'get_snowball_financial_summary',
                    'description' => 'Retrieve high-level debt payoff KPIs, total liability, monthly debt service, estimated debt-free date, total interest savings, and cashflow surplus.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                    ],
                ],

                // 11. Admin Coupons & Discounts
                [
                    'name' => 'admin_list_coupons',
                    'description' => '(Admin Only) List promotional and discount coupons with redemption counts and expiry dates.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'only_active' => ['type' => 'boolean', 'description' => 'Filter only active coupons'],
                        ],
                    ],
                ],
                [
                    'name' => 'admin_create_coupon',
                    'description' => '(Admin Only) Create a promotional discount coupon (percentage or fixed discount).',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'code' => ['type' => 'string', 'description' => 'Coupon code (e.g. SUMMER2026, WELCOME10)'],
                            'type' => ['type' => 'string', 'enum' => ['percent', 'fixed'], 'default' => 'percent'],
                            'value' => ['type' => 'number', 'description' => 'Discount percentage or fixed amount in EUR'],
                            'max_uses' => ['type' => 'integer', 'description' => 'Maximum allowed redemptions (null for unlimited)'],
                            'description' => ['type' => 'string', 'description' => 'Internal or promo description'],
                            'is_active' => ['type' => 'boolean', 'default' => true],
                            'expires_at' => ['type' => 'string', 'description' => 'Expiry datetime (YYYY-MM-DD or ISO 8601)'],
                        ],
                        'required' => ['code', 'value'],
                    ],
                ],
                [
                    'name' => 'admin_delete_coupon',
                    'description' => '(Admin Only) Delete or remove a discount coupon.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'code' => ['type' => 'string', 'description' => 'Coupon code or ID to delete'],
                        ],
                        'required' => ['code'],
                    ],
                ],

                // 12. Admin Maintenance, Backups & System Logs
                [
                    'name' => 'admin_create_backup',
                    'description' => '(Admin Only) Trigger and create a database SQL dump backup.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'disk' => ['type' => 'string', 'enum' => ['local', 's3'], 'default' => 'local'],
                        ],
                    ],
                ],
                [
                    'name' => 'admin_list_backups',
                    'description' => '(Admin Only) List all database and system backup snapshots.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],
                [
                    'name' => 'admin_read_error_logs',
                    'description' => '(Admin Only) Read recent application error logs from storage/logs/laravel.log.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'lines' => ['type' => 'integer', 'default' => 50, 'description' => 'Number of tail lines to retrieve (10-150)'],
                        ],
                    ],
                ],
                [
                    'name' => 'admin_list_failed_jobs',
                    'description' => '(Admin Only) List failed background queue worker jobs.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'default' => 20],
                        ],
                    ],
                ],
                [
                    'name' => 'admin_retry_failed_job',
                    'description' => '(Admin Only) Retry one or all failed queue jobs.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'string', 'default' => 'all', 'description' => 'Job ID or "all" to retry all failed jobs'],
                        ],
                    ],
                ],
                [
                    'name' => 'admin_toggle_maintenance',
                    'description' => '(Admin Only) Put the application into or out of maintenance mode (artisan down/up).',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'enable' => ['type' => 'boolean', 'description' => 'True to enable maintenance mode (offline), false to make live'],
                            'secret' => ['type' => 'string', 'description' => 'Optional bypass secret token for maintenance mode'],
                        ],
                        'required' => ['enable'],
                    ],
                ],

                // 13. Admin Announcements & Settings
                [
                    'name' => 'admin_list_announcements',
                    'description' => '(Admin Only) List site-wide dashboard notifications and banner announcements.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],
                [
                    'name' => 'admin_create_announcement',
                    'description' => '(Admin Only) Create a dashboard announcement banner for platform users.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string', 'description' => 'Announcement headline'],
                            'body' => ['type' => 'string', 'description' => 'Announcement text or message'],
                            'type' => ['type' => 'string', 'enum' => ['info', 'warning', 'success', 'danger'], 'default' => 'info'],
                            'is_active' => ['type' => 'boolean', 'default' => true],
                            'starts_at' => ['type' => 'string', 'description' => 'Optional start datetime'],
                            'ends_at' => ['type' => 'string', 'description' => 'Optional expiry datetime'],
                        ],
                        'required' => ['title', 'body'],
                    ],
                ],
                [
                    'name' => 'admin_delete_announcement',
                    'description' => '(Admin Only) Delete an announcement by ID.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'description' => 'Announcement ID'],
                        ],
                        'required' => ['id'],
                    ],
                ],
                [
                    'name' => 'admin_get_settings',
                    'description' => '(Admin Only) Retrieve general system environment, active modules count, and platform statistics.',
                    'inputSchema' => ['type' => 'object', 'properties' => (object) []],
                ],

                // 14. ClusterForge (SEO & Keyword Topic Clusters)
                [
                    'name' => 'clusterforge_list_projects',
                    'description' => 'List and search SEO keyword and topic cluster projects with progress and status filters.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'search' => ['type' => 'string', 'description' => 'Search projects by topic, website or pillar title'],
                            'status' => ['type' => 'string', 'description' => 'Optional status filter (e.g. pending, completed, failed)'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                            'limit' => ['type' => 'integer', 'description' => 'Max number of results (default 25)'],
                        ],
                    ],
                ],
                [
                    'name' => 'clusterforge_get_project',
                    'description' => 'Retrieve detailed information, subtopics, keyword metrics, and questions for a ClusterForge project.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'project_id' => ['type' => 'integer', 'description' => 'The ID of the ClusterForge project'],
                            'include_content' => ['type' => 'boolean', 'description' => 'Whether to include the full pillar page markdown content'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['project_id'],
                    ],
                ],
                [
                    'name' => 'clusterforge_search_keywords',
                    'description' => 'Search subtopics, long-tail keywords, search volumes, and CPC across ClusterForge projects.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Keyword term, phrase, or title to search for'],
                            'project_id' => ['type' => 'integer', 'description' => 'Filter by specific project ID'],
                            'min_volume' => ['type' => 'integer', 'description' => 'Minimum monthly search volume'],
                            'max_cpc' => ['type' => 'number', 'description' => 'Maximum CPC limit'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                            'limit' => ['type' => 'integer', 'description' => 'Max number of keywords to return (default 30)'],
                        ],
                    ],
                ],
                [
                    'name' => 'clusterforge_get_subtopic',
                    'description' => 'Retrieve full cluster details for a specific subtopic, including long-tail keyword, questions, and full cluster article content.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'subtopic_id' => ['type' => 'integer', 'description' => 'The subtopic ID'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['subtopic_id'],
                    ],
                ],
                [
                    'name' => 'clusterforge_search_questions',
                    'description' => 'Search through all AI-generated SEO research questions and answers within ClusterForge topic clusters.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Search term matching question or answer text'],
                            'project_id' => ['type' => 'integer', 'description' => 'Filter by specific project ID'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                            'limit' => ['type' => 'integer', 'description' => 'Max results to return (default 25)'],
                        ],
                        'required' => ['query'],
                    ],
                ],
                [
                    'name' => 'clusterforge_create_project',
                    'description' => 'Create a new AI-driven SEO topic cluster project and optionally queue cluster generation immediately.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'topic' => ['type' => 'string', 'description' => 'Core topic or seed keyword'],
                            'website' => ['type' => 'string', 'description' => 'Target website URL'],
                            'language' => ['type' => 'string', 'enum' => ['de', 'en'], 'default' => 'de'],
                            'pillar_title' => ['type' => 'string', 'description' => 'Optional title for the pillar page'],
                            'start_generation' => ['type' => 'boolean', 'default' => true, 'description' => 'Automatically dispatch AI cluster generation job in background'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['topic'],
                    ],
                ],
                [
                    'name' => 'clusterforge_retry_project',
                    'description' => 'Retry or restart generation for a pending or failed ClusterForge topic cluster project.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'project_id' => ['type' => 'integer', 'description' => 'Project ID to restart'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['project_id'],
                    ],
                ],
                [
                    'name' => 'clusterforge_export_content',
                    'description' => 'Export full markdown content (with frontmatter metadata) for a pillar page or subtopic cluster page.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'project_id' => ['type' => 'integer', 'description' => 'Project ID'],
                            'type' => ['type' => 'string', 'enum' => ['pillar', 'cluster'], 'default' => 'pillar', 'description' => 'Export type: pillar page or cluster subtopic page'],
                            'subtopic_id' => ['type' => 'integer', 'description' => 'Required if type is cluster'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['project_id'],
                    ],
                ],
                [
                    'name' => 'clusterforge_delete_project',
                    'description' => 'Delete an SEO topic cluster project and its associated subtopics and questions.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'project_id' => ['type' => 'integer', 'description' => 'Project ID to delete'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['project_id'],
                    ],
                ],
                [
                    'name' => 'clusterforge_save_subtopics',
                    'description' => 'Save externally generated subtopics for a ClusterForge project (bypasses Gemini). Use this after you have generated the subtopic list yourself. Sets project status to generating_questions.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'project_id' => ['type' => 'integer', 'description' => 'The ClusterForge project ID'],
                            'subtopics' => [
                                'type' => 'array',
                                'description' => 'Array of subtopic objects to save',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'title' => ['type' => 'string', 'description' => 'Subtopic title'],
                                        'long_tail_keyword' => ['type' => 'string', 'description' => 'Long-tail keyword'],
                                        'description' => ['type' => 'string', 'description' => 'Short description of the subtopic'],
                                    ],
                                    'required' => ['title'],
                                ],
                            ],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['project_id', 'subtopics'],
                    ],
                ],
                [
                    'name' => 'clusterforge_save_questions',
                    'description' => 'Save externally generated questions for a specific ClusterForge subtopic (bypasses Gemini). Replaces any existing questions for that subtopic.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'subtopic_id' => ['type' => 'integer', 'description' => 'The subtopic ID to save questions for'],
                            'questions' => [
                                'type' => 'array',
                                'description' => 'Array of question strings (up to 10)',
                                'items' => ['type' => 'string'],
                            ],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['subtopic_id', 'questions'],
                    ],
                ],
                [
                    'name' => 'clusterforge_save_answers',
                    'description' => 'Save externally generated answers for all questions in a ClusterForge subtopic (bypasses Gemini). answers array must match question order.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'subtopic_id' => ['type' => 'integer', 'description' => 'The subtopic ID'],
                            'answers' => [
                                'type' => 'array',
                                'description' => 'Array of answer strings in the same order as the subtopic questions',
                                'items' => ['type' => 'string'],
                            ],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['subtopic_id', 'answers'],
                    ],
                ],
                [
                    'name' => 'clusterforge_save_cluster_content',
                    'description' => 'Save externally generated cluster page content (title, meta, intro markdown) for a subtopic (bypasses Gemini). Full cluster_content is auto-assembled from title + intro + Q&A.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'subtopic_id' => ['type' => 'integer', 'description' => 'The subtopic ID'],
                            'title' => ['type' => 'string', 'description' => 'H1 page title for the cluster page'],
                            'meta_description' => ['type' => 'string', 'description' => 'SEO meta description (150-160 chars)'],
                            'introduction_markdown' => ['type' => 'string', 'description' => 'Introduction section markdown (200-350 words)'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['subtopic_id', 'title'],
                    ],
                ],
                [
                    'name' => 'clusterforge_save_pillar_content',
                    'description' => 'Save externally generated pillar page content (title, meta, full markdown body) for a ClusterForge project and mark it completed (bypasses Gemini).',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'project_id' => ['type' => 'integer', 'description' => 'The ClusterForge project ID'],
                            'title' => ['type' => 'string', 'description' => 'H1 pillar page title'],
                            'meta_description' => ['type' => 'string', 'description' => 'SEO meta description for the pillar page'],
                            'content_markdown' => ['type' => 'string', 'description' => 'Full pillar page body in Markdown (~700-1100 words)'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['project_id', 'title', 'content_markdown'],
                    ],
                ],

                // 15. DevManager (Agile/Scrum Backlog & Roadmaps)
                [
                    'name' => 'devmanager_list_user_stories',
                    'description' => 'List agile development user stories, story points, and backlog items from DevManager.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                    ],
                ],
                [
                    'name' => 'devmanager_create_user_story',
                    'description' => 'Create a user story or backlog item in DevManager.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string', 'description' => 'User story title'],
                            'description' => ['type' => 'string', 'description' => 'Detailed acceptance criteria or user story text'],
                            'status' => ['type' => 'string', 'enum' => ['backlog', 'todo', 'in_progress', 'done'], 'default' => 'backlog'],
                            'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high', 'urgent'], 'default' => 'medium'],
                            'story_points' => ['type' => 'integer', 'default' => 3],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['title'],
                    ],
                ],
                [
                    'name' => 'devmanager_list_milestones',
                    'description' => 'List development milestones, releases, and roadmap target dates.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                    ],
                ],

                // 16. LoopEngine (Automated Process Orchestration)
                [
                    'name' => 'loopengine_list_processes',
                    'description' => 'List automated multi-step business processes and workflow templates from LoopEngine.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                    ],
                ],
                [
                    'name' => 'loopengine_trigger_process_run',
                    'description' => 'Trigger an automated workflow execution run for a business process.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'process_id' => ['type' => 'integer', 'description' => 'Process ID to execute'],
                            'input_data' => ['type' => 'object', 'description' => 'Input payload parameters for the process run'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['process_id'],
                    ],
                ],

                // 17. CustomerSuccess (Root Cause & Retention Intelligence)
                [
                    'name' => 'customersuccess_list_inquiries',
                    'description' => 'List diagnosed customer success inquiries, problems, and prioritized retention cases.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                    ],
                ],
                [
                    'name' => 'customersuccess_diagnose_inquiry',
                    'description' => 'Log and structure a customer success issue with root cause analysis, consequences, and recommended action steps.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'question' => ['type' => 'string', 'description' => 'Client inquiry or presenting question'],
                            'problem' => ['type' => 'string', 'description' => 'Diagnosed core underlying problem'],
                            'root_cause' => ['type' => 'string', 'description' => 'Identified root cause'],
                            'consequences' => ['type' => 'string', 'description' => 'Business consequences if unresolved'],
                            'recommended_actions' => ['type' => 'string', 'description' => 'Actionable step-by-step resolution plan'],
                            'priority' => ['type' => 'string', 'enum' => ['low', 'medium', 'high', 'critical'], 'default' => 'medium'],
                            'module_key' => ['type' => 'string', 'description' => 'Associated Allocore module key'],
                            'team_id' => ['type' => 'integer', 'description' => 'Optional team ID'],
                        ],
                        'required' => ['question', 'problem'],
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
            'admin_list_modules' => $this->toolAdminListModules($arguments),
            'admin_get_module_details', 'get_module_details' => $this->toolAdminGetModuleDetails($arguments),
            'admin_install_module', 'install_module' => $this->toolAdminInstallModule($arguments),
            'admin_update_module', 'update_module_metadata' => $this->toolAdminUpdateModule($arguments),
            'admin_toggle_module', 'toggle_module' => $this->toolAdminToggleModule($arguments),
            'admin_toggle_module_pool', 'manage_tool_pool' => $this->toolAdminToggleModulePool($arguments),
            'admin_deprecate_module', 'deprecate_module' => $this->toolAdminDeprecateModule($arguments),
            'admin_delete_module', 'delete_module' => $this->toolAdminDeleteModule($arguments),
            'admin_sync_module_plans', 'sync_subscription_plans' => $this->toolAdminSyncModulePlans(),
            'diagnose_audit_gaps' => $this->toolDiagnoseAuditGaps($arguments),
            'calculate_pillar_scores' => $this->toolCalculatePillarScores($arguments),
            'generate_action_plan' => $this->toolGenerateActionPlan($arguments),
            'search_books' => $this->toolSearchBooks($arguments),
            'get_book_details' => $this->toolGetBookDetails($arguments),
            'create_or_update_book' => $this->toolCreateOrUpdateBook($arguments),
            'search_glossary_terms' => $this->toolSearchGlossaryTerms($arguments),
            'create_or_update_glossary_term' => $this->toolCreateOrUpdateGlossaryTerm($arguments),
            'delete_glossary_term' => $this->toolDeleteGlossaryTerm($arguments),
            'search_blog_posts' => $this->toolSearchBlogPosts($arguments),
            'get_post_details' => $this->toolGetPostDetails($arguments),
            'create_or_update_post' => $this->toolCreateOrUpdatePost($arguments),
            'upload_post_image' => $this->toolUploadPostImage($arguments),
            'search_users_and_teams' => $this->toolSearchUsersAndTeams($arguments),
            'get_user_subscription_status' => $this->toolGetUserSubscriptionStatus($arguments),
            'admin_create_user', 'create_user' => $this->toolAdminCreateUser($arguments),
            'admin_update_user', 'update_user' => $this->toolAdminUpdateUser($arguments),
            'admin_delete_user', 'delete_user' => $this->toolAdminDeleteUser($arguments),
            'admin_get_user_details', 'get_user_details' => $this->toolAdminGetUserDetails($arguments),
            'admin_assign_subscription', 'assign_user_subscription' => $this->toolAdminAssignSubscription($arguments),
            'admin_update_subscription', 'update_user_subscription' => $this->toolAdminUpdateSubscription($arguments),
            'admin_cancel_subscription', 'cancel_user_subscription' => $this->toolAdminCancelSubscription($arguments),
            'admin_list_plans', 'list_plans' => $this->toolAdminListPlans(),
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
            'log_work_time_entry' => $this->toolLogWorkTimeEntry($arguments),
            'get_team_workforce_summary' => $this->toolGetTeamWorkforceSummary($arguments),
            'score_customer_sweet_spot' => $this->toolScoreCustomerSweetSpot($arguments),
            'list_sweet_spot_rankings' => $this->toolListSweetSpotRankings($arguments),
            'create_project_milestone' => $this->toolCreateProjectMilestone($arguments),
            'get_project_portfolio_overview' => $this->toolGetProjectPortfolioOverview($arguments),
            'track_production_order_status' => $this->toolTrackProductionOrderStatus($arguments),
            'log_workstation_scan_event' => $this->toolLogWorkstationScanEvent($arguments),
            'list_snowball_debts' => $this->toolListSnowballDebts($arguments),
            'create_or_update_snowball_debt' => $this->toolCreateOrUpdateSnowballDebt($arguments),
            'log_snowball_payment' => $this->toolLogSnowballPayment($arguments),
            'calculate_snowball_payoff_plan' => $this->toolCalculateSnowballPayoffPlan($arguments),
            'get_snowball_financial_summary' => $this->toolGetSnowballFinancialSummary($arguments),

            // Admin Coupons
            'admin_list_coupons', 'list_coupons' => $this->toolAdminListCoupons($arguments),
            'admin_create_coupon', 'create_coupon' => $this->toolAdminCreateCoupon($arguments),
            'admin_delete_coupon', 'delete_coupon' => $this->toolAdminDeleteCoupon($arguments),

            // Admin System, Backups & Maintenance
            'admin_create_backup', 'create_backup' => $this->toolAdminCreateBackup($arguments),
            'admin_list_backups', 'list_backups' => $this->toolAdminListBackups(),
            'admin_read_error_logs', 'read_error_logs' => $this->toolAdminReadErrorLogs($arguments),
            'admin_list_failed_jobs', 'list_failed_jobs' => $this->toolAdminListFailedJobs($arguments),
            'admin_retry_failed_job', 'retry_failed_job' => $this->toolAdminRetryFailedJob($arguments),
            'admin_toggle_maintenance', 'toggle_maintenance' => $this->toolAdminToggleMaintenance($arguments),

            // Admin Announcements & Settings
            'admin_list_announcements', 'list_announcements' => $this->toolAdminListAnnouncements(),
            'admin_create_announcement', 'create_announcement' => $this->toolAdminCreateAnnouncement($arguments),
            'admin_delete_announcement', 'delete_announcement' => $this->toolAdminDeleteAnnouncement($arguments),
            'admin_get_settings', 'get_settings' => $this->toolAdminGetSettings(),

            // ClusterForge Tools
            'clusterforge_list_projects' => $this->toolClusterforgeListProjects($arguments),
            'clusterforge_get_project' => $this->toolClusterforgeGetProject($arguments),
            'clusterforge_search_keywords' => $this->toolClusterforgeSearchKeywords($arguments),
            'clusterforge_get_subtopic' => $this->toolClusterforgeGetSubtopic($arguments),
            'clusterforge_search_questions' => $this->toolClusterforgeSearchQuestions($arguments),
            'clusterforge_create_project' => $this->toolClusterforgeCreateProject($arguments),
            'clusterforge_retry_project' => $this->toolClusterforgeRetryProject($arguments),
            'clusterforge_export_content' => $this->toolClusterforgeExportContent($arguments),
            'clusterforge_delete_project' => $this->toolClusterforgeDeleteProject($arguments),
            'clusterforge_save_subtopics' => $this->toolClusterforgeSaveSubtopics($arguments),
            'clusterforge_save_questions' => $this->toolClusterforgeSaveQuestions($arguments),
            'clusterforge_save_answers' => $this->toolClusterforgeSaveAnswers($arguments),
            'clusterforge_save_cluster_content' => $this->toolClusterforgeSaveClusterContent($arguments),
            'clusterforge_save_pillar_content' => $this->toolClusterforgeSavePillarContent($arguments),

            // DevManager Tools
            'devmanager_list_user_stories' => $this->toolDevmanagerListUserStories($arguments),
            'devmanager_create_user_story' => $this->toolDevmanagerCreateUserStory($arguments),
            'devmanager_list_milestones' => $this->toolDevmanagerListMilestones($arguments),

            // LoopEngine Tools
            'loopengine_list_processes' => $this->toolLoopengineListProcesses($arguments),
            'loopengine_trigger_process_run' => $this->toolLoopengineTriggerProcessRun($arguments),

            // CustomerSuccess Tools
            'customersuccess_list_inquiries' => $this->toolCustomersuccessListInquiries($arguments),
            'customersuccess_diagnose_inquiry' => $this->toolCustomersuccessDiagnoseInquiry($arguments),
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

        $modules = $query->orderBy('sort_order')->orderBy('name')->get();

        return [
            'total' => $modules->count(),
            'modules' => $modules->map(fn ($m) => [
                'id' => $m->id,
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

    /**
     * (Admin Only) List all modules with full admin metadata and disk detection.
     */
    protected function toolAdminListModules(array $args): array
    {
        $this->ensureAdmin();

        $query = Module::with(['plans:id,name,slug']);
        if (! empty($args['category'])) $query->where('category', $args['category']);
        if (! empty($args['only_pool'])) $query->inSubscriptionPool();
        if (! empty($args['only_active'])) $query->active();

        $modules = $query->orderBy('sort_order')->orderBy('name')->get();

        // Scan disk modules
        $diskModules = [];
        $uninstalledDiskModules = [];
        try {
            $installedKeys = $modules->pluck('key')->all();
            foreach (ModuleFacade::all() as $dm) {
                $k = Str::kebab($dm->getName());
                $isInstalled = in_array($k, $installedKeys, true);
                $dData = [
                    'name' => $dm->getName(),
                    'key' => $k,
                    'alias' => $dm->get('alias', $k),
                    'description' => $dm->get('description', ''),
                    'path' => $dm->getPath(),
                    'is_enabled_on_disk' => $dm->isEnabled(),
                    'is_installed_in_db' => $isInstalled,
                ];
                $diskModules[] = $dData;
                if (! $isInstalled) {
                    $uninstalledDiskModules[] = $dData;
                }
            }
        } catch (\Throwable $e) {
            // Ignore disk scan error
        }

        return [
            'total_installed' => $modules->count(),
            'total_disk_modules' => count($diskModules),
            'active_count' => Module::where('is_active', true)->where('is_deprecated', false)->count(),
            'pool_count' => Module::inSubscriptionPool()->count(),
            'deprecated_count' => Module::where('is_deprecated', true)->count(),
            'modules' => $modules->map(fn ($m) => [
                'id' => $m->id,
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
                'allowed_roles' => $m->allowed_roles ?? [],
                'plans' => $m->plans->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'slug' => $p->slug]),
            ]),
            'uninstalled_disk_modules' => $uninstalledDiskModules,
        ];
    }

    /**
     * (Admin Only) Get complete details for a module/tool.
     */
    protected function toolAdminGetModuleDetails(array $args): array
    {
        $this->ensureAdmin();

        $keyOrId = $args['module_key'] ?? ($args['id'] ?? null);
        if (! $keyOrId) {
            throw new \InvalidArgumentException('module_key or id is required.');
        }

        $module = is_numeric($keyOrId)
            ? Module::with('plans')->find($keyOrId)
            : Module::with('plans')->where('key', $keyOrId)->first();

        $diskInfo = null;
        try {
            $lookup = $module ? $module->name : $keyOrId;
            $dm = ModuleFacade::find($lookup) ?? ModuleFacade::find(Str::studly($lookup)) ?? ModuleFacade::find(Str::kebab($lookup));
            if (! $dm) {
                foreach (ModuleFacade::all() as $m) {
                    if (Str::kebab($m->getName()) === Str::kebab($keyOrId)) {
                        $dm = $m;
                        break;
                    }
                }
            }
            if ($dm) {
                $diskInfo = [
                    'name' => $dm->getName(),
                    'alias' => $dm->get('alias', Str::kebab($dm->getName())),
                    'description' => $dm->get('description', ''),
                    'path' => $dm->getPath(),
                    'is_enabled' => $dm->isEnabled(),
                ];
            }
        } catch (\Throwable $e) {
        }

        if (! $module && ! $diskInfo) {
            throw new \InvalidArgumentException("Module '{$keyOrId}' not found in database or disk.");
        }

        return [
            'is_installed' => (bool) $module,
            'module' => $module ? [
                'id' => $module->id,
                'key' => $module->key,
                'name' => $module->getRawOriginal('name'),
                'description' => $module->getRawOriginal('description'),
                'category' => $module->category,
                'icon' => $module->icon,
                'route_prefix' => $module->route_prefix,
                'in_subscription_pool' => (bool) $module->in_subscription_pool,
                'is_active' => (bool) $module->is_active,
                'is_deprecated' => (bool) $module->is_deprecated,
                'badge_text' => $module->badge_text,
                'sort_order' => $module->sort_order,
                'allowed_roles' => $module->allowed_roles ?? [],
                'plans' => $module->plans->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'slug' => $p->slug]),
                'created_at' => $module->created_at?->toIso8601String(),
                'updated_at' => $module->updated_at?->toIso8601String(),
            ] : null,
            'disk_module' => $diskInfo,
        ];
    }

    /**
     * (Admin Only) Install and activate a module from disk.
     */
    protected function toolAdminInstallModule(array $args): array
    {
        $this->ensureAdmin();

        $name = trim($args['name'] ?? ($args['module_key'] ?? ''));
        if (empty($name)) {
            throw new \InvalidArgumentException('name (disk module name or key) is required to install.');
        }

        // Try exact match, studly, or kebab
        $diskModule = ModuleFacade::find($name)
            ?? ModuleFacade::find(Str::studly($name))
            ?? ModuleFacade::find(Str::kebab($name));

        if (! $diskModule) {
            // Find by matching kebab
            foreach (ModuleFacade::all() as $m) {
                if (Str::kebab($m->getName()) === Str::kebab($name)) {
                    $diskModule = $m;
                    break;
                }
            }
        }

        if (! $diskModule) {
            $available = collect(ModuleFacade::all())->map(fn ($m) => $m->getName())->values()->all();
            throw new \InvalidArgumentException("Module '{$name}' not found on disk. Available disk modules: " . implode(', ', $available));
        }

        $key = Str::kebab($diskModule->getName());
        $record = Module::where('key', $key)->first();

        if (! $record) {
            $record = Module::create([
                'key' => $key,
                'name' => $args['name'] ?? $diskModule->getName(),
                'description' => $args['description'] ?? $diskModule->get('description', ''),
                'icon' => $args['icon'] ?? 'sparkles',
                'category' => $args['category'] ?? 'Produktivität & Prozesse',
                'route_prefix' => $args['route_prefix'] ?? $diskModule->get('alias', $key),
                'in_subscription_pool' => isset($args['in_subscription_pool']) ? (bool) $args['in_subscription_pool'] : true,
                'is_deprecated' => false,
                'is_active' => true,
            ]);
        } else {
            $record->update(['is_active' => true]);
        }

        $runMigrations = $args['run_migrations'] ?? true;
        $migrationOutput = null;
        if ($runMigrations) {
            try {
                Artisan::call('module:migrate', ['module' => $diskModule->getName(), '--force' => true]);
                $migrationOutput = trim(Artisan::output());
            } catch (\Throwable $e) {
                Log::warning("Module migration failed for {$diskModule->getName()}: " . $e->getMessage());
                $migrationOutput = 'Error: ' . $e->getMessage();
            }
        }

        $runSeeders = $args['run_seeders'] ?? false;
        if ($runSeeders) {
            try {
                Artisan::call('module:seed', ['module' => $diskModule->getName(), '--force' => true]);
            } catch (\Throwable $e) {
                // Optional
            }
        }

        $this->syncAllToolsPlan();

        return [
            'status' => 'installed',
            'module' => [
                'id' => $record->id,
                'key' => $record->key,
                'name' => $record->getRawOriginal('name'),
                'category' => $record->category,
                'is_active' => (bool) $record->is_active,
                'in_subscription_pool' => (bool) $record->in_subscription_pool,
            ],
            'migration_output' => $migrationOutput,
            'message' => "Module '{$diskModule->getName()}' installed and activated successfully.",
        ];
    }

    /**
     * (Admin Only) Update full tool/module metadata, roles, pool, and status.
     */
    protected function toolAdminUpdateModule(array $args): array
    {
        $this->ensureAdmin();

        $keyOrId = $args['module_key'] ?? ($args['id'] ?? null);
        if (! $keyOrId) {
            throw new \InvalidArgumentException('module_key is required.');
        }

        $module = is_numeric($keyOrId) ? Module::find($keyOrId) : Module::byKey($keyOrId);
        if (! $module) {
            throw new \InvalidArgumentException("Module '{$keyOrId}' not found.");
        }

        $fields = [
            'name', 'description', 'category', 'icon', 'route_prefix',
            'badge_text', 'sort_order', 'allowed_roles',
            'is_active', 'in_subscription_pool', 'is_deprecated',
        ];

        $data = [];
        foreach ($fields as $f) {
            if (array_key_exists($f, $args)) {
                $val = $args[$f];
                if (in_array($f, ['is_active', 'in_subscription_pool', 'is_deprecated'], true)) {
                    $val = (bool) $val;
                } elseif ($f === 'sort_order') {
                    $val = (int) $val;
                } elseif ($f === 'allowed_roles') {
                    if (is_string($val)) {
                        $val = array_filter(array_map('trim', explode(',', $val)));
                    }
                }
                $data[$f] = $val;
            }
        }

        if (! empty($data)) {
            $module->update($data);
            $this->syncAllToolsPlan();
        }

        return [
            'status' => 'success',
            'module' => [
                'id' => $module->id,
                'key' => $module->key,
                'name' => $module->getRawOriginal('name'),
                'description' => $module->getRawOriginal('description'),
                'category' => $module->category,
                'icon' => $module->icon,
                'route_prefix' => $module->route_prefix,
                'in_subscription_pool' => (bool) $module->in_subscription_pool,
                'is_active' => (bool) $module->is_active,
                'is_deprecated' => (bool) $module->is_deprecated,
                'badge_text' => $module->badge_text,
                'sort_order' => $module->sort_order,
                'allowed_roles' => $module->allowed_roles ?? [],
            ],
            'updated_fields' => array_keys($data),
            'message' => "Module '{$module->key}' was successfully updated.",
        ];
    }

    /**
     * (Admin Only) Toggle active status of a module.
     */
    protected function toolAdminToggleModule(array $args): array
    {
        $this->ensureAdmin();

        $module = Module::byKey($args['module_key'] ?? '');
        if (! $module) {
            throw new \InvalidArgumentException("Module '{$args['module_key']}' not found.");
        }

        $newState = array_key_exists('is_active', $args) ? (bool) $args['is_active'] : ! $module->is_active;
        $module->update(['is_active' => $newState]);
        $this->syncAllToolsPlan();

        return [
            'status' => 'success',
            'module_key' => $module->key,
            'is_active' => $module->is_active,
            'message' => $module->is_active ? "Module '{$module->key}' activated." : "Module '{$module->key}' deactivated.",
        ];
    }

    /**
     * (Admin Only) Toggle or set subscription pool status.
     */
    protected function toolAdminToggleModulePool(array $args): array
    {
        $this->ensureAdmin();

        $module = Module::byKey($args['module_key'] ?? '');
        if (! $module) {
            throw new \InvalidArgumentException("Module '{$args['module_key']}' not found.");
        }

        $newPool = array_key_exists('in_pool', $args)
            ? (bool) $args['in_pool']
            : (array_key_exists('in_subscription_pool', $args) ? (bool) $args['in_subscription_pool'] : ! $module->in_subscription_pool);

        $module->update(['in_subscription_pool' => $newPool]);
        $this->syncAllToolsPlan();

        return [
            'status' => 'success',
            'module_key' => $module->key,
            'in_subscription_pool' => (bool) $module->in_subscription_pool,
            'message' => $module->in_subscription_pool
                ? "Module '{$module->key}' added to the customer subscription pool."
                : "Module '{$module->key}' removed from the customer subscription pool.",
        ];
    }

    /**
     * (Admin Only) Deprecate or un-deprecate a module.
     */
    protected function toolAdminDeprecateModule(array $args): array
    {
        $this->ensureAdmin();

        $module = Module::byKey($args['module_key'] ?? '');
        if (! $module) {
            throw new \InvalidArgumentException("Module '{$args['module_key']}' not found.");
        }

        $isDeprecated = array_key_exists('is_deprecated', $args) ? (bool) $args['is_deprecated'] : true;
        $module->update(['is_deprecated' => $isDeprecated]);
        $this->syncAllToolsPlan();

        return [
            'status' => 'success',
            'module_key' => $module->key,
            'is_deprecated' => (bool) $module->is_deprecated,
            'message' => $module->is_deprecated
                ? "Module '{$module->key}' marked as deprecated/archived."
                : "Module '{$module->key}' restored from archive.",
        ];
    }

    /**
     * (Admin Only) Delete/uninstall a module registration record.
     */
    protected function toolAdminDeleteModule(array $args): array
    {
        $this->ensureAdmin();

        $keyOrId = $args['module_key'] ?? ($args['id'] ?? null);
        if (! $keyOrId) {
            throw new \InvalidArgumentException('module_key is required.');
        }

        $module = is_numeric($keyOrId) ? Module::find($keyOrId) : Module::byKey($keyOrId);
        if (! $module) {
            throw new \InvalidArgumentException("Module '{$keyOrId}' not found.");
        }

        $key = $module->key;
        $name = $module->getRawOriginal('name');

        // Detach from plans
        $module->plans()->detach();
        $module->delete();

        $this->syncAllToolsPlan();

        return [
            'status' => 'deleted',
            'module_key' => $key,
            'name' => $name,
            'message' => "Module '{$name}' ({$key}) registration was successfully deleted from database.",
        ];
    }

    /**
     * (Admin Only) Synchronize active in-pool modules with the All Tools Bundle plan.
     */
    protected function toolAdminSyncModulePlans(): array
    {
        $this->ensureAdmin();

        return $this->syncAllToolsPlan();
    }

    protected function syncAllToolsPlan(): array
    {
        $plan = Plan::where('slug', 'all-tools')->orWhere('slug', 'bundle')->first();
        if (! $plan) {
            return ['status' => 'skipped', 'message' => 'All Tools Bundle plan not found.'];
        }

        $poolModuleIds = Module::where('in_subscription_pool', true)
            ->where('is_active', true)
            ->where('is_deprecated', false)
            ->pluck('id')
            ->all();

        $plan->modules()->sync($poolModuleIds);

        return [
            'status' => 'synced',
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'synced_modules_count' => count($poolModuleIds),
        ];
    }

    protected function toolManageToolPool(array $args): array
    {
        $this->ensureAdmin();

        return $this->toolAdminToggleModulePool($args);
    }

    protected function toolDeprecateModule(array $args): array
    {
        $this->ensureAdmin();

        return $this->toolAdminDeprecateModule($args);
    }

    protected function toolUpdateModuleMetadata(array $args): array
    {
        $this->ensureAdmin();

        return $this->toolAdminUpdateModule($args);
    }

    protected function toolSyncSubscriptionPlans(): array
    {
        $this->ensureAdmin();

        return $this->toolAdminSyncModulePlans();
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
        $q = GlossaryTerm::query();
        if (! isset($args['include_unpublished']) || ! $args['include_unpublished']) {
            $q->where('is_published', true);
        }

        if (! empty($args['query'])) {
            $s = $args['query'];
            $q->where(fn ($sub) => $sub->where('term', 'like', "%{$s}%")->orWhere('definition', 'like', "%{$s}%"));
        }

        $category = $args['category'] ?? $args['pillar'] ?? null;
        if (! empty($category)) {
            $q->where('category', 'like', "%{$category}%");
        }

        $terms = $q->limit($args['limit'] ?? 30)->get();

        return ['terms' => $terms->map(fn ($t) => [
            'id' => $t->id,
            'slug' => $t->slug,
            'term' => $t->term,
            'category' => $t->category,
            'pillar' => $t->category,
            'is_published' => (bool) $t->is_published,
            'simple_definition' => $t->simple_definition,
        ])];
    }

    protected function toolCreateOrUpdateGlossaryTerm(array $args): array
    {
        $category = $args['category'] ?? $args['pillar'] ?? 'Revenue';
        $isPublished = isset($args['is_published']) ? (bool) $args['is_published'] : true;

        $t = GlossaryTerm::updateOrCreate(
            ['slug' => $args['slug']],
            [
                'term' => $args['term'],
                'definition' => $args['definition'],
                'simple_definition' => $args['simple_definition'] ?? null,
                'category' => $category,
                'is_published' => $isPublished,
            ]
        );

        return [
            'status' => 'success',
            'id' => $t->id,
            'slug' => $t->slug,
            'term' => $t->term,
            'category' => $t->category,
            'pillar' => $t->category,
            'is_published' => (bool) $t->is_published,
            'url' => url('/glossary/'.$t->slug),
        ];
    }

    protected function toolDeleteGlossaryTerm(array $args): array
    {
        $term = null;
        if (! empty($args['term_id'])) {
            $term = GlossaryTerm::find($args['term_id']);
        } elseif (! empty($args['slug'])) {
            $term = GlossaryTerm::where('slug', $args['slug'])->first();
        }

        if (! $term) {
            throw new \InvalidArgumentException('Glossary term not found with provided term_id or slug.');
        }

        $deletedInfo = [
            'id' => $term->id,
            'term' => $term->term,
            'slug' => $term->slug,
            'category' => $term->category,
        ];

        $term->delete();

        return [
            'status' => 'deleted',
            'deleted_term' => $deletedInfo,
        ];
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

    protected function toolUploadPostImage(array $args): array
    {
        if (empty($args['filename']) || empty($args['content_base64'])) {
            throw new \InvalidArgumentException('Both filename and content_base64 parameters are required.');
        }

        $name = Str::slug(pathinfo($args['filename'], PATHINFO_FILENAME));
        $ext = strtolower(pathinfo($args['filename'], PATHINFO_EXTENSION));

        if (! in_array($ext, ['svg', 'png', 'jpg', 'jpeg', 'webp'], true)) {
            throw new \InvalidArgumentException("Invalid file extension '.{$ext}'. Allowed: svg, png, jpg, jpeg, webp.");
        }

        $data = base64_decode($args['content_base64'], true);
        if ($data === false) {
            throw new \InvalidArgumentException('Invalid base64 encoded image content.');
        }

        if (strlen($data) > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('File size exceeds the 5MB maximum limit.');
        }

        // SVG security inspection
        if ($ext === 'svg' && preg_match('/<script|on\w+\s*=|javascript:/i', $data)) {
            throw new \InvalidArgumentException('Unsafe SVG content detected. Embedded JavaScript and event handlers are prohibited.');
        }

        $dir = public_path('img/posts');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filename = ($name ?: 'post-graphic-'.time()).".{$ext}";
        $relativePath = "img/posts/{$filename}";
        $fullPath = public_path($relativePath);

        File::put($fullPath, $data);

        $postUpdated = false;
        if (! empty($args['post_id'])) {
            $post = Post::find($args['post_id']);
            if ($post) {
                $post->update(['featured_image' => '/'.$relativePath]);
                $postUpdated = true;
            }
        }

        return [
            'status' => 'uploaded',
            'filename' => $filename,
            'relative_path' => '/'.$relativePath,
            'url' => url($relativePath),
            'size_bytes' => strlen($data),
            'post_id_updated' => $postUpdated ? (int) $args['post_id'] : null,
        ];
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

    /**
     * Ensure the authenticated caller has admin privileges.
     */
    protected function ensureAdmin(): User
    {
        $user = Auth::user();

        if (! $user) {
            throw new \RuntimeException('Unauthorized: An authenticated admin API token is required to perform this operation.');
        }

        if (! $user->isAdmin()) {
            throw new \RuntimeException('Forbidden: Only administrator accounts have permission to perform this operation on Allocore Suite.');
        }

        return $user;
    }

    /**
     * (Admin Only) Create a new user account.
     */
    protected function toolAdminCreateUser(array $args): array
    {
        $this->ensureAdmin();

        $name = trim($args['name'] ?? '');
        $email = strtolower(trim($args['email'] ?? ''));

        if (empty($name) || empty($email)) {
            throw new \InvalidArgumentException('name and email are required to create a user.');
        }

        if (User::where('email', $email)->exists()) {
            throw new \RuntimeException("A user with email '{$email}' already exists.");
        }

        $plainPassword = ! empty($args['password']) ? $args['password'] : Str::random(14);
        $isActive = isset($args['is_active']) ? (bool) $args['is_active'] : true;
        $locale = in_array($args['locale'] ?? '', ['en', 'de']) ? $args['locale'] : 'de';

        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $plainPassword,
            'is_active' => $isActive,
            'locale' => $locale,
        ];

        if (! empty($args['current_team_id'])) {
            $userData['current_team_id'] = (int) $args['current_team_id'];
        }

        $user = User::create($userData);

        if (! empty($args['role'])) {
            try {
                $user->syncRoles([$args['role']]);
            } catch (\Throwable $e) {
                Log::warning('Role sync warning on MCP create user: '.$e->getMessage());
            }
        }

        if (! isset($args['email_verified']) || $args['email_verified']) {
            $user->markEmailAsVerified();
        }

        return [
            'status' => 'created',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'is_active' => (bool) $user->is_active,
                'email_verified' => $user->hasVerifiedEmail(),
                'locale' => $user->locale,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'generated_password' => empty($args['password']) ? $plainPassword : '(as specified)',
            'message' => "User '{$user->name}' was successfully created.",
        ];
    }

    /**
     * (Admin Only) Update an existing user.
     */
    protected function toolAdminUpdateUser(array $args): array
    {
        $this->ensureAdmin();

        $userId = (int) ($args['user_id'] ?? 0);
        $user = User::findOrFail($userId);

        $updates = [];

        if (! empty($args['name'])) {
            $updates['name'] = trim($args['name']);
        }

        if (! empty($args['email'])) {
            $newEmail = strtolower(trim($args['email']));
            if ($newEmail !== strtolower($user->email)) {
                if (User::where('email', $newEmail)->where('id', '!=', $user->id)->exists()) {
                    throw new \RuntimeException("Email '{$newEmail}' is already taken by another user.");
                }
                $updates['email'] = $newEmail;
            }
        }

        if (! empty($args['password'])) {
            $updates['password'] = $args['password'];
        }

        if (isset($args['is_active'])) {
            $updates['is_active'] = (bool) $args['is_active'];
        }

        if (isset($args['locale']) && in_array($args['locale'], ['en', 'de'])) {
            $updates['locale'] = $args['locale'];
        }

        if (isset($args['current_team_id'])) {
            $updates['current_team_id'] = $args['current_team_id'] ? (int) $args['current_team_id'] : null;
        }

        if (! empty($updates)) {
            $user->update($updates);
        }

        if (! empty($args['role'])) {
            try {
                $user->syncRoles([$args['role']]);
            } catch (\Throwable $e) {
                Log::warning('Role sync warning on MCP update user: '.$e->getMessage());
            }
        }

        if (isset($args['email_verified'])) {
            if ($args['email_verified'] && ! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            } elseif (! $args['email_verified'] && $user->hasVerifiedEmail()) {
                $user->email_verified_at = null;
                $user->save();
            }
        }

        return [
            'status' => 'updated',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'is_active' => (bool) $user->is_active,
                'email_verified' => $user->hasVerifiedEmail(),
                'locale' => $user->locale,
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
            'updated_fields' => array_keys($updates),
            'message' => "User '{$user->name}' was successfully updated.",
        ];
    }

    /**
     * (Admin Only) Delete a user account.
     */
    protected function toolAdminDeleteUser(array $args): array
    {
        $admin = $this->ensureAdmin();
        $userId = (int) ($args['user_id'] ?? 0);

        if ($userId <= 0) {
            throw new \InvalidArgumentException('user_id is required.');
        }

        if ($userId === $admin->id) {
            throw new \RuntimeException('Security Protection: You cannot delete your own admin account via MCP.');
        }

        $user = User::findOrFail($userId);
        $userName = $user->name;
        $userEmail = $user->email;

        $user->delete();

        return [
            'status' => 'deleted',
            'user_id' => $userId,
            'name' => $userName,
            'email' => $userEmail,
            'message' => "User '{$userName}' ({$userEmail}) was successfully deleted.",
        ];
    }

    /**
     * (Admin Only) Get full profile and subscription details for a user.
     */
    protected function toolAdminGetUserDetails(array $args): array
    {
        $this->ensureAdmin();

        $userId = (int) ($args['user_id'] ?? 0);
        $user = User::with(['roles', 'teams', 'currentTeam', 'toolSubscriptions.plan'])->findOrFail($userId);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'is_admin' => $user->isAdmin(),
                'is_active' => (bool) $user->is_active,
                'email_verified' => $user->hasVerifiedEmail(),
                'locale' => $user->locale,
                'created_at' => $user->created_at?->toIso8601String(),
                'current_team' => $user->currentTeam ? ['id' => $user->currentTeam->id, 'name' => $user->currentTeam->name] : null,
                'teams_count' => $user->teams->count(),
            ],
            'subscriptions' => $user->toolSubscriptions->map(fn ($s) => [
                'id' => $s->id,
                'plan_id' => $s->plan_id,
                'plan_name' => $s->plan?->name,
                'plan_slug' => $s->plan?->slug,
                'status' => $s->status,
                'billing_interval' => $s->billing_interval,
                'payment_method' => $s->payment_method,
                'starts_at' => $s->starts_at?->toIso8601String(),
                'ends_at' => $s->ends_at?->toIso8601String(),
                'is_active' => $s->isActive(),
                'admin_note' => $s->admin_note,
            ])->toArray(),
        ];
    }

    /**
     * (Admin Only) Assign or create a subscription plan for a user.
     */
    protected function toolAdminAssignSubscription(array $args): array
    {
        $this->ensureAdmin();

        $userId = (int) ($args['user_id'] ?? 0);
        $user = User::findOrFail($userId);

        $plan = null;
        if (! empty($args['plan_id'])) {
            $plan = Plan::find($args['plan_id']);
        } elseif (! empty($args['plan_slug'])) {
            $plan = Plan::where('slug', $args['plan_slug'])->first();
        }

        if (! $plan) {
            $plan = Plan::where('slug', 'all-tools')->first() ?? Plan::where('is_active', true)->first();
        }

        if (! $plan) {
            throw new \RuntimeException('No active plan found to assign.');
        }

        $interval = in_array($args['billing_interval'] ?? '', ['monthly', 'yearly']) ? $args['billing_interval'] : 'monthly';
        $paymentMethod = in_array($args['payment_method'] ?? '', ['manual', 'stripe', 'bank', 'free']) ? $args['payment_method'] : 'manual';
        $status = in_array($args['status'] ?? '', ['active', 'pending', 'cancelled']) ? $args['status'] : 'active';
        $adminNote = $args['admin_note'] ?? 'Assigned via MCP Admin';

        $startsAt = now();
        if (! empty($args['duration_days'])) {
            $endsAt = now()->addDays((int) $args['duration_days']);
        } else {
            $endsAt = $interval === 'yearly' ? now()->addYear() : now()->addMonth();
        }

        $price = $plan->priceFor($interval);

        $subscription = ToolSubscription::create([
            'billable_type' => User::class,
            'billable_id' => $user->id,
            'plan_id' => $plan->id,
            'billing_interval' => $interval,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'admin_note' => $adminNote,
            'subtotal' => $price,
            'total' => $price,
        ]);

        return [
            'status' => 'assigned',
            'subscription_id' => $subscription->id,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'plan' => ['id' => $plan->id, 'name' => $plan->name, 'slug' => $plan->slug],
            'billing_interval' => $interval,
            'subscription_status' => $status,
            'starts_at' => $startsAt->toIso8601String(),
            'ends_at' => $endsAt->toIso8601String(),
            'message' => "Plan '{$plan->name}' ({$interval}) successfully assigned to {$user->name}.",
        ];
    }

    /**
     * (Admin Only) Update an existing subscription.
     */
    protected function toolAdminUpdateSubscription(array $args): array
    {
        $this->ensureAdmin();

        $subId = (int) ($args['subscription_id'] ?? 0);
        $sub = ToolSubscription::with(['plan', 'billable'])->findOrFail($subId);

        if (! empty($args['status']) && in_array($args['status'], ['active', 'pending', 'cancelled'])) {
            $sub->status = $args['status'];
            if ($args['status'] === 'cancelled') {
                $sub->ends_at = now();
            }
        }

        if (! empty($args['plan_id'])) {
            $newPlan = Plan::findOrFail($args['plan_id']);
            $sub->plan_id = $newPlan->id;
        }

        if (! empty($args['billing_interval']) && in_array($args['billing_interval'], ['monthly', 'yearly'])) {
            $sub->billing_interval = $args['billing_interval'];
        }

        if (! empty($args['extend_days'])) {
            $base = ($sub->ends_at && $sub->ends_at->isFuture()) ? $sub->ends_at : now();
            $sub->ends_at = $base->addDays((int) $args['extend_days']);
            $sub->status = 'active';
        }

        if (isset($args['admin_note'])) {
            $sub->admin_note = $args['admin_note'];
        }

        $sub->save();

        return [
            'status' => 'updated',
            'subscription_id' => $sub->id,
            'subscription_status' => $sub->status,
            'plan_name' => $sub->plan?->name,
            'billing_interval' => $sub->billing_interval,
            'ends_at' => $sub->ends_at?->toIso8601String(),
            'admin_note' => $sub->admin_note,
            'message' => "Subscription #{$sub->id} was successfully updated.",
        ];
    }

    /**
     * (Admin Only) Cancel an active subscription.
     */
    protected function toolAdminCancelSubscription(array $args): array
    {
        $this->ensureAdmin();

        $subId = (int) ($args['subscription_id'] ?? 0);
        $sub = ToolSubscription::findOrFail($subId);

        $sub->status = 'cancelled';
        $sub->ends_at = now();

        if (! empty($args['admin_note'])) {
            $sub->admin_note = ($sub->admin_note ? $sub->admin_note."\n" : '').'Cancelled via MCP: '.$args['admin_note'];
        }

        $sub->save();

        return [
            'status' => 'cancelled',
            'subscription_id' => $sub->id,
            'ends_at' => $sub->ends_at?->toIso8601String(),
            'message' => "Subscription #{$sub->id} has been cancelled.",
        ];
    }

    /**
     * (Admin Only) List all subscription plans.
     */
    protected function toolAdminListPlans(): array
    {
        $this->ensureAdmin();

        $plans = Plan::withCount('modules')->orderBy('id')->get();

        return [
            'total' => $plans->count(),
            'plans' => $plans->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'description' => $p->description,
                'price_monthly' => $p->price_monthly,
                'price_yearly' => $p->price_yearly,
                'currency' => $p->currency,
                'modules_count' => $p->modules_count,
                'is_active' => (bool) $p->is_active,
            ])->toArray(),
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

        $terms = GlossaryTerm::published()->get(['id', 'term', 'slug', 'category']);
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
                        'url' => url('/glossary/'.$termObj->slug),
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

    protected function toolLogWorkTimeEntry(array $args): array
    {
        $userId = $args['user_id'] ?? Auth::id() ?? 1;
        $teamId = $args['team_id'] ?? (Auth::user()?->current_team_id ?? 1);
        $date = $args['date'] ?? date('Y-m-d');
        $startTime = $args['start_time'] ?? '09:00';
        $endTime = $args['end_time'] ?? '17:00';
        $breakMinutes = (int) ($args['break_minutes'] ?? 0);
        $notes = $args['notes'] ?? null;

        $start = \Carbon\Carbon::parse("$date $startTime");
        $end = \Carbon\Carbon::parse("$date $endTime");
        $durationMinutes = max(0, $end->diffInMinutes($start) - $breakMinutes);
        $durationHours = round($durationMinutes / 60, 2);

        $entry = TimeEntry::withoutGlobalScopes()->create([
            'team_id' => $teamId,
            'user_id' => $userId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'break_minutes' => $breakMinutes,
            'notes' => $notes,
        ]);

        return [
            'status' => 'time_logged',
            'entry_id' => $entry->id,
            'user_id' => $userId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'break_minutes' => $breakMinutes,
            'logged_hours' => $durationHours,
            'notes' => $notes,
            'tool_route' => '/app/timebutler',
        ];
    }

    protected function toolGetTeamWorkforceSummary(array $args): array
    {
        $teamId = $args['team_id'] ?? (Auth::user()?->current_team_id ?? 1);
        $query = TimeEntry::withoutGlobalScopes()->where('team_id', $teamId);

        if (! empty($args['start_date'])) {
            $query->where('date', '>=', $args['start_date']);
        }
        if (! empty($args['end_date'])) {
            $query->where('date', '<=', $args['end_date']);
        }

        $entries = $query->with('user')->latest('date')->limit(100)->get();

        $totalMinutes = 0;
        $userBreakdown = [];

        foreach ($entries as $entry) {
            $mins = $entry->durationMinutes() ?? 0;
            $totalMinutes += $mins;
            $userName = $entry->user?->name ?? "User #{$entry->user_id}";
            if (! isset($userBreakdown[$userName])) {
                $userBreakdown[$userName] = ['entries_count' => 0, 'total_hours' => 0];
            }
            $userBreakdown[$userName]['entries_count']++;
            $userBreakdown[$userName]['total_hours'] += round($mins / 60, 2);
        }

        return [
            'team_id' => $teamId,
            'total_entries' => $entries->count(),
            'total_hours' => round($totalMinutes / 60, 2),
            'user_breakdown' => $userBreakdown,
            'recent_entries' => $entries->take(15)->map(fn ($e) => [
                'id' => $e->id,
                'user' => $e->user?->name ?? "#{$e->user_id}",
                'date' => $e->date?->format('Y-m-d') ?? (string) $e->date,
                'hours' => round(($e->durationMinutes() ?? 0) / 60, 2),
                'notes' => $e->notes,
            ]),
        ];
    }

    protected function toolScoreCustomerSweetSpot(array $args): array
    {
        $teamId = $args['team_id'] ?? (Auth::user()?->current_team_id ?? 1);
        $userId = Auth::id() ?? 1;

        $name = $args['name'];
        $industry = $args['industry'] ?? 'B2B';
        $revenue = (float) $args['revenue'];
        $profitMarginEur = (float) $args['profit_margin_eur'];
        $effortHours = max(0.5, (float) $args['effort_hours']);
        $chemistryScore = (int) ($args['chemistry_score'] ?? 7);
        $growthScore = (int) ($args['growth_score'] ?? 7);
        $paymentWillingness = (int) ($args['payment_willingness'] ?? 8);

        $marginPercent = $revenue > 0 ? round(($profitMarginEur / $revenue) * 100, 2) : 0;
        $marginPerHour = round($profitMarginEur / $effortHours, 2);

        // Calculate Multi-Factor Sweet Spot Score (0-100)
        $profitScore = min(100, max(0, ($marginPerHour / 150) * 100));
        $chemScoreNorm = $chemistryScore * 10;
        $growthScoreNorm = $growthScore * 10;
        $paymentScoreNorm = $paymentWillingness * 10;
        $totalScore = round(($profitScore * 0.4) + ($chemScoreNorm * 0.2) + ($growthScoreNorm * 0.2) + ($paymentScoreNorm * 0.2), 1);
        $isTop = $totalScore >= 75.0;

        $customer = SweetSpotCustomer::withoutGlobalScopes()->updateOrCreate(
            ['team_id' => $teamId, 'name' => $name],
            [
                'user_id' => $userId,
                'industry' => $industry,
                'revenue' => $revenue,
                'profit_margin_eur' => $profitMarginEur,
                'margin_percent' => $marginPercent,
                'effort_hours' => $effortHours,
                'chemistry_score' => $chemistryScore,
                'growth_score' => $growthScore,
                'payment_willingness' => $paymentWillingness,
            ]
        );

        CustomerScore::withoutGlobalScopes()->updateOrCreate(
            ['customer_id' => $customer->id, 'team_id' => $teamId],
            [
                'margin_per_hour' => $marginPerHour,
                'profitability_score' => round($profitScore, 1),
                'chemistry_score' => $chemScoreNorm,
                'growth_score' => $growthScoreNorm,
                'payment_score' => $paymentScoreNorm,
                'total_score' => $totalScore,
                'top_flag' => $isTop,
                'calculated_at' => now(),
            ]
        );

        return [
            'status' => 'customer_scored',
            'customer_id' => $customer->id,
            'customer_name' => $name,
            'margin_per_hour' => $marginPerHour,
            'margin_percent' => $marginPercent,
            'sweet_spot_score' => $totalScore,
            'is_sweet_spot_client' => $isTop,
            'recommendation' => $isTop ? 'Klasse-A Sweet Spot Kunde: Priorisieren & Ausbauen.' : 'Klasse-B/C Kunde: Aufwand reduzieren oder Preise nachverhandeln.',
            'tool_route' => '/app/sweet-spot',
        ];
    }

    protected function toolListSweetSpotRankings(array $args): array
    {
        $teamId = $args['team_id'] ?? (Auth::user()?->current_team_id ?? 1);
        $query = SweetSpotCustomer::withoutGlobalScopes()->where('team_id', $teamId)->with('score');

        if (! empty($args['top_only'])) {
            $query->whereHas('score', fn ($q) => $q->where('top_flag', true));
        }

        $limit = min(100, max(1, (int) ($args['limit'] ?? 20)));
        $customers = $query->limit($limit)->get();

        $ranked = $customers->map(function ($c) {
            $score = $c->score;
            return [
                'id' => $c->id,
                'name' => $c->name,
                'industry' => $c->industry,
                'revenue' => (float) $c->revenue,
                'profit_margin_eur' => (float) $c->profit_margin_eur,
                'effort_hours' => (float) $c->effort_hours,
                'margin_per_hour' => (float) ($score?->margin_per_hour ?? 0),
                'sweet_spot_score' => (float) ($score?->total_score ?? 0),
                'is_sweet_spot' => (bool) ($score?->top_flag ?? false),
            ];
        })->sortByDesc('sweet_spot_score')->values()->all();

        return [
            'team_id' => $teamId,
            'total_clients' => count($ranked),
            'sweet_spot_count' => collect($ranked)->where('is_sweet_spot', true)->count(),
            'clients' => $ranked,
        ];
    }

    protected function toolCreateProjectMilestone(array $args): array
    {
        $projectId = (int) $args['project_id'];
        $teamId = $args['team_id'] ?? (Auth::user()?->current_team_id ?? 1);
        $userId = Auth::id() ?? 1;

        $project = PlanProject::withoutGlobalScopes()->find($projectId);
        if (! $project) {
            throw new \InvalidArgumentException("Project ID #{$projectId} not found.");
        }

        $goal = Goal::withoutGlobalScopes()->create([
            'team_id' => $teamId,
            'project_id' => $projectId,
            'user_id' => $userId,
            'title' => $args['title'],
            'description' => $args['description'] ?? null,
            'target_date' => $args['target_date'] ?? null,
            'progress' => (int) ($args['progress'] ?? 0),
            'status' => $args['status'] ?? 'in_progress',
        ]);

        return [
            'status' => 'milestone_created',
            'goal_id' => $goal->id,
            'project_id' => $projectId,
            'project_name' => $project->name,
            'title' => $goal->title,
            'target_date' => $goal->target_date?->format('Y-m-d'),
            'progress' => $goal->progress,
            'tool_route' => "/app/planhive/projects/{$projectId}",
        ];
    }

    protected function toolGetProjectPortfolioOverview(array $args): array
    {
        $teamId = $args['team_id'] ?? (Auth::user()?->current_team_id ?? 1);
        $query = PlanProject::withoutGlobalScopes()->where('team_id', $teamId)->with(['goals', 'tasks']);

        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        $limit = min(100, max(1, (int) ($args['limit'] ?? 20)));
        $projects = $query->latest()->limit($limit)->get();

        return [
            'team_id' => $teamId,
            'total_projects' => $projects->count(),
            'projects' => $projects->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'start_date' => $p->start_date?->format('Y-m-d'),
                'end_date' => $p->end_date?->format('Y-m-d'),
                'goals_count' => $p->goals->count(),
                'avg_goal_progress' => $p->goals->count() > 0 ? round($p->goals->avg('progress'), 1) : 0,
                'tasks_count' => $p->tasks->count(),
                'tasks_done' => $p->tasks->where('status', 'done')->count(),
            ]),
        ];
    }

    protected function toolTrackProductionOrderStatus(array $args): array
    {
        $query = DentalOrder::withoutGlobalScopes()->with(['steps', 'scanEvents.workstation', 'company', 'productType']);

        if (! empty($args['order_id'])) {
            $query->where('id', $args['order_id']);
        } elseif (! empty($args['tracking_code'])) {
            $query->where('tracking_code', $args['tracking_code']);
        } elseif (! empty($args['qr_code'])) {
            $query->where('qr_code', $args['qr_code']);
        } else {
            $orders = $query->latest()->limit(15)->get();
            return [
                'total_orders' => $orders->count(),
                'orders' => $orders->map(fn ($o) => [
                    'id' => $o->id,
                    'patient_ref' => $o->patient_ref,
                    'doctor_name' => $o->doctor_name,
                    'tracking_code' => $o->tracking_code,
                    'status' => $o->status?->value ?? (string) $o->status,
                    'priority' => $o->priority?->value ?? (string) $o->priority,
                    'progress_percent' => $o->progressPercentage(),
                    'due_date' => $o->due_date?->format('Y-m-d'),
                    'is_overdue' => $o->isOverdue(),
                ]),
            ];
        }

        $order = $query->first();
        if (! $order) {
            throw new \InvalidArgumentException('Dental production order not found with provided identifiers.');
        }

        return [
            'id' => $order->id,
            'patient_ref' => $order->patient_ref,
            'doctor_name' => $order->doctor_name,
            'tracking_code' => $order->tracking_code,
            'qr_code' => $order->qr_code,
            'status' => $order->status?->value ?? (string) $order->status,
            'priority' => $order->priority?->value ?? (string) $order->priority,
            'due_date' => $order->due_date?->format('Y-m-d'),
            'is_overdue' => $order->isOverdue(),
            'progress_percent' => $order->progressPercentage(),
            'total_steps' => $order->totalStepsCount(),
            'completed_steps' => $order->completedStepsCount(),
            'steps' => $order->steps->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'status' => $s->status?->value ?? (string) $s->status,
                'sort_order' => $s->sort_order,
            ]),
            'latest_scans' => $order->scanEvents->take(5)->map(fn ($se) => [
                'scanned_at' => $se->scanned_at?->toIso8601String(),
                'event_type' => $se->event_type?->value ?? (string) $se->event_type,
                'workstation' => $se->workstation?->name ?? 'Mobile Terminal',
                'duration' => $se->formattedDuration(),
            ]),
        ];
    }

    protected function toolLogWorkstationScanEvent(array $args): array
    {
        $orderId = (int) $args['order_id'];
        $order = DentalOrder::withoutGlobalScopes()->find($orderId);
        if (! $order) {
            throw new \InvalidArgumentException("Dental order ID #{$orderId} not found.");
        }

        $teamId = $args['team_id'] ?? $order->team_id ?? (Auth::user()?->current_team_id ?? 1);
        $userId = Auth::id() ?? 1;
        $workstationId = $args['workstation_id'] ?? null;
        $orderStepId = $args['order_step_id'] ?? null;
        $eventTypeStr = $args['event_type'] ?? 'start';
        $notes = $args['notes'] ?? null;
        $durationSeconds = isset($args['duration_seconds']) ? (int) $args['duration_seconds'] : null;

        $eventType = match ($eventTypeStr) {
            'complete' => \Modules\DentalTrack\Enums\ScanEventType::Complete,
            'pause' => \Modules\DentalTrack\Enums\ScanEventType::Pause,
            'transfer_to_waiting' => \Modules\DentalTrack\Enums\ScanEventType::TransferToWaiting,
            default => \Modules\DentalTrack\Enums\ScanEventType::Start,
        };

        $scan = ScanEvent::withoutGlobalScopes()->create([
            'team_id' => $teamId,
            'dentaltrack_order_id' => $orderId,
            'dentaltrack_order_step_id' => $orderStepId,
            'dentaltrack_workstation_id' => $workstationId,
            'user_id' => $userId,
            'event_type' => $eventType,
            'scanned_at' => now(),
            'duration_seconds' => $durationSeconds,
            'notes' => $notes,
        ]);

        return [
            'status' => 'scan_event_recorded',
            'scan_event_id' => $scan->id,
            'order_id' => $orderId,
            'tracking_code' => $order->tracking_code,
            'event_type' => $eventTypeStr,
            'scanned_at' => $scan->scanned_at->toIso8601String(),
            'tool_route' => "/dentaltrack/admin/orders/{$orderId}",
        ];
    }

    protected function toolListSnowballDebts(array $args): array
    {
        $teamId = $args['team_id'] ?? (Auth::user()?->current_team_id ?? 1);
        $status = $args['status'] ?? 'active';
        $limit = min(200, max(1, (int) ($args['limit'] ?? 50)));

        $query = SnowballDebt::withoutGlobalScopes()->where('team_id', $teamId);
        if ($status === 'active') {
            $query->where('is_paid', false);
        } elseif ($status === 'paid') {
            $query->where('is_paid', true);
        }

        $debts = $query->orderBy('current_balance', 'asc')->limit($limit)->get();

        return [
            'team_id' => $teamId,
            'total_debts' => $debts->count(),
            'debts' => $debts->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'creditor' => $d->creditor,
                'original_balance' => (float) $d->original_balance,
                'current_balance' => (float) $d->current_balance,
                'interest_rate' => (float) $d->interest_rate,
                'minimum_payment' => (float) $d->minimum_payment,
                'monthly_interest' => (float) $d->monthlyInterestAmount(),
                'progress_percent' => (float) $d->progressPercent(),
                'total_paid' => (float) $d->totalPaidAmount(),
                'due_day' => $d->due_day,
                'category' => $d->category,
                'is_paid' => (bool) $d->is_paid,
                'paid_at' => $d->paid_at?->format('Y-m-d'),
                'notes' => $d->notes,
            ]),
        ];
    }

    protected function toolCreateOrUpdateSnowballDebt(array $args): array
    {
        $teamId = $args['team_id'] ?? (Auth::user()?->current_team_id ?? 1);

        $id = $args['id'] ?? null;
        $debt = $id ? SnowballDebt::withoutGlobalScopes()->where('team_id', $teamId)->find($id) : new SnowballDebt();

        if ($id && ! $debt) {
            throw new \InvalidArgumentException("Debt ID #{$id} not found for current team.");
        }

        $debt->team_id = $teamId;
        if (! empty($args['name'])) $debt->name = $args['name'];
        if (isset($args['creditor'])) $debt->creditor = $args['creditor'];
        if (isset($args['original_balance'])) $debt->original_balance = (float) $args['original_balance'];
        if (isset($args['current_balance'])) {
            $debt->current_balance = (float) $args['current_balance'];
            if (! $debt->original_balance) {
                $debt->original_balance = $debt->current_balance;
            }
        }
        if (isset($args['interest_rate'])) $debt->interest_rate = (float) $args['interest_rate'];
        if (isset($args['minimum_payment'])) $debt->minimum_payment = (float) $args['minimum_payment'];
        if (isset($args['due_day'])) $debt->due_day = (int) $args['due_day'];
        if (isset($args['category'])) $debt->category = $args['category'];
        if (isset($args['notes'])) $debt->notes = $args['notes'];

        $debt->save();

        return [
            'status' => $id ? 'debt_updated' : 'debt_created',
            'debt' => [
                'id' => $debt->id,
                'name' => $debt->name,
                'creditor' => $debt->creditor,
                'current_balance' => (float) $debt->current_balance,
                'interest_rate' => (float) $debt->interest_rate,
                'minimum_payment' => (float) $debt->minimum_payment,
                'is_paid' => (bool) $debt->is_paid,
            ],
            'module_route' => '/app/snowball/debts',
        ];
    }

    protected function toolLogSnowballPayment(array $args): array
    {
        $debtId = (int) $args['debt_id'];
        $debt = SnowballDebt::withoutGlobalScopes()->find($debtId);
        if (! $debt) {
            throw new \InvalidArgumentException("Debt ID #{$debtId} not found.");
        }

        $amount = (float) $args['amount'];
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Payment amount must be greater than 0.");
        }

        $payment = SnowballPayment::withoutGlobalScopes()->create([
            'team_id' => $debt->team_id,
            'debt_id' => $debtId,
            'amount' => $amount,
            'payment_date' => $args['payment_date'] ?? now()->toDateString(),
            'note' => $args['note'] ?? 'Logged via MCP Tool',
        ]);

        $debt->refresh();

        return [
            'status' => 'payment_recorded',
            'payment_id' => $payment->id,
            'debt_id' => $debtId,
            'debt_name' => $debt->name,
            'payment_amount' => $amount,
            'remaining_balance' => (float) $debt->current_balance,
            'is_paid_off' => (bool) $debt->is_paid,
            'module_route' => '/app/snowball/payments',
        ];
    }

    protected function toolCalculateSnowballPayoffPlan(array $args): array
    {
        $teamId = $args['team_id'] ?? (Auth::user()?->current_team_id ?? 1);
        $strategy = $args['strategy'] ?? 'snowball';
        $extraBudget = isset($args['extra_monthly_budget']) ? (float) $args['extra_monthly_budget'] : null;

        $debts = SnowballDebt::withoutGlobalScopes()
            ->where('team_id', $teamId)
            ->where('is_paid', false)
            ->get();

        if ($debts->isEmpty()) {
            return [
                'status' => 'no_debts',
                'message' => 'No active debts found. Company is completely debt-free!',
                'total_balance' => 0,
            ];
        }

        $setting = SnowballDebtSetting::withoutGlobalScopes()->where('team_id', $teamId)->first();
        if ($extraBudget === null) {
            $extraBudget = $setting ? (float) $setting->monthly_extra_budget : 0.0;
        }

        $calc = new SnowballCalculatorService();
        $comparison = $calc->compareStrategies($debts, $extraBudget);
        $plan = $calc->calculatePlan($debts, $strategy, $extraBudget);

        return [
            'team_id' => $teamId,
            'chosen_strategy' => $strategy,
            'extra_monthly_budget' => $extraBudget,
            'total_starting_balance' => (float) $debts->sum('current_balance'),
            'total_monthly_minimum' => (float) $debts->sum('minimum_payment'),
            'summary' => [
                'debt_free_date' => $plan['debt_free_date']?->format('F Y'),
                'total_months' => $plan['total_months'],
                'total_interest_paid' => $plan['total_interest'],
                'total_amount_paid' => $plan['total_paid'],
            ],
            'strategy_comparison' => [
                'snowball' => [
                    'months' => $comparison['snowball']['total_months'],
                    'debt_free_date' => $comparison['snowball']['debt_free_date']?->format('F Y'),
                    'interest_paid' => $comparison['snowball']['total_interest'],
                ],
                'avalanche' => [
                    'months' => $comparison['avalanche']['total_months'],
                    'debt_free_date' => $comparison['avalanche']['debt_free_date']?->format('F Y'),
                    'interest_paid' => $comparison['avalanche']['total_interest'],
                ],
                'interest_saved_with_avalanche' => $comparison['interest_saved_with_avalanche'],
                'months_saved_with_avalanche' => $comparison['months_saved_with_avalanche'],
            ],
            'payoff_order' => collect($plan['schedule'])->pluck('debt_name')->unique()->values()->all(),
            'module_route' => '/app/snowball/plan',
        ];
    }

    protected function toolGetSnowballFinancialSummary(array $args): array
    {
        $teamId = $args['team_id'] ?? (Auth::user()?->current_team_id ?? 1);

        $debts = SnowballDebt::withoutGlobalScopes()->where('team_id', $teamId)->get();
        $activeDebts = $debts->where('is_paid', false);
        $paidDebts = $debts->where('is_paid', true);

        $totalOriginal = (float) $debts->sum('original_balance');
        $totalCurrent = (float) $activeDebts->sum('current_balance');
        $totalMinPayment = (float) $activeDebts->sum('minimum_payment');
        $totalPaid = (float) $debts->sum(fn ($d) => $d->totalPaidAmount());
        $monthlyInterest = (float) $activeDebts->sum(fn ($d) => $d->monthlyInterestAmount());

        $cashflows = SnowballCashflow::withoutGlobalScopes()->where('team_id', $teamId)->get();
        $monthlyIncome = (float) $cashflows->where('type', 'income')->sum(fn ($c) => $c->monthlyNormalizedAmount());
        $monthlyExpenses = (float) $cashflows->where('type', 'expense')->sum(fn ($c) => $c->monthlyNormalizedAmount());
        $cashflowSurplus = max(0, $monthlyIncome - $monthlyExpenses - $totalMinPayment);

        $setting = SnowballDebtSetting::withoutGlobalScopes()->where('team_id', $teamId)->first();
        $extraBudget = $setting ? (float) $setting->monthly_extra_budget : 0.0;
        $activeStrategy = $setting ? $setting->strategy : 'snowball';

        $calc = new SnowballCalculatorService();
        $plan = $activeDebts->isNotEmpty() ? $calc->calculatePlan($activeDebts, $activeStrategy, $extraBudget) : null;

        return [
            'team_id' => $teamId,
            'kpis' => [
                'total_current_debt_eur' => $totalCurrent,
                'total_original_debt_eur' => $totalOriginal,
                'total_debt_paid_eur' => $totalPaid,
                'progress_percent' => $totalOriginal > 0 ? round((($totalOriginal - $totalCurrent) / $totalOriginal) * 100, 1) : 100.0,
                'monthly_debt_service_eur' => $totalMinPayment,
                'estimated_monthly_interest_eur' => round($monthlyInterest, 2),
                'active_debts_count' => $activeDebts->count(),
                'paid_debts_count' => $paidDebts->count(),
            ],
            'payoff_outlook' => [
                'strategy' => $activeStrategy,
                'extra_monthly_budget' => $extraBudget,
                'estimated_debt_free_date' => $plan ? $plan['debt_free_date']?->format('F Y') : 'Immediately (Debt Free)',
                'months_to_debt_free' => $plan ? $plan['total_months'] : 0,
                'projected_interest_to_pay' => $plan ? $plan['total_interest'] : 0.0,
            ],
            'cashflow_surplus_analysis' => [
                'monthly_income_eur' => $monthlyIncome,
                'monthly_expenses_eur' => $monthlyExpenses,
                'unallocated_surplus_eur' => round($cashflowSurplus, 2),
            ],
            'module_route' => '/app/snowball',
        ];
    }

    /**
     * Resolve team ID from arguments or authenticated user context.
     */
    protected function resolveTeamId(array $args): int
    {
        if (! empty($args['team_id'])) {
            return (int) $args['team_id'];
        }

        $user = Auth::user();
        if ($user && $user->current_team_id) {
            return (int) $user->current_team_id;
        }

        return (int) (Team::first()?->id ?? 1);
    }

    // --- Admin Coupons & Discounts ---

    /**
     * (Admin Only) List all promotional and discount coupons.
     */
    protected function toolAdminListCoupons(array $args): array
    {
        $this->ensureAdmin();

        $query = Coupon::query();
        if (isset($args['only_active']) && $args['only_active']) {
            $query->where('is_active', true);
        }

        $coupons = $query->orderBy('created_at', 'desc')->get();

        return [
            'total' => $coupons->count(),
            'coupons' => $coupons->map(fn ($c) => [
                'id' => $c->id,
                'code' => $c->code,
                'type' => $c->type,
                'value' => (float) $c->value,
                'max_uses' => $c->max_uses,
                'used_count' => $c->used_count,
                'is_active' => (bool) $c->is_active,
                'is_valid_now' => $c->isValid(),
                'starts_at' => $c->starts_at?->toIso8601String(),
                'expires_at' => $c->expires_at?->toIso8601String(),
                'description' => $c->description,
                'created_at' => $c->created_at?->toIso8601String(),
            ]),
        ];
    }

    /**
     * (Admin Only) Create a promotional discount coupon.
     */
    protected function toolAdminCreateCoupon(array $args): array
    {
        $this->ensureAdmin();

        $code = strtoupper(trim($args['code'] ?? ''));
        if (empty($code)) {
            throw new \InvalidArgumentException('code is required.');
        }

        if (Coupon::where('code', $code)->exists()) {
            throw new \InvalidArgumentException("Coupon with code '{$code}' already exists.");
        }

        $type = in_array($args['type'] ?? '', ['percent', 'fixed'], true) ? $args['type'] : 'percent';
        $value = (float) ($args['value'] ?? 10);
        $maxUses = isset($args['max_uses']) ? (int) $args['max_uses'] : null;
        $description = trim($args['description'] ?? '');
        $isActive = isset($args['is_active']) ? (bool) $args['is_active'] : true;

        $startsAt = ! empty($args['starts_at']) ? \Carbon\Carbon::parse($args['starts_at']) : now();
        $expiresAt = ! empty($args['expires_at']) ? \Carbon\Carbon::parse($args['expires_at']) : null;

        $coupon = Coupon::create([
            'code' => $code,
            'type' => $type,
            'value' => $value,
            'max_uses' => $maxUses,
            'used_count' => 0,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'is_active' => $isActive,
            'description' => $description,
        ]);

        return [
            'status' => 'created',
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'is_active' => (bool) $coupon->is_active,
                'expires_at' => $coupon->expires_at?->toIso8601String(),
            ],
            'message' => "Coupon '{$code}' created successfully.",
        ];
    }

    /**
     * (Admin Only) Delete or remove a discount coupon.
     */
    protected function toolAdminDeleteCoupon(array $args): array
    {
        $this->ensureAdmin();

        $codeOrId = $args['code'] ?? ($args['id'] ?? null);
        if (! $codeOrId) {
            throw new \InvalidArgumentException('code or id is required.');
        }

        $coupon = is_numeric($codeOrId) ? Coupon::find($codeOrId) : Coupon::where('code', strtoupper($codeOrId))->first();
        if (! $coupon) {
            throw new \InvalidArgumentException("Coupon '{$codeOrId}' not found.");
        }

        $code = $coupon->code;
        $coupon->delete();

        return [
            'status' => 'deleted',
            'code' => $code,
            'message' => "Coupon '{$code}' was successfully deleted.",
        ];
    }

    // --- Admin Maintenance, Backups & System Logs ---

    /**
     * (Admin Only) Create a database SQL dump backup.
     */
    protected function toolAdminCreateBackup(array $args): array
    {
        $this->ensureAdmin();

        $disk = $args['disk'] ?? 'local';
        if (! in_array($disk, ['local', 's3'], true)) {
            $disk = 'local';
        }

        $fileName = 'backup_' . now()->format('Ymd_His') . '.sql';
        $path = 'backups/' . $fileName;
        $tempPath = storage_path('app/private/' . $path);

        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>/dev/null || sqlite3 %s .dump > %s',
            escapeshellarg(config('database.connections.mysql.host', 'localhost')),
            escapeshellarg(config('database.connections.mysql.port', '3306')),
            escapeshellarg(config('database.connections.mysql.username', 'root')),
            escapeshellarg(config('database.connections.mysql.password', '')),
            escapeshellarg(config('database.connections.mysql.database', '')),
            escapeshellarg($tempPath),
            escapeshellarg(config('database.connections.sqlite.database', '')),
            escapeshellarg($tempPath)
        );

        exec($command);

        $contents = file_exists($tempPath) ? file_get_contents($tempPath) : '';
        $size = strlen($contents);

        if ($size > 0) {
            Storage::disk($disk)->put($path, $contents);
        }

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        $backup = Backup::create([
            'name' => $fileName,
            'path' => $path,
            'disk' => $disk,
            'type' => 'database',
            'size' => $size,
            'completed_at' => now(),
        ]);

        return [
            'status' => 'success',
            'backup' => [
                'id' => $backup->id,
                'name' => $backup->name,
                'disk' => $backup->disk,
                'size_bytes' => $backup->size,
                'size_human' => round($backup->size / 1024 / 1024, 2) . ' MB',
                'completed_at' => $backup->completed_at?->toIso8601String(),
            ],
            'message' => "Database backup '{$fileName}' created successfully.",
        ];
    }

    /**
     * (Admin Only) List available database and system backups.
     */
    protected function toolAdminListBackups(): array
    {
        $this->ensureAdmin();

        $backups = Backup::latest()->limit(25)->get();

        return [
            'total' => $backups->count(),
            'backups' => $backups->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'disk' => $b->disk,
                'type' => $b->type,
                'size' => $b->size,
                'size_formatted' => round($b->size / 1024 / 1024, 2) . ' MB',
                'completed_at' => $b->completed_at?->toIso8601String(),
                'created_at' => $b->created_at?->toIso8601String(),
            ]),
        ];
    }

    /**
     * (Admin Only) Read recent application error logs from storage/logs/laravel.log.
     */
    protected function toolAdminReadErrorLogs(array $args): array
    {
        $this->ensureAdmin();

        $linesCount = min(150, max(10, (int) ($args['lines'] ?? 50)));
        $logPath = storage_path('logs/laravel.log');

        if (! file_exists($logPath)) {
            return [
                'exists' => false,
                'message' => 'No laravel.log file found in storage/logs.',
                'lines' => [],
            ];
        }

        $file = file($logPath);
        $totalLines = count($file);
        $tail = array_slice($file, -$linesCount);

        return [
            'exists' => true,
            'total_file_lines' => $totalLines,
            'retrieved_lines' => count($tail),
            'log_content' => implode('', $tail),
        ];
    }

    /**
     * (Admin Only) List failed background queue worker jobs.
     */
    protected function toolAdminListFailedJobs(array $args): array
    {
        $this->ensureAdmin();

        $limit = min(50, max(1, (int) ($args['limit'] ?? 20)));

        try {
            $failed = DB::table('failed_jobs')->latest()->limit($limit)->get();
            return [
                'total_failed' => $failed->count(),
                'jobs' => $failed->map(fn ($j) => [
                    'id' => $j->id,
                    'uuid' => $j->uuid ?? null,
                    'connection' => $j->connection,
                    'queue' => $j->queue,
                    'failed_at' => $j->failed_at,
                    'exception_summary' => Str::limit($j->exception, 300),
                ]),
            ];
        } catch (\Throwable $e) {
            return [
                'total_failed' => 0,
                'jobs' => [],
                'message' => 'failed_jobs table not accessible or empty: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * (Admin Only) Retry a failed queue job.
     */
    protected function toolAdminRetryFailedJob(array $args): array
    {
        $this->ensureAdmin();

        $id = $args['id'] ?? 'all';
        Artisan::call('queue:retry', ['id' => [(string) $id]]);
        $output = trim(Artisan::output());

        return [
            'status' => 'executed',
            'job_id' => $id,
            'output' => $output,
        ];
    }

    /**
     * (Admin Only) Put the application into or out of maintenance mode.
     */
    protected function toolAdminToggleMaintenance(array $args): array
    {
        $this->ensureAdmin();

        $enable = (bool) ($args['enable'] ?? false);
        $secret = $args['secret'] ?? null;

        if ($enable) {
            $params = [];
            if ($secret) $params['--secret'] = $secret;
            Artisan::call('down', $params);
            $msg = 'Application is now in maintenance mode (offline).';
        } else {
            Artisan::call('up');
            $msg = 'Application is now live (maintenance mode disabled).';
        }

        return [
            'status' => 'success',
            'maintenance_mode' => $enable,
            'output' => trim(Artisan::output()),
            'message' => $msg,
        ];
    }

    // --- Admin Announcements & Platform Settings ---

    /**
     * (Admin Only) List site-wide dashboard announcements.
     */
    protected function toolAdminListAnnouncements(): array
    {
        $this->ensureAdmin();

        $announcements = Announcement::orderBy('created_at', 'desc')->get();

        return [
            'total' => $announcements->count(),
            'announcements' => $announcements->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'body' => $a->body,
                'type' => $a->type,
                'is_active' => (bool) $a->is_active,
                'starts_at' => $a->starts_at?->toIso8601String(),
                'ends_at' => $a->ends_at?->toIso8601String(),
                'created_at' => $a->created_at?->toIso8601String(),
            ]),
        ];
    }

    /**
     * (Admin Only) Create a dashboard banner announcement.
     */
    protected function toolAdminCreateAnnouncement(array $args): array
    {
        $this->ensureAdmin();

        $title = trim($args['title'] ?? '');
        $body = trim($args['body'] ?? '');

        if (empty($title) || empty($body)) {
            throw new \InvalidArgumentException('title and body are required.');
        }

        $type = in_array($args['type'] ?? '', ['info', 'warning', 'success', 'danger'], true) ? $args['type'] : 'info';
        $isActive = isset($args['is_active']) ? (bool) $args['is_active'] : true;
        $startsAt = ! empty($args['starts_at']) ? \Carbon\Carbon::parse($args['starts_at']) : now();
        $endsAt = ! empty($args['ends_at']) ? \Carbon\Carbon::parse($args['ends_at']) : null;

        $announcement = Announcement::create([
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'is_active' => $isActive,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return [
            'status' => 'created',
            'announcement' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'type' => $announcement->type,
                'is_active' => (bool) $announcement->is_active,
            ],
            'message' => "Announcement '{$title}' created successfully.",
        ];
    }

    /**
     * (Admin Only) Delete an announcement.
     */
    protected function toolAdminDeleteAnnouncement(array $args): array
    {
        $this->ensureAdmin();

        $id = (int) ($args['id'] ?? 0);
        $announcement = Announcement::findOrFail($id);
        $title = $announcement->title;
        $announcement->delete();

        return [
            'status' => 'deleted',
            'id' => $id,
            'message' => "Announcement '{$title}' deleted successfully.",
        ];
    }

    /**
     * (Admin Only) Get general platform and environment settings.
     */
    protected function toolAdminGetSettings(): array
    {
        $this->ensureAdmin();

        return [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_url' => config('app.url'),
            'app_locale' => config('app.locale'),
            'app_timezone' => config('app.timezone'),
            'mail_mailer' => config('mail.default'),
            'queue_connection' => config('queue.default'),
            'session_driver' => config('session.driver'),
            'database_default' => config('database.default'),
            'active_modules_count' => Module::active()->count(),
            'total_users' => User::count(),
            'total_teams' => Team::count(),
        ];
    }

    // --- Module Tools: ClusterForge ---

    /**
     * (ClusterForge) List and search SEO keyword and topic cluster projects.
     */
    protected function toolClusterforgeListProjects(array $args): array
    {
        $user = Auth::user();
        $query = ClusterForgeProject::withoutGlobalScope('current_team');

        if (! empty($args['team_id'])) {
            $query->where('team_id', (int) $args['team_id']);
        } elseif (! ($user && $user->isAdmin())) {
            $query->where('team_id', $this->resolveTeamId($args));
        }

        if (! empty($args['search'])) {
            $s = trim($args['search']);
            $query->where(function ($q) use ($s) {
                $q->where('topic', 'like', "%{$s}%")
                  ->orWhere('website', 'like', "%{$s}%")
                  ->orWhere('pillar_title', 'like', "%{$s}%");
            });
        }

        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        $limit = min(100, max(1, (int) ($args['limit'] ?? 25)));
        $total = (clone $query)->count();
        $projects = $query->withCount(['subtopics', 'questions'])->latest()->limit($limit)->get();

        $statsQuery = ClusterForgeProject::withoutGlobalScope('current_team');
        if (! empty($args['team_id'])) {
            $statsQuery->where('team_id', (int) $args['team_id']);
        } elseif (! ($user && $user->isAdmin())) {
            $statsQuery->where('team_id', $this->resolveTeamId($args));
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'completed' => (clone $statsQuery)->where('status', ClusterForgeProject::STATUS_COMPLETED)->count(),
            'processing' => (clone $statsQuery)->processing()->count(),
            'failed' => (clone $statsQuery)->where('status', ClusterForgeProject::STATUS_FAILED)->count(),
        ];

        return [
            'total' => $total,
            'stats' => $stats,
            'projects' => $projects->map(fn ($p) => [
                'id' => $p->id,
                'team_id' => $p->team_id,
                'topic' => $p->topic,
                'website' => $p->website,
                'language' => $p->language,
                'status' => $p->status,
                'status_label' => $p->statusLabel(),
                'progress_percent' => $p->progressPercent(),
                'subtopics_count' => $p->subtopics_count,
                'questions_count' => $p->questions_count,
                'pillar_title' => $p->pillar_title,
                'created_at' => $p->created_at?->toIso8601String(),
            ]),
        ];
    }

    /**
     * (ClusterForge) Get complete details of a specific project with subtopics and keywords.
     */
    protected function toolClusterforgeGetProject(array $args): array
    {
        $projectId = (int) ($args['project_id'] ?? 0);
        $user = Auth::user();
        $query = ClusterForgeProject::withoutGlobalScope('current_team')->with(['subtopics.questions']);

        if (! empty($args['team_id'])) {
            $query->where('team_id', (int) $args['team_id']);
        } elseif (! ($user && $user->isAdmin())) {
            $query->where('team_id', $this->resolveTeamId($args));
        }

        $project = $query->findOrFail($projectId);
        $includeContent = (bool) ($args['include_content'] ?? false);

        return [
            'project' => [
                'id' => $project->id,
                'team_id' => $project->team_id,
                'topic' => $project->topic,
                'website' => $project->website,
                'language' => $project->language,
                'status' => $project->status,
                'status_label' => $project->statusLabel(),
                'progress_percent' => $project->progressPercent(),
                'error' => $project->error,
                'pillar_title' => $project->pillar_title,
                'pillar_meta_description' => $project->pillar_meta_description,
                'pillar_content' => $includeContent ? $project->pillar_content : ($project->pillar_content ? Str::limit($project->pillar_content, 400) : null),
                'subtopics_count' => $project->subtopics->count(),
                'questions_count' => $project->subtopics->sum(fn ($s) => $s->questions->count()),
                'created_at' => $project->created_at?->toIso8601String(),
                'subtopics' => $project->subtopics->map(fn ($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'long_tail_keyword' => $s->long_tail_keyword,
                    'search_volume' => $s->search_volume,
                    'cpc' => $s->cpc,
                    'competition' => $s->competition,
                    'competition_index' => $s->competition_index,
                    'cluster_title' => $s->cluster_title,
                    'questions_count' => $s->questions->count(),
                ]),
            ],
        ];
    }

    /**
     * (ClusterForge) Search keywords and subtopics across ClusterForge projects.
     */
    protected function toolClusterforgeSearchKeywords(array $args): array
    {
        $user = Auth::user();
        $query = ClusterForgeSubtopic::whereHas('project', function ($q) use ($args, $user) {
            $q->withoutGlobalScope('current_team');
            if (! empty($args['team_id'])) {
                $q->where('team_id', (int) $args['team_id']);
            } elseif (! ($user && $user->isAdmin())) {
                $q->where('team_id', $this->resolveTeamId($args));
            }
        })->with(['project' => fn ($q) => $q->withoutGlobalScope('current_team')]);

        if (! empty($args['project_id'])) {
            $query->where('project_id', (int) $args['project_id']);
        }

        if (! empty($args['query'])) {
            $term = trim($args['query']);
            $query->where(function ($q) use ($term) {
                $q->where('long_tail_keyword', 'like', "%{$term}%")
                  ->orWhere('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('cluster_title', 'like', "%{$term}%");
            });
        }

        if (isset($args['min_volume']) && is_numeric($args['min_volume'])) {
            $query->where('search_volume', '>=', (int) $args['min_volume']);
        }

        if (isset($args['max_cpc']) && is_numeric($args['max_cpc'])) {
            $query->where('cpc', '<=', (float) $args['max_cpc']);
        }

        $limit = min(100, max(1, (int) ($args['limit'] ?? 30)));
        $subtopics = $query->orderByDesc('search_volume')->limit($limit)->get();

        return [
            'total' => $subtopics->count(),
            'keywords' => $subtopics->map(fn ($s) => [
                'subtopic_id' => $s->id,
                'project_id' => $s->project_id,
                'project_topic' => $s->project?->topic,
                'keyword' => $s->long_tail_keyword ?: $s->title,
                'title' => $s->title,
                'description' => $s->description,
                'search_volume' => $s->search_volume,
                'cpc' => $s->cpc,
                'competition' => $s->competition,
                'competition_index' => $s->competition_index,
                'cluster_title' => $s->cluster_title,
            ]),
        ];
    }

    /**
     * (ClusterForge) Retrieve full cluster details and Q&A content for a subtopic.
     */
    protected function toolClusterforgeGetSubtopic(array $args): array
    {
        $subtopicId = (int) ($args['subtopic_id'] ?? 0);
        $user = Auth::user();
        $query = ClusterForgeSubtopic::whereHas('project', function ($q) use ($args, $user) {
            $q->withoutGlobalScope('current_team');
            if (! empty($args['team_id'])) {
                $q->where('team_id', (int) $args['team_id']);
            } elseif (! ($user && $user->isAdmin())) {
                $q->where('team_id', $this->resolveTeamId($args));
            }
        })->with(['project' => fn ($q) => $q->withoutGlobalScope('current_team'), 'questions']);

        $subtopic = $query->findOrFail($subtopicId);

        return [
            'subtopic' => [
                'id' => $subtopic->id,
                'project_id' => $subtopic->project_id,
                'project_topic' => $subtopic->project?->topic,
                'title' => $subtopic->title,
                'description' => $subtopic->description,
                'long_tail_keyword' => $subtopic->long_tail_keyword,
                'search_volume' => $subtopic->search_volume,
                'cpc' => $subtopic->cpc,
                'competition' => $subtopic->competition,
                'competition_index' => $subtopic->competition_index,
                'low_bid' => $subtopic->low_bid,
                'high_bid' => $subtopic->high_bid,
                'cluster_title' => $subtopic->cluster_title,
                'cluster_meta_description' => $subtopic->cluster_meta_description,
                'cluster_content' => $subtopic->cluster_content,
                'questions' => $subtopic->questions->map(fn ($q) => [
                    'id' => $q->id,
                    'question' => $q->question,
                    'answer' => $q->answer,
                ]),
            ],
        ];
    }

    /**
     * (ClusterForge) Search SEO questions and answers across topic clusters.
     */
    protected function toolClusterforgeSearchQuestions(array $args): array
    {
        $user = Auth::user();
        $query = ClusterForgeQuestion::whereHas('subtopic.project', function ($q) use ($args, $user) {
            $q->withoutGlobalScope('current_team');
            if (! empty($args['team_id'])) {
                $q->where('team_id', (int) $args['team_id']);
            } elseif (! ($user && $user->isAdmin())) {
                $q->where('team_id', $this->resolveTeamId($args));
            }
        })->with(['subtopic.project' => fn ($q) => $q->withoutGlobalScope('current_team')]);

        if (! empty($args['project_id'])) {
            $query->whereHas('subtopic', fn ($q) => $q->where('project_id', (int) $args['project_id']));
        }

        if (! empty($args['query'])) {
            $term = trim($args['query']);
            $query->where(function ($q) use ($term) {
                $q->where('question', 'like', "%{$term}%")
                  ->orWhere('answer', 'like', "%{$term}%");
            });
        }

        $limit = min(100, max(1, (int) ($args['limit'] ?? 25)));
        $questions = $query->limit($limit)->get();

        return [
            'total' => $questions->count(),
            'questions' => $questions->map(fn ($q) => [
                'id' => $q->id,
                'subtopic_id' => $q->subtopic_id,
                'subtopic_title' => $q->subtopic?->title,
                'keyword' => $q->subtopic?->long_tail_keyword,
                'project_id' => $q->subtopic?->project_id,
                'project_topic' => $q->subtopic?->project?->topic,
                'question' => $q->question,
                'answer' => $q->answer,
            ]),
        ];
    }

    /**
     * (ClusterForge) Create an AI SEO keyword clustering project.
     */
    protected function toolClusterforgeCreateProject(array $args): array
    {
        $teamId = $this->resolveTeamId($args);
        $user = Auth::user();

        $topic = trim($args['topic'] ?? '');
        if (empty($topic)) {
            throw new \InvalidArgumentException('topic is required for ClusterForge project.');
        }

        $project = ClusterForgeProject::withoutGlobalScope('current_team')->create([
            'team_id' => $teamId,
            'user_id' => $user?->id ?? 1,
            'topic' => $topic,
            'website' => $args['website'] ?? null,
            'language' => in_array($args['language'] ?? 'de', ['de', 'en'], true) ? $args['language'] : 'de',
            'status' => ClusterForgeProject::STATUS_PENDING,
            'pillar_title' => $args['pillar_title'] ?? $topic,
        ]);

        $startGeneration = $args['start_generation'] ?? true;
        if ($startGeneration) {
            ClusterForgeGenerateProjectJob::dispatch($project->id);
        }

        return [
            'status' => 'created',
            'generation_queued' => (bool) $startGeneration,
            'project' => [
                'id' => $project->id,
                'topic' => $project->topic,
                'status' => $project->status,
                'language' => $project->language,
                'pillar_title' => $project->pillar_title,
            ],
            'message' => "ClusterForge project for '{$topic}' created successfully." . ($startGeneration ? ' AI topic generation queued.' : ''),
        ];
    }

    /**
     * (ClusterForge) Retry cluster generation for a failed or pending project.
     */
    protected function toolClusterforgeRetryProject(array $args): array
    {
        $user = Auth::user();
        $projectId = (int) ($args['project_id'] ?? 0);

        $query = ClusterForgeProject::withoutGlobalScope('current_team');
        if (! empty($args['team_id'])) {
            $query->where('team_id', (int) $args['team_id']);
        } elseif (! ($user && $user->isAdmin())) {
            $query->where('team_id', $this->resolveTeamId($args));
        }

        $project = $query->findOrFail($projectId);

        if ($project->isInProgress()) {
            return [
                'status' => 'already_running',
                'project_id' => $project->id,
                'current_status' => $project->status,
                'message' => 'This project is already being generated.',
            ];
        }

        $project->update([
            'status' => ClusterForgeProject::STATUS_PENDING,
            'error' => null,
        ]);

        ClusterForgeGenerateProjectJob::dispatch($project->id);

        return [
            'status' => 'queued',
            'project_id' => $project->id,
            'message' => "ClusterForge project #{$project->id} ('{$project->topic}') generation restarted.",
        ];
    }

    /**
     * (ClusterForge) Export markdown content for a pillar page or cluster subtopic page.
     */
    protected function toolClusterforgeExportContent(array $args): array
    {
        $user = Auth::user();
        $projectId = (int) ($args['project_id'] ?? 0);
        $type = $args['type'] ?? 'pillar';

        $query = ClusterForgeProject::withoutGlobalScope('current_team');
        if (! empty($args['team_id'])) {
            $query->where('team_id', (int) $args['team_id']);
        } elseif (! ($user && $user->isAdmin())) {
            $query->where('team_id', $this->resolveTeamId($args));
        }

        $project = $query->findOrFail($projectId);

        if ($type === 'cluster') {
            $subtopicId = (int) ($args['subtopic_id'] ?? 0);
            $sub = $project->subtopics()->findOrFail($subtopicId);

            $filename = sprintf('cluster-%s.md', Str::slug($sub->long_tail_keyword ?: $sub->title ?: 'page'));
            $body = sprintf(
                "<!--\nTitle: %s\nMeta Description: %s\nLong-tail keyword: %s\n-->\n\n%s\n",
                $sub->cluster_title ?? '',
                $sub->cluster_meta_description ?? '',
                $sub->long_tail_keyword ?? '',
                $sub->cluster_content ?? ''
            );

            return [
                'type' => 'cluster',
                'project_id' => $project->id,
                'subtopic_id' => $sub->id,
                'filename' => $filename,
                'title' => $sub->cluster_title,
                'meta_description' => $sub->cluster_meta_description,
                'long_tail_keyword' => $sub->long_tail_keyword,
                'content' => $body,
            ];
        }

        $filename = sprintf('pillar-%s.md', Str::slug($project->topic ?: 'page'));
        $body = sprintf(
            "<!--\nTitle: %s\nMeta Description: %s\n-->\n\n%s\n",
            $project->pillar_title ?? '',
            $project->pillar_meta_description ?? '',
            $project->pillar_content ?? ''
        );

        return [
            'type' => 'pillar',
            'project_id' => $project->id,
            'filename' => $filename,
            'title' => $project->pillar_title,
            'meta_description' => $project->pillar_meta_description,
            'content' => $body,
        ];
    }

    /**
     * (ClusterForge) Delete an SEO topic cluster project.
     */
    protected function toolClusterforgeDeleteProject(array $args): array
    {
        $user = Auth::user();
        $projectId = (int) ($args['project_id'] ?? 0);

        $query = ClusterForgeProject::withoutGlobalScope('current_team');
        if (! empty($args['team_id'])) {
            $query->where('team_id', (int) $args['team_id']);
        } elseif (! ($user && $user->isAdmin())) {
            $query->where('team_id', $this->resolveTeamId($args));
        }

        $project = $query->findOrFail($projectId);
        $topic = $project->topic;
        $project->delete();

        return [
            'status' => 'deleted',
            'project_id' => $projectId,
            'message' => "ClusterForge project '{$topic}' deleted successfully.",
        ];
    }

    /**
     * (ClusterForge) Save externally generated subtopics — Gemini bypass.
     */
    protected function toolClusterforgeSaveSubtopics(array $args): array
    {
        $user = Auth::user();
        $projectId = (int) ($args['project_id'] ?? 0);

        $query = ClusterForgeProject::withoutGlobalScope('current_team');
        if (! ($user && $user->isAdmin())) {
            $query->where('team_id', $this->resolveTeamId($args));
        }
        $project = $query->findOrFail($projectId);

        $subtopics = $args['subtopics'] ?? [];
        if (empty($subtopics) || ! is_array($subtopics)) {
            throw new \InvalidArgumentException('subtopics array is required and must not be empty.');
        }

        DB::transaction(function () use ($project, $subtopics) {
            $project->subtopics()->delete();
            foreach (array_slice($subtopics, 0, 5) as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $project->subtopics()->create([
                    'title'             => (string) ($row['title'] ?? ('Subtopic ' . ($i + 1))),
                    'long_tail_keyword' => isset($row['long_tail_keyword']) ? (string) $row['long_tail_keyword'] : null,
                    'description'       => isset($row['description']) ? (string) $row['description'] : null,
                    'sort_order'        => $i,
                ]);
            }
        });

        $project->update(['status' => ClusterForgeProject::STATUS_GENERATING_QUESTIONS, 'error' => null]);

        return [
            'status'           => 'saved',
            'project_id'       => $project->id,
            'subtopics_saved'  => $project->subtopics()->count(),
            'project_status'   => $project->fresh()->status,
            'subtopics'        => $project->subtopics()->get(['id', 'title', 'long_tail_keyword', 'sort_order']),
            'message'          => "Subtopics saved for project #{$project->id} ('{$project->topic}'). Next: call clusterforge_save_questions for each subtopic_id.",
        ];
    }

    /**
     * (ClusterForge) Save externally generated questions for a subtopic — Gemini bypass.
     */
    protected function toolClusterforgeSaveQuestions(array $args): array
    {
        $user = Auth::user();
        $subtopicId = (int) ($args['subtopic_id'] ?? 0);

        $subtopic = ClusterForgeSubtopic::whereHas('project', function ($q) use ($args, $user) {
            $q->withoutGlobalScope('current_team');
            if (! ($user && $user->isAdmin())) {
                $q->where('team_id', $this->resolveTeamId($args));
            }
        })->findOrFail($subtopicId);

        $questions = array_values(array_filter($args['questions'] ?? [], fn ($q) => is_string($q) && trim($q) !== ''));
        if (empty($questions)) {
            throw new \InvalidArgumentException('questions array must contain at least one non-empty string.');
        }

        DB::transaction(function () use ($subtopic, $questions) {
            $subtopic->questions()->delete();
            foreach (array_slice($questions, 0, 10) as $i => $text) {
                $subtopic->questions()->create([
                    'question'   => trim($text),
                    'sort_order' => $i,
                ]);
            }
        });

        return [
            'status'          => 'saved',
            'subtopic_id'     => $subtopic->id,
            'subtopic_title'  => $subtopic->title,
            'questions_saved' => count(array_slice($questions, 0, 10)),
            'message'         => "Questions saved for subtopic #{$subtopic->id}. Next: call clusterforge_save_answers with subtopic_id={$subtopic->id}.",
        ];
    }

    /**
     * (ClusterForge) Save externally generated answers for a subtopic's questions — Gemini bypass.
     */
    protected function toolClusterforgeSaveAnswers(array $args): array
    {
        $user = Auth::user();
        $subtopicId = (int) ($args['subtopic_id'] ?? 0);

        $subtopic = ClusterForgeSubtopic::whereHas('project', function ($q) use ($args, $user) {
            $q->withoutGlobalScope('current_team');
            if (! ($user && $user->isAdmin())) {
                $q->where('team_id', $this->resolveTeamId($args));
            }
        })->with('questions')->findOrFail($subtopicId);

        $answers = $args['answers'] ?? [];
        if (empty($answers) || ! is_array($answers)) {
            throw new \InvalidArgumentException('answers array is required.');
        }

        $questions = $subtopic->questions()->orderBy('sort_order')->get();
        if ($questions->isEmpty()) {
            throw new \InvalidArgumentException("Subtopic #{$subtopicId} has no questions. Save questions first.");
        }

        DB::transaction(function () use ($questions, $answers) {
            foreach ($questions as $i => $question) {
                $answer = $answers[$i] ?? null;
                $question->update(['answer' => ($answer !== null && trim((string) $answer) !== '') ? trim((string) $answer) : null]);
            }
        });

        return [
            'status'         => 'saved',
            'subtopic_id'    => $subtopic->id,
            'subtopic_title' => $subtopic->title,
            'answers_saved'  => $questions->count(),
            'message'        => "Answers saved for subtopic #{$subtopic->id}. Next: call clusterforge_save_cluster_content with subtopic_id={$subtopic->id}.",
        ];
    }

    /**
     * (ClusterForge) Save externally generated cluster page content for a subtopic — Gemini bypass.
     */
    protected function toolClusterforgeSaveClusterContent(array $args): array
    {
        $user = Auth::user();
        $subtopicId = (int) ($args['subtopic_id'] ?? 0);

        $subtopic = ClusterForgeSubtopic::whereHas('project', function ($q) use ($args, $user) {
            $q->withoutGlobalScope('current_team');
            if (! ($user && $user->isAdmin())) {
                $q->where('team_id', $this->resolveTeamId($args));
            }
        })->with(['project' => fn ($q) => $q->withoutGlobalScope('current_team'), 'questions'])->findOrFail($subtopicId);

        $title = trim($args['title'] ?? '');
        if (empty($title)) {
            throw new \InvalidArgumentException('title is required.');
        }
        $meta  = mb_substr(trim($args['meta_description'] ?? ''), 0, 320);
        $intro = trim($args['introduction_markdown'] ?? '');

        // Build the same cluster_content format as KeywordClusterGenerator::generateClusterPage
        $project = $subtopic->project;
        $faqHeading = ($project && $project->language === 'de') ? 'Häufig gestellte Fragen' : 'Frequently Asked Questions';
        $body = "# {$title}\n\n{$intro}\n\n## {$faqHeading}\n\n";
        foreach ($subtopic->questions()->orderBy('sort_order')->get() as $q) {
            $body .= "### {$q->question}\n\n" . ($q->answer ?? '_Noch keine Antwort generiert._') . "\n\n";
        }

        $subtopic->update([
            'cluster_title'            => $title,
            'cluster_meta_description' => $meta,
            'cluster_content'          => $body,
        ]);

        return [
            'status'              => 'saved',
            'subtopic_id'         => $subtopic->id,
            'subtopic_title'      => $subtopic->title,
            'cluster_title'       => $title,
            'cluster_content_len' => mb_strlen($body),
            'message'             => "Cluster page saved for subtopic #{$subtopic->id} ('{$subtopic->title}').",
        ];
    }

    /**
     * (ClusterForge) Save externally generated pillar page content and mark project completed — Gemini bypass.
     */
    protected function toolClusterforgeSavePillarContent(array $args): array
    {
        $user = Auth::user();
        $projectId = (int) ($args['project_id'] ?? 0);

        $query = ClusterForgeProject::withoutGlobalScope('current_team');
        if (! ($user && $user->isAdmin())) {
            $query->where('team_id', $this->resolveTeamId($args));
        }
        $project = $query->findOrFail($projectId);

        $title = trim($args['title'] ?? '');
        if (empty($title)) {
            throw new \InvalidArgumentException('title is required for pillar page.');
        }
        $meta    = mb_substr(trim($args['meta_description'] ?? ''), 0, 320);
        $content = trim($args['content_markdown'] ?? '');
        if (empty($content)) {
            throw new \InvalidArgumentException('content_markdown is required.');
        }

        $project->update([
            'pillar_title'            => $title,
            'pillar_meta_description' => $meta,
            'pillar_content'          => $content,
            'status'                  => ClusterForgeProject::STATUS_COMPLETED,
            'error'                   => null,
        ]);

        return [
            'status'          => 'completed',
            'project_id'      => $project->id,
            'topic'           => $project->topic,
            'pillar_title'    => $title,
            'content_length'  => mb_strlen($content),
            'message'         => "Pillar page saved and project #{$project->id} ('{$project->topic}') marked as completed.",
        ];
    }

    // --- Module Tools: DevManager ---

    /**
     * (DevManager) List development user stories and backlog items.
     */
    protected function toolDevmanagerListUserStories(array $args): array
    {
        $teamId = $this->resolveTeamId($args);
        $stories = DevUserStory::withoutGlobalScope('current_team')->where('team_id', $teamId)->latest()->limit(50)->get();

        return [
            'team_id' => $teamId,
            'total' => $stories->count(),
            'stories' => $stories->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'description' => $s->description,
                'status' => $s->status,
                'priority' => $s->priority,
                'story_points' => $s->story_points,
                'created_at' => $s->created_at?->toIso8601String(),
            ]),
        ];
    }

    /**
     * (DevManager) Create or update a developer user story / backlog item.
     */
    protected function toolDevmanagerCreateUserStory(array $args): array
    {
        $teamId = $this->resolveTeamId($args);
        $user = Auth::user();

        $title = trim($args['title'] ?? '');
        if (empty($title)) {
            throw new \InvalidArgumentException('title is required.');
        }

        $story = DevUserStory::withoutGlobalScope('current_team')->create([
            'team_id' => $teamId,
            'user_id' => $user?->id ?? 1,
            'title' => $title,
            'description' => $args['description'] ?? '',
            'status' => $args['status'] ?? 'backlog',
            'priority' => $args['priority'] ?? 'medium',
            'story_points' => isset($args['story_points']) ? (int) $args['story_points'] : 3,
        ]);

        return [
            'status' => 'created',
            'story' => [
                'id' => $story->id,
                'title' => $story->title,
                'status' => $story->status,
                'story_points' => $story->story_points,
            ],
            'message' => "DevManager user story '{$title}' created.",
        ];
    }

    /**
     * (DevManager) List development milestones and releases.
     */
    protected function toolDevmanagerListMilestones(array $args): array
    {
        $teamId = $this->resolveTeamId($args);
        $milestones = DevMilestone::withoutGlobalScope('current_team')->where('team_id', $teamId)->orderBy('due_date', 'asc')->get();

        return [
            'team_id' => $teamId,
            'total' => $milestones->count(),
            'milestones' => $milestones->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'status' => $m->status,
                'due_date' => $m->due_date?->toDateString(),
            ]),
        ];
    }

    // --- Module Tools: LoopEngine ---

    /**
     * (LoopEngine) List automated business processes and workflows.
     */
    protected function toolLoopengineListProcesses(array $args): array
    {
        $teamId = $this->resolveTeamId($args);
        $processes = LoopProcess::withoutGlobalScope('current_team')->where('team_id', $teamId)->with('steps')->latest()->get();

        return [
            'team_id' => $teamId,
            'total' => $processes->count(),
            'processes' => $processes->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name_de ?: $p->name_en,
                'category' => $p->category,
                'status' => $p->status,
                'version' => $p->version,
                'steps_count' => $p->steps->count(),
                'created_at' => $p->created_at?->toIso8601String(),
            ]),
        ];
    }

    /**
     * (LoopEngine) Trigger an execution run for an automated process.
     */
    protected function toolLoopengineTriggerProcessRun(array $args): array
    {
        $teamId = $this->resolveTeamId($args);
        $user = Auth::user();

        $processId = (int) ($args['process_id'] ?? 0);
        $process = LoopProcess::withoutGlobalScope('current_team')->where('team_id', $teamId)->findOrFail($processId);

        $run = LoopProcessRun::withoutGlobalScope('current_team')->create([
            'team_id' => $teamId,
            'process_id' => $process->id,
            'user_id' => $user?->id ?? 1,
            'status' => 'running',
            'started_at' => now(),
            'input_data' => $args['input_data'] ?? [],
        ]);

        return [
            'status' => 'started',
            'run_id' => $run->id,
            'process_name' => $process->name_de ?: $process->name_en,
            'message' => "Process run #{$run->id} triggered successfully.",
        ];
    }

    // --- Module Tools: CustomerSuccess ---

    /**
     * (CustomerSuccess) List customer success inquiries and problem diagnoses.
     */
    protected function toolCustomersuccessListInquiries(array $args): array
    {
        $teamId = $this->resolveTeamId($args);
        $inquiries = CustomerSuccessInquiry::withoutGlobalScope('current_team')->where('team_id', $teamId)->latest()->limit(30)->get();

        return [
            'team_id' => $teamId,
            'total' => $inquiries->count(),
            'inquiries' => $inquiries->map(fn ($i) => [
                'id' => $i->id,
                'question' => $i->question,
                'problem' => $i->problem,
                'priority' => $i->priority,
                'created_at' => $i->created_at?->toIso8601String(),
            ]),
        ];
    }

    /**
     * (CustomerSuccess) Diagnose a customer success problem with root cause and recommended actions.
     */
    protected function toolCustomersuccessDiagnoseInquiry(array $args): array
    {
        $teamId = $this->resolveTeamId($args);

        $question = trim($args['question'] ?? '');
        $problem = trim($args['problem'] ?? '');

        if (empty($question) || empty($problem)) {
            throw new \InvalidArgumentException('question and problem are required.');
        }

        $inquiry = CustomerSuccessInquiry::withoutGlobalScope('current_team')->create([
            'team_id' => $teamId,
            'question' => $question,
            'answer' => $args['answer'] ?? null,
            'problem' => $problem,
            'root_cause' => $args['root_cause'] ?? null,
            'consequences' => $args['consequences'] ?? null,
            'recommended_actions' => $args['recommended_actions'] ?? null,
            'priority' => $args['priority'] ?? 'medium',
            'estimated_cost' => $args['estimated_cost'] ?? null,
            'expected_benefit' => $args['expected_benefit'] ?? null,
            'module_key' => $args['module_key'] ?? null,
        ]);

        return [
            'status' => 'created',
            'inquiry_id' => $inquiry->id,
            'priority' => $inquiry->priority,
            'message' => "Customer success case #{$inquiry->id} diagnosed and stored.",
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
                ['uri' => 'allocore://workforce/times', 'name' => 'TimeButler Workforce & Time Entries', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://customers/sweet-spot', 'name' => 'SweetSpot Customer Profitability Matrix', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://projects/portfolio', 'name' => 'PlanHive Project Portfolio & Goal Progress', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://production/orders', 'name' => 'DentalTrack Production Orders & Workstations', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://debts/snowball', 'name' => 'Debt Snowball & Avalanche Payoff Overview', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://integrations/status', 'name' => 'Webhooks & Third-Party Integrations', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://financial/summary', 'name' => 'Financial Overview & Active Subscriptions', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://admin/announcements', 'name' => 'Active Platform Dashboard Announcements', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://admin/coupons', 'name' => 'Active Promo & Discount Coupons', 'mimeType' => 'application/json'],
                ['uri' => 'allocore://clusterforge/projects', 'name' => 'ClusterForge SEO Topic Clusters', 'mimeType' => 'application/json'],
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
            'allocore://knowledge/terms' => GlossaryTerm::published()->get(['id', 'term', 'slug', 'category'])->toJson(JSON_PRETTY_PRINT),
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
            'allocore://workforce/times' => json_encode($this->toolGetTeamWorkforceSummary([]), JSON_PRETTY_PRINT),
            'allocore://customers/sweet-spot' => json_encode($this->toolListSweetSpotRankings([]), JSON_PRETTY_PRINT),
            'allocore://projects/portfolio' => json_encode($this->toolGetProjectPortfolioOverview([]), JSON_PRETTY_PRINT),
            'allocore://production/orders' => json_encode($this->toolTrackProductionOrderStatus([]), JSON_PRETTY_PRINT),
            'allocore://debts/snowball' => json_encode($this->toolGetSnowballFinancialSummary([]), JSON_PRETTY_PRINT),
            'allocore://integrations/status' => json_encode($this->toolListWebhooksAndIntegrations([]), JSON_PRETTY_PRINT),
            'allocore://financial/summary' => json_encode($this->toolGetFinancialSummary(), JSON_PRETTY_PRINT),
            'allocore://admin/announcements' => Announcement::active()->get(['id', 'title', 'body', 'type', 'starts_at', 'ends_at'])->toJson(JSON_PRETTY_PRINT),
            'allocore://admin/coupons' => Coupon::where('is_active', true)->get(['id', 'code', 'type', 'value', 'max_uses', 'used_count', 'expires_at'])->toJson(JSON_PRETTY_PRINT),
            'allocore://clusterforge/projects' => json_encode($this->toolClusterforgeListProjects([]), JSON_PRETTY_PRINT),
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
                    'name' => 'debt_payoff_strategist',
                    'description' => 'Analyze company liabilities, compare Debt Snowball vs Debt Avalanche payoff timelines, and formulate an accelerated debt-free roadmap.',
                    'arguments' => [
                        ['name' => 'extra_monthly_budget', 'required' => false],
                        ['name' => 'strategy', 'required' => false],
                        ['name' => 'team_id', 'required' => false],
                    ],
                ],
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
                    'name' => 'icp_customer_analyzer',
                    'description' => 'Analyze client portfolio with Sweet Spot profitability matrix to isolate Ideal Customer Profile (ICP).',
                    'arguments' => [['name' => 'customer_id', 'required' => false], ['name' => 'industry', 'required' => false]],
                ],
                [
                    'name' => 'project_risk_assessor',
                    'description' => 'Assess milestone bottlenecks, delayed project goals, and resource constraints in PlanHive.',
                    'arguments' => [['name' => 'project_id', 'required' => true]],
                ],
                [
                    'name' => 'workforce_capacity_planner',
                    'description' => 'Evaluate logged workforce hours, overtime patterns, and team capacity from TimeButler.',
                    'arguments' => [['name' => 'team_id', 'required' => false]],
                ],
                [
                    'name' => 'auto_link_audit_solutions',
                    'description' => 'Inspect unassigned questions and deduce optimal tools & books.',
                    'arguments' => [],
                ],
                [
                    'name' => 'clusterforge_seo_architect',
                    'description' => 'Analyze SEO keyword clusters, search volumes, and outline a complete content silo architecture.',
                    'arguments' => [
                        ['name' => 'topic', 'required' => false],
                        ['name' => 'project_id', 'required' => false],
                    ],
                ],
            ],
        ];
    }

    public function getPrompt(string $name, array $args): array
    {
        $promptText = match ($name) {
            'clusterforge_seo_architect' => "Sie sind der Allocore Senior SEO & Content Strategy Director. Analysieren Sie das Keyword-Cluster und die Themenarchitektur für das Thema/Projekt '".($args['topic'] ?? 'SEO Strategie')."'. Strukturieren Sie eine hochkonvertierende Content-Silo-Architektur: 1) Pillar Page (Cornerstone Content), 2) Subtopic Cluster Pages mit Fokus auf High-Intent Long-Tail Keywords, 3) Interne Verlinkungsstruktur (Internal Linking Hub & Spoke), 4) Relevante W-Fragen für Featured Snippets.",
            'debt_payoff_strategist' => "Sie sind der Allocore Senior Corporate Finance & Debt Strategist. Analysieren Sie die Verbindlichkeiten und Schulden des Unternehmens. Vergleichen Sie die Schneeball-Methode (Snowball: kleinste Salden zuerst für schnelle psychologische und operative Siege) mit der Lawinen-Methode (Avalanche: höchste Zinssätze zuerst zur Zinsminimierung). Bei einem monatlichen Zusatztilgungsbudget von ".($args['extra_monthly_budget'] ?? '500')." EUR: Erstellen Sie einen monatlichen Tilgungsplan, berechnen Sie das exakte Entschuldungsdatum (Debt-Free Date) und heben Sie die Gesamtzinsersparnis hervor.",
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
            'icp_customer_analyzer' => "Analysieren Sie das Kundenportfolio anhand der Sweet-Spot-Matrix (Deckungsbeitrag pro Arbeitsstunde, Harmonie, Weiterempfehlung). Identifizieren Sie Klasse-A Wunschkunden und formulieren Sie klare Kriterien zur Neukundengewinnung.",
            'project_risk_assessor' => "Führen Sie eine strukturierte Risiko- und Engpassanalyse für Projekt #".($args['project_id'] ?? 1).". Prüfen Sie Meilenstein-Fortschritte, offene Aufgaben und formulieren Sie Präventivmaßnahmen gegen Verzug.",
            'workforce_capacity_planner' => "Analysieren Sie die Arbeitszeiterfassung und Teamauslastung. Identifizieren Sie Überstundenrisiken, Kapazitätsengpässe und Optimierungspotenziale bei der Schicht- und Ressourcenplanung.",
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
