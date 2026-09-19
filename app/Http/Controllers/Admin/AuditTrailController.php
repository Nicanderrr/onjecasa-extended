<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'event' => ['nullable', 'string', 'max:255'],
        ]);

        $branchId = BranchContext::activeId();
        $baseQuery = DB::table('pos_audit_trails')->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
        $query = DB::table('pos_audit_trails')->when($branchId, fn ($builder) => $builder->where('branch_id', $branchId))->orderByDesc('created_at')->orderByDesc('id');

        if (!empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $summary = [
            'audit_count' => (clone $baseQuery)->count(),
            'today_count' => (clone $baseQuery)->whereDate('created_at', now()->toDateString())->count(),
            'admin_count' => (clone $baseQuery)->where('user_role', 'admin')->count(),
            'cashier_count' => (clone $baseQuery)->where('user_role', 'cashier')->count(),
            'latest_audit_at' => (clone $baseQuery)->max('created_at'),
        ];

        $events = DB::table('pos_audit_trails')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->select('event')->distinct()->orderBy('event')->pluck('event');
        $audits = $query->paginate(25)->withQueryString();

        return view('pos_admin.audit-trails.index', compact('audits', 'events', 'filters', 'summary'));
    }
}
