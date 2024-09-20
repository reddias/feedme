<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{

    /**
     * Like or unlike a recipe.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function like(Request $request): JsonResponse
    {
        $request->validate([
            'recipe_id' => 'required|integer|exists:recipes,id',
        ]);

        $userId = auth()->id();
        $recipe = Recipe::query()->findOrFail($request->recipe_id);
        $existingLike = $recipe->likes()->where('user_id', $userId);

        if (!$existingLike->exists()) {
            $existingLike = $recipe->likes()->create([
                'user_id' => $userId,
            ]);
        } else {
            $existingLike->delete();
        }

        return response()->json($existingLike);
    }
}
