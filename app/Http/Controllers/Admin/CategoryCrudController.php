<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\BranchContext;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategoryCrudController extends Controller
{
    public function index(): View { $rows = BranchContext::scope(DB::table('pos_categories'))->orderByDesc('id')->get(); return view('pos_admin.categories.index', compact('rows')); }
    public function create(): View { return view('pos_admin.categories.create'); }
    public function store(Request $request): RedirectResponse {
        $d = $request->validate(['name'=>'required|string|max:255','code'=>'nullable|string|max:100']);
        $branchId = BranchContext::activeId();
        if (! $branchId) {
            return redirect()->back()->withInput()->with('error', 'Select a branch before creating a POS category.');
        }
        $categoryId = DB::table('pos_categories')->insertGetId(['branch_id'=>$branchId,'name'=>$d['name'],'code'=>$d['code'] ?: ('CAT-'.random_int(1000,9999)),'created_at'=>now(),'updated_at'=>now()]);
        AuditTrail::record('category_created', 'Created category '.$d['name'], ['auditable_type'=>'category','auditable_id'=>$categoryId,'properties'=>['category'=>$d]]);
        return redirect()->route('pos.admin.categories.index')->with('success','Category Added');
    }
    public function edit(int $id): View { $row = BranchContext::scope(DB::table('pos_categories'))->where('id',$id)->first(); abort_unless($row,404); return view('pos_admin.categories.edit', compact('row')); }
    public function update(Request $request, int $id): RedirectResponse {
        $d = $request->validate(['name'=>'required|string|max:255','code'=>'required|string|max:100']);
        $before = BranchContext::scope(DB::table('pos_categories'))->where('id',$id)->first();
        BranchContext::scope(DB::table('pos_categories'))->where('id',$id)->update(['name'=>$d['name'],'code'=>$d['code'],'updated_at'=>now()]);
        AuditTrail::record('category_updated', 'Updated category '.$d['name'], ['auditable_type'=>'category','auditable_id'=>$id,'properties'=>['before'=>$before ? (array) $before : null,'after'=>$d]]);
        return redirect()->route('pos.admin.categories.index')->with('success','Category Updated');
    }
    public function destroy(int $id): RedirectResponse { $before = BranchContext::scope(DB::table('pos_categories'))->where('id',$id)->first(); BranchContext::scope(DB::table('pos_categories'))->where('id',$id)->delete(); AuditTrail::record('category_deleted', 'Deleted category '.($before->name ?? '#'.$id), ['auditable_type'=>'category','auditable_id'=>$id,'properties'=>['category'=>$before ? (array) $before : null]]); return redirect()->route('pos.admin.categories.index')->with('success','Deleted'); }
}
