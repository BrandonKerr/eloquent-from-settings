<?php

namespace Brandonkerr\EloquentFromSettings\Tests;

use Brandonkerr\EloquentFromSettings\Tests\Stubs\Exceptions\MissingTraitException;
use Brandonkerr\EloquentFromSettings\Tests\Stubs\Models\Author;
use Brandonkerr\EloquentFromSettings\Tests\Stubs\Models\Book;
use Brandonkerr\EloquentFromSettings\Traits\FromSettings;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FromSettingsTest extends PackageTestCase
{
    /**
     * Ensure the author and all relationships are created from the settings data
     *
     * @return void
     * @test
     */
    public function fullTest(): void
    {
        $data = [
            "name" => "Bob",
            "books" => [
                [
                    "title" => "Bob's First Book",
                    // foo is not an attribute nor relationship and will be thrown away
                    "foo" => "Bar",
                    "reviews" => [
                        [
                            "reviewer" => "Jane Doe",
                            "score" => 80,
                        ],
                        [
                            "reviewer" => "John Smith",
                            "score" => 45,
                        ],
                    ],
                ],
                [
                    "title" => "Please Don't Review Me",
                ],
            ],
        ];

        $author = Author::factory()->fromSettingsArray(...$data)->create();

        $this->assertDatabaseCount("authors", 1);
        $this->assertDatabaseCount("books", 2);
        $this->assertDatabaseCount("reviews", 2);

        $this->assertSame("Bob", $author->name);

        $author->loadMissing(["books", "reviews"]);

        $this->assertSame("Bob's First Book", $author->books->first()->title);
        $this->assertSame("Please Don't Review Me", $author->books->last()->title);

        $this->assertCount(2, $author->books->first()->reviews);
        $this->assertSame("Jane Doe", $author->books->first()->reviews->first()->reviewer);
        $this->assertSame(80, $author->books->first()->reviews->first()->score);
        $this->assertSame("John Smith", $author->books->first()->reviews->last()->reviewer);
        $this->assertSame(45, $author->books->first()->reviews->last()->score);

        $this->assertEmpty($author->books->last()->reviews);
    }

    /**
     * Ensure the author and all relationships are created from the settings data JSON
     *
     * @return void
     * @test
     */
    public function fullTestFromJson(): void
    {
        $json = '{
           "name":"Bob",
           "books":[
              {
                 "title":"Bob\'s First Book",
                 "foo":"Bar",
                 "reviews":[
                    {
                       "reviewer":"Jane Doe",
                       "score":80
                    },
                    {
                       "reviewer":"John Smith",
                       "score":45
                    }
                 ]
              },
              {
                 "title":"Please Don\'t Review Me"
              }
           ]
        }';
        $author = Author::factory()->fromSettingsJson($json)->create();

        $this->assertDatabaseCount("authors", 1);
        $this->assertDatabaseCount("books", 2);
        $this->assertDatabaseCount("reviews", 2);

        $this->assertSame("Bob", $author->name);

        $author->loadMissing(["books", "reviews"]);

        $this->assertSame("Bob's First Book", $author->books->first()->title);
        $this->assertSame("Please Don't Review Me", $author->books->last()->title);

        $this->assertCount(2, $author->books->first()->reviews);
        $this->assertSame("Jane Doe", $author->books->first()->reviews->first()->reviewer);
        $this->assertSame(80, $author->books->first()->reviews->first()->score);
        $this->assertSame("John Smith", $author->books->first()->reviews->last()->reviewer);
        $this->assertSame(45, $author->books->first()->reviews->last()->score);

        $this->assertEmpty($author->books->last()->reviews);
    }

    /**
     * Ensure the book and all relationships are created from the settings data,
     * connecting to the existing author
     *
     * @return void
     * @test
     */
    public function canAddNewBookToExistingAuthor(): void
    {

        $author = Author::factory()->create();
        $author->load(["books", "reviews"]);


        $this->assertEmpty($author->books);
        $this->assertEmpty($author->reviews);

        // NOTE: the author_id value must be fillable to use an existing author
        $data1 = [
            "author_id" => $author->id,
            "title" => "My Book",
            "reviews" => [
                [
                    "reviewer" => "Jane Doe",
                    "score" => 80,
                ],
                [
                    "reviewer" => "John Smith",
                    "score" => 45,
                ],
            ],
        ];
        $data2 = [
            "author_id" => $author->id,
            "title" => "My Unreviewed Book",
        ];

        Book::factory()->fromSettingsArray(...$data1)->create();
        Book::factory()->fromSettingsArray(...$data2)->create();

        $author->load(["books", "reviews"]);

        $this->assertSame("My Book", $author->books->first()->title);
        $this->assertSame("My Unreviewed Book", $author->books->last()->title);

        $this->assertCount(2, $author->books->first()->reviews);
        $this->assertSame("Jane Doe", $author->books->first()->reviews->first()->reviewer);
        $this->assertSame(80, $author->books->first()->reviews->first()->score);
        $this->assertSame("John Smith", $author->books->first()->reviews->last()->reviewer);
        $this->assertSame(45, $author->books->first()->reviews->last()->score);

        $this->assertEmpty($author->books->last()->reviews);
    }

    /**
     * Ensure the book and all relationships are created from the settings data,
     * creating a new author
     *
     * @return void
     * @test
     */
    public function canCreateAuthorFromBook(): void
    {
        $data = [
            "title" => "My Book",
            "author" => [
                "name" => "Bob Jones",
            ],
            "reviews" => [
                [
                    "reviewer" => "Jane Doe",
                    "score" => 80,
                ],
                [
                    "reviewer" => "John Smith",
                    "score" => 45,
                ],
            ],
        ];

        Book::factory()->fromSettingsArray(...$data)->create();

        $this->assertDatabaseCount("authors", 1);
        $this->assertDatabaseCount("books", 1);
        $this->assertDatabaseCount("reviews", 2);

        $author = Author::first()->load(["books", "reviews"]);

        $this->assertSame("Bob Jones", $author->name);

        $this->assertSame("My Book", $author->books->first()->title);

        $this->assertCount(2, $author->books->first()->reviews);
        $this->assertSame("Jane Doe", $author->books->first()->reviews->first()->reviewer);
        $this->assertSame(80, $author->books->first()->reviews->first()->score);
        $this->assertSame("John Smith", $author->books->first()->reviews->last()->reviewer);
        $this->assertSame(45, $author->books->first()->reviews->last()->score);
    }

    /**
     * Ensure the book and all relationships are created from the settings data,
     * using the custom function to find and link the existing author
     *
     * @return void
     * @test
     */
    public function canCreateBookWithFoundAuthor(): void
    {
        $author = Author::factory()->create();
        $this->assertDatabaseCount("authors", 1);

        $data = [
            "title" => "My Book",
            "forAuthor" => [
                "name" => $author->name,
            ],
            "reviews" => [
                [
                    "reviewer" => "Jane Doe",
                    "score" => 80,
                ],
                [
                    "reviewer" => "John Smith",
                    "score" => 45,
                ],
            ],
        ];

        $book = Book::factory()->fromSettingsArray(...$data)->create();

        $this->assertDatabaseCount("authors", 1);
        $this->assertDatabaseCount("books", 1);
        $this->assertDatabaseCount("reviews", 2);


        $book->load(["author", "reviews"]);

        $this->assertSame($author->name, $book->author->name);

        $this->assertSame("My Book", $book->title);

        $this->assertCount(2, $book->reviews);
        $this->assertSame("Jane Doe", $book->reviews->first()->reviewer);
        $this->assertSame(80, $book->reviews->first()->score);
        $this->assertSame("John Smith", $book->reviews->last()->reviewer);
        $this->assertSame(45, $book->reviews->last()->score);
    }

    /**
     * Ensure the book and all relationships are created from the settings data,
     * using nested array values which are not key-value arrays inside
     *
     * @return void
     * @test
     */
    public function canUsedNestedArrayValuesWithoutKeyValue(): void
    {
        $data = [
            "title" => "My Book",
            "author" => [
                "name" => "Book Writer",
            ],
            "perfectReviews" => [
                [
                    "Jane Doe",
                ],
                [
                    "John Smith",
                ],
            ],
        ];

        $book = Book::factory()->fromSettingsArray(...$data)->create();

        $this->assertDatabaseCount("authors", 1);
        $this->assertDatabaseCount("books", 1);
        $this->assertDatabaseCount("reviews", 2);


        $book->load(["author", "reviews"]);

        $this->assertSame("Book Writer", $book->author->name);

        $this->assertSame("My Book", $book->title);

        $this->assertCount(2, $book->reviews);
        $this->assertSame("Jane Doe", $book->reviews->first()->reviewer);
        $this->assertSame(100, $book->reviews->first()->score);
        $this->assertSame("John Smith", $book->reviews->last()->reviewer);
        $this->assertSame(100, $book->reviews->last()->score);
    }

    /**
     * Ensure that a MissingTraitException exception is thrown if a relationship's factory does not
     * use the FromSettings trait
     *
     * @return void
     * @test
     */
    public function throwsExceptionWhenNestedRelationshipDoesNotUseTrait(): void
    {
        $data = [
            "name" => "Foo",
            "bar" => [
                "name" => "Bar",
            ],
        ];

        $this->expectException(MissingTraitException::class);
        Foo::factory()->fromSettingsArray(...$data)->create();
    }
}

class Foo extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return FooFactory
     */
    protected static function newFactory(): FooFactory
    {
        return FooFactory::new();
    }

    public function bar(): HasOne
    {
        return $this->hasOne(Bar::class);
    }
}

class FooFactory extends Factory
{
    use FromSettings;

    protected $model = Foo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, string>
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->name,
        ];
    }
}

class Bar extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return BarFactory
     */
    protected static function newFactory(): BarFactory
    {
        return BarFactory::new();
    }

}

class BarFactory extends Factory
{
    // Bar Factory DOES NOT use FromSettings

    protected $model = Bar::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, string>
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->name
        ];
    }
}
