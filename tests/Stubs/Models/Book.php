<?php

namespace Brandonkerr\EloquentFromSettings\Tests\Stubs\Models;

use Brandonkerr\EloquentFromSettings\Tests\Stubs\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        // specify the factory to handle our stub namespace
        return BookFactory::new();
    }

    /**
     * Book belongs to an author
     *
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * Book has many reviews
     *
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /* NOTE fillable author_id to allow for assignment through FromSettings */
    protected $fillable = ["title", "author_id"];
}
