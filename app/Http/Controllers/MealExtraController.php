<?php

namespace App\Http\Controllers;

use App\Models\MealExtra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MealExtraController extends Controller
{
    public function index(): View
    {
        $extras = MealExtra::orderBy('sort_order')->orderBy('name')->get();

        return view('backend.meal_extras.index', compact('extras'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request);
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['icon'] = $data['icon'] ?: 'bi-basket2';
        MealExtra::create($data);

        return redirect()->route('meal_extras.index')->with('success', 'Add-on added successfully.');
    }

    public function edit(MealExtra $mealExtra): View
    {
        $extras = MealExtra::orderBy('sort_order')->orderBy('name')->get();

        return view('backend.meal_extras.index', compact('extras', 'mealExtra'));
    }

    public function update(Request $request, MealExtra $mealExtra): RedirectResponse
    {
        $data = $this->validatedData($request, $mealExtra->id);

        if ($request->hasFile('image')) {
            $this->deleteImage($mealExtra->image);
            $data['image'] = $this->storeImage($request);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['icon'] = $data['icon'] ?: 'bi-basket2';
        $mealExtra->update($data);

        return redirect()->route('meal_extras.index')->with('success', 'Add-on updated successfully.');
    }

    public function destroy(MealExtra $mealExtra): RedirectResponse
    {
        $this->deleteImage($mealExtra->image);
        $mealExtra->delete();

        return redirect()->route('meal_extras.index')->with('success', 'Add-on deleted successfully.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:meal_extras,name';
        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:15000'],
            'icon' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function storeImage(Request $request): string
    {
        $directory = public_path('assets/admin/img/meal-extras');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $request->file('image');
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $fileName);

        return 'assets/admin/img/meal-extras/' . $fileName;
    }

    private function deleteImage(?string $path): void
    {
        $path = ltrim((string) $path, '/');
        if ($path === '') {
            return;
        }

        $fullPath = public_path($path);
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}

