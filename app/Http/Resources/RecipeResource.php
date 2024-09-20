<?php

namespace App\Http\Resources;

use App\Models\Comment;
use App\Models\Like;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
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
            'ingredients' => IngredientResource::collection($this->whenLoaded('ingredients')),
            'likes_count' => Like::query()->where('recipe_id', $this->id)->count(),
            'comments_count' => Comment::query()->where('recipe_id', $this->id)->count(),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDateTimeString()
        ];
    }
}
