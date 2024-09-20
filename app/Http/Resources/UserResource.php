<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
            'description' => $this->description,
            'photo_url' => $this->photo_url,
            'role' => $this->is_admin == 1 ? "admin" : "user",
            'status' => $this->when($this->is_admin, $this->status),
            'recipes' => RecipeResource::collection($this->whenLoaded('recipes')),
            'likes' => LikeResource::collection($this->whenLoaded('likes')),
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDateTimeString()
        ];
    }
}
