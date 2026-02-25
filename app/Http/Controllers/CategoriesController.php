<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $categories = Category::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Category/AdminCategory', [
            'categories' => $categories,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Category/CreateCategory');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            // 'offers_level' => "nullable|boolean",
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            // 'offers_level' => $validated['offers_level'] ? $request->boolean('offers_level') : false,
        ]);


        return redirect()->route('admin.categories')->with('success', 'A category has been created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Categories $categories)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $categories = Category::findOrFail($id);

        return Inertia::render('Category/EditCategory', [
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'offers_level'  => "nullable|boolean",
        ]);

        $category = $category->update([
            'name' => $validated['name'],
            'offers_level' => $validated['offers_level'] ? $request->boolean('offers_level') : false,
        ]);

        return redirect()->route('admin.categories')->with('success', 'Office Rates updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories')->with('success', 'A category has been deleted successfully.');
    }
}
