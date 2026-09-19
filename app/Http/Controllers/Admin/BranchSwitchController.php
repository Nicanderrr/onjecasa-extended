<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Support\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchSwitchController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => ['required'],
        ]);

        $branchId = $data['branch_id'] === 'overall' ? 'overall' : (int) $data['branch_id'];

        if ($branchId !== 'overall') {
            abort_unless(Branch::whereKey($branchId)->where('is_active', true)->exists(), 404);
        }

        abort_unless(BranchContext::canAccess($request->user(), $branchId), 403);

        session(['active_branch_id' => $branchId]);

        return back()->with('success', 'Branch switched.');
    }
}
