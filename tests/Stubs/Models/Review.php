<?php

namespace Brandonkerr\EloquentFromSettings\Tests\Stubs\Models;

use Brandonkerr\EloquentFromSettings\Tests\Stubs\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
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
        return ReviewFactory::new();
    }

    /**
     * Review belongs to a book
     *
     * @return BelongsTo
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /* NOTE fillable book_id to allow for assignment through FromSettings */
    protected $fillable = ["reviewer", "score", "book_id"];

}
