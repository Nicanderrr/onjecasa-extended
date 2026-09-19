<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\BranchContext;
use App\Support\BranchProductSync;
use App\Support\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AIController extends Controller
{
    public function index(): View
    {
        return view('pos_admin.ai.index');
    }

    public function chat(Request $request)
    {
        if (! $this->aiEnabled()) {
            return response()->json(['error' => 'POS AI assistant is disabled by the superadmin.'], 403);
        }

        $message = (string) $request->input('message', '');
        $history = $request->input('history', []);

        if (trim($message) === '') {
            return response()->json(['reply' => 'Please type a message.'], 422);
        }

        $context = $this->getSystemContext();
        $apiKey = config('services.openai.api_key');
        $model = env('OPENAI_MODEL', 'gpt-4o-mini');

        if (! $apiKey) {
            return response()->json(['reply' => 'OpenAI API key is not configured. Add OPENAI_API_KEY in .env.']);
        }

        try {
            $messages = array_merge([
                ['role' => 'system', 'content' => $context],
            ], is_array($history) ? $history : []);

            $messages[] = ['role' => 'user', 'content' => $message];

            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'tools' => $this->aiActionTools(),
                    'tool_choice' => 'auto',
                ]);

            if ($response->successful()) {
                $assistantMessage = data_get($response->json(), 'choices.0.message', []);
                $toolCalls = data_get($assistantMessage, 'tool_calls', []);

                if (is_array($toolCalls) && count($toolCalls) > 0) {
                    $messages[] = $assistantMessage;

                    foreach ($toolCalls as $toolCall) {
                        $messages[] = [
                            'role' => 'tool',
                            'tool_call_id' => data_get($toolCall, 'id'),
                            'content' => json_encode($this->executeAiAction($toolCall), JSON_THROW_ON_ERROR),
                        ];
                    }

                    $finalResponse = Http::withToken($apiKey)
                        ->timeout(30)
                        ->post('https://api.openai.com/v1/chat/completions', [
                            'model' => $model,
                            'messages' => $messages,
                        ]);

                    if (! $finalResponse->successful()) {
                        Log::error('OpenAI final API error', ['status' => $finalResponse->status(), 'body' => $finalResponse->body()]);
                        return response()->json(['error' => 'OpenAI API error after action: ' . $finalResponse->status()], 500);
                    }

                    $rawReply = (string) data_get($finalResponse->json(), 'choices.0.message.content', 'Action completed.');

                    return response()->json([
                        'reply' => $this->normalizePlainReply($rawReply),
                    ]);
                }

                $rawReply = (string) data_get($assistantMessage, 'content', 'No response generated.');
                return response()->json([
                    'reply' => $this->normalizePlainReply($rawReply),
                ]);
            }

            Log::error('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
            $providerMessage = (string) data_get($response->json(), 'error.message', '');
            $providerCode = (string) data_get($response->json(), 'error.code', '');
            $details = trim($providerMessage . ($providerCode !== '' ? ' (' . $providerCode . ')' : ''));

            return response()->json([
                'error' => $details !== '' ? $details : ('OpenAI API error: ' . $response->status()),
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Admin AI exception', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function alerts()
    {
        if (! $this->aiEnabled()) {
            return response()->json(['error' => 'POS AI assistant is disabled by the superadmin.'], 403);
        }

        $lowStock = DB::table('pos_products')
            ->when(BranchContext::activeId(), fn ($query, $branchId) => $query->where('branch_id', $branchId))
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(10)
            ->get(['id', 'name', 'stock']);

        $outOfStock = $lowStock->where('stock', '<=', 0)->count();

        return response()->json([
            'has_alert' => $lowStock->count() > 0,
            'low_stock_count' => $lowStock->count(),
            'out_of_stock_count' => $outOfStock,
            'items' => $lowStock->values(),
            'message' => $lowStock->count() > 0
                ? 'Low stock detected on ' . $lowStock->count() . ' products.'
                : 'No low stock alerts.',
        ]);
    }

    public function analyze(Request $request)
    {
        if (! $this->aiEnabled()) {
            return response()->json(['error' => 'POS AI assistant is disabled by the superadmin.'], 403);
        }

        $request->validate([
            'file' => 'required|file|max:10240',
            'prompt' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $prompt = (string) $request->input('prompt', 'Analyze this file and summarize only operationally useful details for the POS.');
        $apiKey = config('services.openai.api_key');

        if (! $apiKey) {
            return response()->json(['reply' => 'OpenAI API key is not configured. Add OPENAI_API_KEY in .env.']);
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);

        if (! $isImage) {
            return response()->json([
                'reply' => 'File received: ' . $file->getClientOriginalName() . '. Document parsing is not enabled yet; upload an image for full AI analysis.',
            ]);
        }

        $model = env('OPENAI_VISION_MODEL', 'gpt-4o-mini');
        $base64Image = base64_encode(file_get_contents($file->path()));
        $mimeType = (string) $file->getMimeType();

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [[
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt],
                            ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$base64Image}"]],
                        ],
                    ]],
                ]);

            if ($response->successful()) {
                return response()->json([
                    'reply' => data_get($response->json(), 'choices.0.message.content', 'No response generated.'),
                ]);
            }

            Log::error('OpenAI vision API error', ['status' => $response->status(), 'body' => $response->body()]);
            return response()->json(['error' => 'OpenAI vision API error: ' . $response->status()], 500);
        } catch (\Throwable $e) {
            Log::error('Admin AI vision exception', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getSystemContext(): string
    {
        $totalProducts = (int) DB::table('pos_products')->count();
        $branchId = BranchContext::activeId();
        $branchName = BranchContext::isOverall() ? 'Overall' : (BranchContext::active()?->name ?? 'No branch selected');
        $totalProducts = (int) DB::table('pos_products')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->count();
        $lowStockProducts = (int) DB::table('pos_products')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->where('stock', '<=', 10)->count();
        $outOfStockProducts = (int) DB::table('pos_products')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->where('stock', '<=', 0)->count();
        $totalOrders = (int) DB::table('pos_orders')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->count();
        $pendingOrders = (int) DB::table('pos_orders')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->where('status', 'pending')->count();
        $completedOrders = (int) DB::table('pos_orders')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->where('status', 'completed')->count();
        $totalPayments = (int) DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->count();
        $salesTotal = (float) DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->sum('p.amount');
        $todaySales = (float) DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->whereDate('p.created_at', now()->toDateString())->sum('p.amount');
        $totalStaff = (int) DB::table('pos_staff')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->count();
        $totalCategories = (int) DB::table('pos_categories')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->count();
        $adminUsers = (int) DB::table('users')->where('role', 'admin')->count();
        $cashierUsers = (int) DB::table('users')->where('role', 'cashier')->count();

        $recentOrders = DB::table('pos_orders')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(function ($o) {
                $code = $o->code ?: ('ORD-' . $o->id);
                $total = $o->grand_total ?? $o->total_amount ?? 0;
                $status = $o->status ?: 'unknown';
                return "- {$code}: total {$total}, status {$status}";
            })->implode("\n");

        $topProducts = DB::table('pos_order_items as oi')
            ->join('pos_products as p', 'p.id', '=', 'oi.product_id')
            ->selectRaw('p.name as name, SUM(oi.qty) as qty_sold')
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('qty_sold')
            ->limit(5)
            ->get()
            ->map(fn ($row) => "- {$row->name}: {$row->qty_sold} sold")
            ->implode("\n");

        $lowStockNames = DB::table('pos_products')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('stock', '<=', 10)
            ->orderBy('stock')
            ->limit(10)
            ->get(['name', 'stock'])
            ->map(fn ($p) => "- {$p->name}: {$p->stock} left")
            ->implode("\n");

        $productCatalog = DB::table('pos_products')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->limit(60)
            ->get(['id', 'code', 'name', 'price', 'stock'])
            ->map(fn ($p) => "- ID {$p->id}, Code {$p->code}, {$p->name}: GHS {$p->price}, stock {$p->stock}")
            ->implode("\n");

        $schemaOverview = collect(DB::select('SHOW TABLES'))
            ->map(function ($row) {
                $table = array_values((array) $row)[0] ?? null;
                return $table ? "- {$table}" : null;
            })
            ->filter()
            ->values()
            ->implode("\n");

        return "You are ONJECASA POS Command AI Assistant.
Current System Time: " . now()->format('Y-m-d H:i:s') . "

SYSTEM STATE:
- Active Branch: {$branchName} (#{$branchId})
- Products: {$totalProducts}
- Low Stock (<=10): {$lowStockProducts}
- Out of Stock: {$outOfStockProducts}
- Orders: {$totalOrders}
- Pending Orders: {$pendingOrders}
- Completed Orders: {$completedOrders}
- Payments: {$totalPayments}
- Total Sales: {$salesTotal}
- Today's Sales: {$todaySales}
- Staff Records: {$totalStaff}
- Categories: {$totalCategories}
- Admin Users: {$adminUsers}
- Cashier Users: {$cashierUsers}

TOP PRODUCTS:
" . ($topProducts !== '' ? $topProducts : "- No sales yet") . "

LOW STOCK ITEMS:
" . ($lowStockNames !== '' ? $lowStockNames : "- None") . "

PRODUCT CATALOG FOR EXACT EDITS:
" . ($productCatalog !== '' ? $productCatalog : "- No products") . "

RECENT ORDERS:
" . ($recentOrders !== '' ? $recentOrders : "- No orders yet") . "

DATABASE TABLES:
{$schemaOverview}

INTERACTION RULES:
1. Speak like an operations assistant: concise, confident, practical.
2. Use plain English sentences only. Do not use markdown, asterisks, hashtags, or decorative symbols.
3. Prioritize action: what to check, what to fix, what to do next.
4. When the admin asks you to create users, reset passwords, activate or deactivate users, create POS products, or update existing POS products, use the available system tools.
5. If data is missing, say exactly what is missing.
6. Ignore noisy symbols and focus on meaningful operational content.
7. Never expose secrets, API keys, or raw credential values.
8. Use GHS or GHC for money. Do not use dollar signs for this business.
9. If you create or reset a user password, clearly return the login email and temporary password the admin supplied or that the system generated.
10. For product edits, update an existing product. Do not create a product unless the admin clearly asks to create, add, or register a new product.
11. If a product name matches more than one product, ask the admin to choose from the matching IDs. Do not guess.
12. Keep answers short unless the user asks for details.";
    }

    private function normalizePlainReply(string $reply): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $reply);
        $text = preg_replace('/[`*_#>~]+/u', '', $text);
        $text = preg_replace('/^\s*[-•]\s*/mu', '', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);
        return trim((string) $text);
    }

    private function aiActionTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_system_user',
                    'description' => 'Create a customer, cashier, or admin login account. Cashiers are also added to POS staff.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Full name for the user.'],
                            'email' => ['type' => 'string', 'description' => 'Unique login email address.'],
                            'password' => ['type' => 'string', 'description' => 'Optional password. If omitted, the system generates one.'],
                            'role' => ['type' => 'string', 'enum' => ['customer', 'cashier', 'admin'], 'description' => 'Type of account to create.'],
                            'staff_number' => ['type' => 'string', 'description' => 'Required or generated for cashier staff records.'],
                            'active' => ['type' => 'boolean', 'description' => 'Whether the account should be active. Defaults to true.'],
                        ],
                        'required' => ['name', 'email', 'role'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'reset_user_password',
                    'description' => 'Reset an existing user password by email.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'email' => ['type' => 'string'],
                            'password' => ['type' => 'string', 'description' => 'Optional new password. If omitted, the system generates one.'],
                        ],
                        'required' => ['email'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'set_user_status',
                    'description' => 'Activate or deactivate an existing user account by email.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'email' => ['type' => 'string'],
                            'active' => ['type' => 'boolean'],
                        ],
                        'required' => ['email', 'active'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_pos_product',
                    'description' => 'Create a new basic in-store POS product without image upload. Use only when the admin clearly asks to create, add, or register a new product.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'code' => ['type' => 'string', 'description' => 'Optional SKU. The system generates one if omitted.'],
                            'description' => ['type' => 'string'],
                            'price' => ['type' => 'number'],
                            'stock' => ['type' => 'integer'],
                        ],
                        'required' => ['name', 'price', 'stock'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'update_pos_product',
                    'description' => 'Update an existing POS product precisely. Use this for changing product price, stock, code, name, or description.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product_id' => ['type' => 'integer', 'description' => 'Exact POS product ID when known.'],
                            'code' => ['type' => 'string', 'description' => 'Exact existing POS SKU/code when known.'],
                            'name' => ['type' => 'string', 'description' => 'Existing product name to find when ID/code is not known.'],
                            'new_name' => ['type' => 'string', 'description' => 'New product name, if renaming.'],
                            'new_code' => ['type' => 'string', 'description' => 'New SKU/code, if changing it.'],
                            'description' => ['type' => 'string', 'description' => 'New description, if changing it.'],
                            'price' => ['type' => 'number', 'description' => 'Absolute new price.'],
                            'price_delta' => ['type' => 'number', 'description' => 'Amount to add to the current price. Use negative values to reduce price.'],
                            'stock' => ['type' => 'integer', 'description' => 'Absolute new stock quantity.'],
                            'stock_delta' => ['type' => 'integer', 'description' => 'Amount to add to current stock. Use negative values to reduce stock.'],
                            'sync_to_website' => ['type' => 'boolean', 'description' => 'Whether to update linked website product too. Defaults to true.'],
                        ],
                        'required' => [],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];
    }

    private function executeAiAction(array $toolCall): array
    {
        $name = (string) data_get($toolCall, 'function.name');
        $arguments = json_decode((string) data_get($toolCall, 'function.arguments', '{}'), true);

        if (! is_array($arguments)) {
            return ['ok' => false, 'message' => 'Invalid action arguments.'];
        }

        try {
            return match ($name) {
                'create_system_user' => $this->aiCreateSystemUser($arguments),
                'reset_user_password' => $this->aiResetUserPassword($arguments),
                'set_user_status' => $this->aiSetUserStatus($arguments),
                'create_pos_product' => $this->aiCreatePosProduct($arguments),
                'update_pos_product' => $this->aiUpdatePosProduct($arguments),
                default => ['ok' => false, 'message' => 'Unknown action: ' . $name],
            };
        } catch (\Throwable $e) {
            Log::error('Admin AI action failed', [
                'action' => $name,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function aiCreateSystemUser(array $arguments): array
    {
        $data = Validator::make($arguments, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:pos_staff,email'],
            'password' => ['nullable', 'string', 'min:6', 'max:100'],
            'role' => ['required', 'in:customer,cashier,admin,branch_admin'],
            'staff_number' => ['nullable', 'string', 'max:100', 'unique:pos_staff,number'],
            'active' => ['nullable', 'boolean'],
        ])->validate();

        $password = $data['password'] ?? $this->generateTemporaryPassword();
        $role = $data['role'];
        $active = array_key_exists('active', $data) ? (bool) $data['active'] : true;
        $branchId = BranchContext::activeId();

        if (in_array($role, ['cashier', 'admin', 'branch_admin'], true) && ! $branchId) {
            return ['ok' => false, 'message' => 'Select a branch before creating admin, branch admin, or cashier users.'];
        }

        [$userId, $staffId, $staffNumber] = DB::transaction(function () use ($data, $password, $role, $active, $branchId) {
            $user = new User();
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($password);
            $user->role = $role;
            $user->is_admin = $role === 'admin' ? 1 : 0;
            $user->is_active = $active;
            $user->email_verified_at = now();
            $user->save();

            $staffId = null;
            $staffNumber = $data['staff_number'] ?? null;

            if ($role === 'cashier') {
                $staffNumber = $staffNumber ?: 'STAFF-' . strtoupper(Str::random(6));
                $staffId = DB::table('pos_staff')->insertGetId([
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'name' => $data['name'],
                    'number' => $staffNumber,
                    'email' => $data['email'],
                    'pincode' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (in_array($role, ['cashier', 'admin', 'branch_admin'], true)) {
                BranchContext::syncUserBranches($user, [$branchId]);
            }

            return [$user->id, $staffId, $staffNumber];
        });

        AuditTrail::record('ai_user_created', 'AI created ' . $role . ' account ' . $data['email'], [
            'auditable_type' => 'user',
            'auditable_id' => $userId,
            'properties' => [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $role,
                'staff_id' => $staffId,
                'staff_number' => $staffNumber,
                'active' => $active,
                'password' => $password,
            ],
        ]);

        return [
            'ok' => true,
            'message' => ucfirst($role) . ' account created.',
            'data' => [
                'user_id' => $userId,
                'staff_id' => $staffId,
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $role,
                'staff_number' => $staffNumber,
                'active' => $active,
                'temporary_password' => $password,
            ],
        ];
    }

    private function aiResetUserPassword(array $arguments): array
    {
        $data = Validator::make($arguments, [
            'email' => ['required', 'email'],
            'password' => ['nullable', 'string', 'min:6', 'max:100'],
        ])->validate();

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return ['ok' => false, 'message' => 'No user exists with email ' . $data['email'] . '.'];
        }

        $password = $data['password'] ?? $this->generateTemporaryPassword();
        $user->forceFill(['password' => Hash::make($password)])->save();

        AuditTrail::record('ai_user_password_reset', 'AI reset password for ' . $user->email, [
            'auditable_type' => 'user',
            'auditable_id' => $user->id,
            'properties' => [
                'email' => $user->email,
                'password' => $password,
            ],
        ]);

        return [
            'ok' => true,
            'message' => 'Password reset.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'temporary_password' => $password,
            ],
        ];
    }

    private function aiSetUserStatus(array $arguments): array
    {
        $data = Validator::make($arguments, [
            'email' => ['required', 'email'],
            'active' => ['required', 'boolean'],
        ])->validate();

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return ['ok' => false, 'message' => 'No user exists with email ' . $data['email'] . '.'];
        }

        $user->forceFill(['is_active' => (bool) $data['active']])->save();

        AuditTrail::record('ai_user_status_updated', 'AI ' . ((bool) $data['active'] ? 'activated ' : 'deactivated ') . $user->email, [
            'auditable_type' => 'user',
            'auditable_id' => $user->id,
            'properties' => [
                'email' => $user->email,
                'active' => (bool) $data['active'],
            ],
        ]);

        return [
            'ok' => true,
            'message' => ((bool) $data['active'] ? 'User activated.' : 'User deactivated.'),
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'active' => (bool) $data['active'],
            ],
        ];
    }

    private function aiCreatePosProduct(array $arguments): array
    {
        $data = Validator::make($arguments, [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ])->validate();

        $code = $data['code'] ?? ('PRD-' . random_int(1000, 9999));
        $branchId = BranchContext::activeId();

        if (! $branchId) {
            return ['ok' => false, 'message' => 'Select a branch before creating a POS product.'];
        }

        if (DB::table('pos_products')->where('branch_id', $branchId)->where('code', $code)->exists()) {
            return ['ok' => false, 'message' => 'Another product in this branch already uses code ' . $code . '.'];
        }

        $productId = DB::table('pos_products')->insertGetId([
            'branch_id' => $branchId,
            'code' => $code,
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'price' => $data['price'],
            'stock' => $data['stock'],
            'image' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = DB::table('pos_products')->where('id', $productId)->first();
        BranchProductSync::restore($branchId, $code);
        BranchProductSync::syncProductToBranches($product, syncStock: true);

        AuditTrail::record('ai_product_created', 'AI created POS product ' . $data['name'], [
            'auditable_type' => 'product',
            'auditable_id' => $productId,
            'properties' => [
                'code' => $code,
                'name' => $data['name'],
                'price' => $data['price'],
                'stock' => $data['stock'],
            ],
        ]);

        return [
            'ok' => true,
            'message' => 'POS product created.',
            'data' => [
                'product_id' => $productId,
                'code' => $code,
                'name' => $data['name'],
                'price' => $data['price'],
                'stock' => $data['stock'],
            ],
        ];
    }

    private function aiUpdatePosProduct(array $arguments): array
    {
        if (! BranchContext::activeId()) {
            return ['ok' => false, 'message' => 'Select a branch before changing POS product values. Overall mode is read-only for AI product edits.'];
        }

        $data = Validator::make($arguments, [
            'product_id' => ['nullable', 'integer'],
            'code' => ['nullable', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:255'],
            'new_name' => ['nullable', 'string', 'max:255'],
            'new_code' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_delta' => ['nullable', 'numeric'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'stock_delta' => ['nullable', 'integer'],
            'sync_to_website' => ['nullable', 'boolean'],
        ])->validate();

        $resolution = $this->resolvePosProduct($data);

        if (! ($resolution['ok'] ?? false)) {
            return $resolution;
        }

        $product = $resolution['product'];
        $oldCode = (string) $product->code;
        $updates = [];

        if (array_key_exists('new_name', $data) && filled($data['new_name'])) {
            $updates['name'] = $data['new_name'];
        }

        if (array_key_exists('new_code', $data) && filled($data['new_code'])) {
            $existingCode = DB::table('pos_products')
                ->where('branch_id', BranchContext::activeId())
                ->where('code', $data['new_code'])
                ->where('id', '<>', $product->id)
                ->exists();

            if ($existingCode) {
                return ['ok' => false, 'message' => 'Another POS product already uses code ' . $data['new_code'] . '.'];
            }

            $updates['code'] = $data['new_code'];
        }

        if (array_key_exists('description', $data)) {
            $updates['description'] = (string) ($data['description'] ?? '');
        }

        if (array_key_exists('price', $data) && $data['price'] !== null) {
            $updates['price'] = round((float) $data['price'], 2);
        } elseif (array_key_exists('price_delta', $data) && $data['price_delta'] !== null) {
            $updates['price'] = max(0, round((float) $product->price + (float) $data['price_delta'], 2));
        }

        if (array_key_exists('stock', $data) && $data['stock'] !== null) {
            $updates['stock'] = (int) $data['stock'];
        } elseif (array_key_exists('stock_delta', $data) && $data['stock_delta'] !== null) {
            $updates['stock'] = max(0, (int) $product->stock + (int) $data['stock_delta']);
        }

        if (empty($updates)) {
            return [
                'ok' => false,
                'message' => 'No product changes were provided. Specify price, stock, name, code, or description.',
            ];
        }

        $before = (array) $product;
        $updates['updated_at'] = now();

        DB::table('pos_products')
            ->where('branch_id', BranchContext::activeId())
            ->where('id', $product->id)
            ->update($updates);

        $updatedProduct = DB::table('pos_products')
            ->where('branch_id', BranchContext::activeId())
            ->where('id', $product->id)
            ->first();
        $websiteSynced = false;

        if (($data['sync_to_website'] ?? true) && ! empty($updatedProduct->website_product_id)) {
            $websiteUpdates = [
                'name' => $updatedProduct->name,
                'contents' => $updatedProduct->description ?: $updatedProduct->name,
                'price' => $updatedProduct->price,
                'stock' => max(0, (int) ($updatedProduct->stock ?? 0)),
                'updated_at' => now(),
            ];

            DB::table('product_pages')->where('id', $updatedProduct->website_product_id)->update($websiteUpdates);
            $websiteSynced = true;
        }

        AuditTrail::record('ai_product_updated', 'AI updated POS product ' . $updatedProduct->name, [
            'auditable_type' => 'product',
            'auditable_id' => $updatedProduct->id,
            'properties' => [
                'before' => $before,
                'after' => (array) $updatedProduct,
                'website_synced' => $websiteSynced,
            ],
        ]);

        BranchProductSync::syncProductToBranches($updatedProduct, $oldCode, false);

        return [
            'ok' => true,
            'message' => 'POS product updated.',
            'data' => [
                'product_id' => $updatedProduct->id,
                'code' => $updatedProduct->code,
                'name' => $updatedProduct->name,
                'old_price' => (float) $product->price,
                'new_price' => (float) $updatedProduct->price,
                'old_stock' => (int) $product->stock,
                'new_stock' => (int) $updatedProduct->stock,
                'website_synced' => $websiteSynced,
            ],
        ];
    }

    private function resolvePosProduct(array $data): array
    {
        if (! empty($data['product_id'])) {
            $product = DB::table('pos_products')->where('branch_id', BranchContext::activeId())->where('id', (int) $data['product_id'])->first();

            return $product
                ? ['ok' => true, 'product' => $product]
                : ['ok' => false, 'message' => 'No POS product exists with ID ' . $data['product_id'] . '.'];
        }

        if (! empty($data['code'])) {
            $product = DB::table('pos_products')->where('branch_id', BranchContext::activeId())->where('code', $data['code'])->first();

            return $product
                ? ['ok' => true, 'product' => $product]
                : ['ok' => false, 'message' => 'No POS product exists with code ' . $data['code'] . '.'];
        }

        if (! empty($data['name'])) {
            $needle = trim((string) $data['name']);
            $exactMatches = DB::table('pos_products')
                ->where('branch_id', BranchContext::activeId())
                ->whereRaw('LOWER(name) = ?', [strtolower($needle)])
                ->get();

            if ($exactMatches->count() === 1) {
                return ['ok' => true, 'product' => $exactMatches->first()];
            }

            $matches = $exactMatches->count() > 1
                ? $exactMatches
                : DB::table('pos_products')
                    ->where('branch_id', BranchContext::activeId())
                    ->where(function ($query) use ($needle) {
                        $query->where('name', 'like', '%' . $needle . '%')
                            ->orWhere('code', 'like', '%' . $needle . '%');
                    })
                    ->orderBy('name')
                    ->limit(8)
                    ->get();

            if ($matches->count() === 1) {
                return ['ok' => true, 'product' => $matches->first()];
            }

            if ($matches->count() > 1) {
                return [
                    'ok' => false,
                    'message' => 'Multiple products matched. Ask the admin to choose a product ID before changing anything.',
                    'matches' => $matches->map(fn ($p) => [
                        'id' => $p->id,
                        'code' => $p->code,
                        'name' => $p->name,
                        'price' => $p->price,
                        'stock' => $p->stock,
                    ])->values()->all(),
                ];
            }

            return ['ok' => false, 'message' => 'No POS product matched ' . $needle . '.'];
        }

        return ['ok' => false, 'message' => 'Specify the product ID, code, or existing product name to update.'];
    }

    private function generateTemporaryPassword(): string
    {
        return 'Temp-' . Str::upper(Str::random(4)) . '-' . random_int(1000, 9999);
    }

    private function aiEnabled(): bool
    {
        $raw = DB::table('pos_settings')->where('key', 'superadmin_security')->value('value');
        $settings = $raw ? json_decode($raw, true) : [];

        if (! is_array($settings)) {
            return true;
        }

        return (bool) ($settings['ai_enabled'] ?? true);
    }
}

