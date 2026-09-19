<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FAQController extends Controller
{
    // Display FAQs on frontend (public - no auth needed)
    public function index()
    {
        $generalFaqs = FAQ::active()->byCategory('general')->ordered()->get();
        $servicesFaqs = FAQ::active()->byCategory('services')->ordered()->get();
        
        return view('Frontend.faqs', compact('generalFaqs', 'servicesFaqs'));
    }

    // Admin: List all FAQs (with admin check)
    public function adminIndex()
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->is_admin != 1) {
            return redirect()->route('home_page')->with('error', 'Unauthorized access.');
        }

        $faqs = FAQ::orderBy('category')->orderBy('order')->get();
        return view('backend.faqs.index', compact('faqs'));
    }

    // Admin: Show create form
    public function create()
    {
        if (!Auth::check() || Auth::user()->is_admin != 1) {
            return redirect()->route('home_page')->with('error', 'Unauthorized access.');
        }

        return view('backend.faqs.create');
    }

    // Admin: Store new FAQ
    public function store(Request $request)
    {
        if (!Auth::check() || Auth::user()->is_admin != 1) {
            return redirect()->route('home_page')->with('error', 'Unauthorized access.');
        }

        $validator = Validator::make($request->all(), [
            'category' => 'required|in:general,services',
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        FAQ::create([
            'category' => $request->category,
            'question' => $request->question,
            'answer' => $request->answer,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ created successfully.');
    }

    // Admin: Show edit form
    public function edit($id)
    {
        if (!Auth::check() || Auth::user()->is_admin != 1) {
            return redirect()->route('home_page')->with('error', 'Unauthorized access.');
        }

        $faq = FAQ::findOrFail($id);
        return view('backend.faqs.edit', compact('faq'));
    }

    // Admin: Update FAQ
    public function update(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()->is_admin != 1) {
            return redirect()->route('home_page')->with('error', 'Unauthorized access.');
        }

        $faq = FAQ::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category' => 'required|in:general,services',
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $faq->update([
            'category' => $request->category,
            'question' => $request->question,
            'answer' => $request->answer,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ updated successfully.');
    }

    // Admin: Delete FAQ
    public function destroy($id)
    {
        if (!Auth::check() || Auth::user()->is_admin != 1) {
            return redirect()->route('home_page')->with('error', 'Unauthorized access.');
        }

        $faq = FAQ::findOrFail($id);
        $faq->delete();

        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ deleted successfully.');
    }

    // Admin: Toggle active status
    public function toggleActive($id)
    {
        if (!Auth::check() || Auth::user()->is_admin != 1) {
            return redirect()->route('home_page')->with('error', 'Unauthorized access.');
        }

        $faq = FAQ::findOrFail($id);
        $faq->is_active = !$faq->is_active;
        $faq->save();

        return redirect()->back()
            ->with('success', 'FAQ status updated successfully.');
    }
}
