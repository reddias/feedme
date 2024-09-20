<?php

namespace App\Http\Controllers;

use App\Http\Requests\DecodeInstructionsRequest;
use App\Http\Resources\RecipeOneResource;
use App\Http\Resources\RecipeResource;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class RecipeController extends Controller
{
    private FileUploadService $fileUploadService;

    public function __construct()
    {
        $this->fileUploadService = new FileUploadService();
    }

    /**
     * Display a paginated listing of recipes.
     *
     * @return JsonResponse
     */
    public function index(): AnonymousResourceCollection
    {
        return RecipeResource::collection(
            Recipe::query()
                ->when(request('search'), function ($query) {
                    $query->search(request('search'))
                        ->orderBy('relevance', 'DESC');
                })->paginate()
        );
    }

    /**
     * Display the specified recipe with its likes.
     *
     * @param int $id
     * @return RecipeOneResource
     */
    public function show(int $id): RecipeOneResource
    {
        $recipe = Recipe::query()->with(['comments', 'category', 'user', 'ingredients'])->findOrFail($id);
        $recipe->increment('view_count');
        return RecipeOneResource::make($recipe);
    }

    /**
     * Delete the specified recipe if the authenticated user is the owner or an admin.
     *
     * @param int $id
     * @return RecipeResource
     */
    public function destroy(int $id): RecipeResource
    {
        $recipe = Recipe::query()->findOrFail($id);
        $user = auth()->user();

        if ($user->id == $recipe->user_id || $user->is_admin) {
            $recipe->delete();
        }

        return RecipeResource::make($recipe);
    }

    /**
     * Create a new recipe with the provided details.
     *
     * @param DecodeInstructionsRequest $request
     * @return RecipeResource
     */
    public function create(DecodeInstructionsRequest $request): RecipeResource
    {
        $request['user_id'] = auth()->id();

        $recipe = Recipe::query()->create($request->all());

        foreach ($request->input('ingredients') as $ingredient_new) {
            $ingredient = Ingredient::query()->firstOrCreate(['name' => $ingredient_new['name']]);
            $recipe->ingredients()->attach($ingredient->id, ['measurement' => $ingredient_new['measurement']]);
        }

        if ($request['photo']) {
            $fileData = $this->fileUploadService->processFile($request['photo'], "photos/recipes/$recipe->id");
            $recipe->photo_url = $fileData['fileUrl'];
            $recipe->save();
        }
        return RecipeResource::make($recipe);
    }

    /**
     * Update the specified recipe with new details.
     *
     * @param DecodeInstructionsRequest $request
     * @param int $id
     * @return RecipeResource
     */
    public function update(DecodeInstructionsRequest $request, int $id): RecipeResource
    {
        $userId = auth()->id();
        $recipe = Recipe::query()->where('user_id', $userId)->findOrFail($id);
        $recipe->update($request->all());

        $ingredients = [];
        foreach ($request->input('ingredients') as $ingredientNew) {
            $ingredient = Ingredient::query()->firstOrCreate(['name' => $ingredientNew['name']]);
            $ingredients[$ingredient->id] = ['measurement' => $ingredientNew['measurement']];
        }

        $recipe->ingredients()->sync($ingredients);

        return RecipeResource::make($recipe);
    }

    /**
     * Update the photo of the specified recipe if the authenticated user is the owner.
     *
     * @param Request $request
     * @param int $id
     * @return RecipeResource
     */
    public function updatePhoto(Request $request, int $id): RecipeResource
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $userId = auth()->id();
        $recipe = Recipe::query()->where('user_id', $userId)->findOrFail($id);

        $recipe = $this->fileUploadService->updateImage($recipe, $request->file('photo'), "photos/recipes/$recipe->id");

        return RecipeResource::make($recipe);
    }

    /**
     * Delete the photo of the specified recipe if the authenticated user is the owner.
     *
     * @param int $id
     * @return RecipeResource
     */
    public function deletePhoto(int $id): RecipeResource
    {
        $userId = auth()->id();
        $recipe = Recipe::query()->where('user_id', $userId)->findOrFail($id);

        $recipe = $this->fileUploadService->deleteImage($recipe);

        return RecipeResource::make($recipe);
    }

    /**
     * Clone the specified recipe and assign it to the authenticated user.
     *
     * @param int $id
     * @return RecipeResource
     */
    public function cloneRecipe(int $id): RecipeResource
    {
        $originalRecipe = Recipe::query()->findOrFail($id);
        $userId = auth()->id();

        $clonedRecipe = $originalRecipe->replicate();
        $clonedRecipe->user_id = $userId;
        $clonedRecipe->save();

        foreach ($originalRecipe->ingredients as $ingredient) {
            $clonedRecipe->ingredients()->attach($ingredient->id, ['measurement' => $ingredient->pivot->measurement]);
        }

        return RecipeResource::make($clonedRecipe);
    }


    /**
     * Get popular recipes.
     *
     * @return AnonymousResourceCollection
     */
    public function popularRecipes(): AnonymousResourceCollection
    {
        $cacheKey = 'popular_recipes';

        $recipes = Cache::remember($cacheKey, now()->addMinutes(10), function () {
            return Recipe::query()
                ->orderByDesc('view_count')
                ->take(10)
                ->get();
        });

        return RecipeResource::collection($recipes);
    }
}
