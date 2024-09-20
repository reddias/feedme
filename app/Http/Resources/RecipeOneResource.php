<?php

namespace App\Http\Resources;

use App\Models\Comment;
use App\Models\Like;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class RecipeOneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $recipeId = $this->id;
        $cacheDuration = now()->addMinutes(10);

        $likesCacheKey = 'recipe_' . $recipeId . '_likes_count';
        $likesCount = Cache::remember($likesCacheKey, $cacheDuration, function () use ($recipeId) {
            return Like::query()->where('recipe_id', $recipeId)->count();
        });

        $commentsCacheKey = 'recipe_' . $recipeId . '_comments_count';
        $commentsCount = Cache::remember($commentsCacheKey, $cacheDuration, function () use ($recipeId) {
            return Comment::query()->where('recipe_id', $recipeId)->count();
        });

        $ingredientsCacheKey = 'recipe_' . $recipeId . '_ingredients';
        $ingredients = Cache::remember($ingredientsCacheKey, $cacheDuration, function () {
            return IngredientResource::collection($this->whenLoaded('ingredients'));
        });


        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'photo_url' => $this->photo_url,
            'view_count' => $this->view_count ?? 0,
            'cooking_time' => $this->cooking_time ?? 0,
            'user' => new UserResource($this->whenLoaded('user')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'instructions' => $this->instructions,
            'ingredients' => $ingredients,
            'likes_count' => $likesCount,
            'comments_count' => $commentsCount,
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDateTimeString()
        ];
    }
}
