<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{

    /**
     * Display a paginated listing of categories.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $page = request('page', 1);
        $search = request('search', '');
        $cacheKey = 'categories_page_' . $page . '_search_' . $search;

        $categories = Cache::remember($cacheKey, 60 * 24, function () use ($search) {
            return Category::query()
                ->when($search, function ($query) use ($search) {
                    $query->search($search)
                        ->orderBy('relevance', 'DESC');
                })->paginate();
        });

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created category in the database.
     *
     * @param Request $request
     * @return CategoryResource
     */
    public function create(Request $request): CategoryResource
    {
        $request->validate([
            'name' => 'required|string',
        ]);
        $category = Category::query()->create($request->all());
        return CategoryResource::make($category);
    }

    /**
     * Display the specified category.
     *
     * @param int $id
     * @return CategoryResource
     */
    public function show(int $id): CategoryResource
    {
        $cacheKey = 'category_' . $id;

        $category = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($id) {
            return Category::query()->find($id);
        });

        return CategoryResource::make($category);
    }

    /**
     * Remove the specified category from the database.
     *
     * @param int $id
     * @return CategoryResource
     */
    public function destroy(int $id): CategoryResource
    {
        $category = Category::query()->findOrFail($id);
        $category->delete();

        return CategoryResource::make($category);
    }
}
