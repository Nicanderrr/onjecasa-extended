<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductPage;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return view('backend.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => trim($validated['name']),
        ]);

        return redirect()->back()->with('success', 'Category added successfully.');
    }

    public function destroy(Category $category)
    {
        $isUsed = ProductPage::where('description', $category->name)->exists();

        if ($isUsed) {
            return redirect()->back()->with('error', 'This category is assigned to products and cannot be deleted.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
