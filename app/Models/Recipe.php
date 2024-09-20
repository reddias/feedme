<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nicolaslopezj\Searchable\SearchableTrait;

class Recipe extends Model
{
    use HasFactory, SearchableTrait;


    /**
     * The table associated with the model.
     *
     * @var string
     * */
    protected $table = 'recipes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'photo_url',
        'view_count',
        'cooking_time',
        'user_id',
        'category_id',
        'instructions',
    ];

    /**
     * Columns over which search can be done
     *
     * @var array|array[]
     */
    protected array $searchable = [
        'columns' => [
            'title' => 10,
            'description' => 5,
            'cooking_time' => 5,
        ],
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'view_count' => 'integer',
        'cooking_time' => 'integer',
        'user_id' => 'integer',
        'category_id' => 'integer',
        'instructions' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Get the comments for the recipe.
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'recipe_id');
    }

    /**
     * Get the likes for the recipe.
     *
     * @return HasMany
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'recipe_id');
    }

    /**
     * Get the user that owns the recipe.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that the recipe belongs to.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)
            ->using(IngredientRecipe::class)
            ->withPivot('measurement')
            ->withTimestamps();
    }
}
