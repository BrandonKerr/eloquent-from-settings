# Eloquent From Settings

[![Packagist Version](https://img.shields.io/packagist/v/BrandonKerr/eloquent-from-settings?label=Release)](https://packagist.org/packages/brandonkerr/eloquent-from-settings)
[![Tests](https://github.com/BrandonKerr/eloquent-from-settings/actions/workflows/test.yml/badge.svg)](https://github.com/BrandonKerr/eloquent-from-settings/actions/workflows/test.yml)
[![Static Analysis](https://github.com/BrandonKerr/eloquent-from-settings/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/BrandonKerr/eloquent-from-settings/actions/workflows/static-analysis.yml)
[![codecov](https://codecov.io/gh/BrandonKerr/eloquent-from-settings/branch/main/graph/badge.svg?token=6IL80QG3LK)](https://codecov.io/gh/BrandonKerr/eloquent-from-settings)

This package allows you to easily build Eloquent models and their relationships, through data settings via array or 
JSON passed to the model's factory.

## Installation

Install the package via composer: 
```bash
composer require brandonkerr/eloquent-from-settings
```

First, ensure that your model has a factory:
```php
namespace App\Models;

// ...
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable 
{
    use HasFactory;
    // ...
}
```

Then simply add the `FromSettings` trait to your factory:
```php
namespace Database\Factories;

// ...
use Brandonkerr\EloquentFromSettings\Traits\FromSettings;

class UserFactory extends Factory
{
    use FromSettings;
    // ...
}
```

## Usage

Simply pass the desired settings via array to the factor's `fromSettingsArray` function:
```php

$data = [
        "name" => "Brandon Kerr",
        "books" => [
            [
                "title" => "How to Use Eloquent From Settings",
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
        ],
    ];
Author::factory()->fromSettingsArray(...$data)->create();
```
or via JSON to the factor's `fromSettingsJson` function:
```php
$json = '{
   "name":"Brandon Kerr",
   "books":[
      {
         "title":"How to Use Eloquent From Settings",
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
      }
   ]
}';
Author::factory()->fromSettingsJson($json)->create();
```
The end result will be 
- an Author model with name _Brandon Kerr_
- a Book model whose Author is created above and title is _How to Use Eloquent From Settings_
- a Review for the Book created above with reviewer _Jane Doe_ and a score of _80_
- a Review for the Book created above with reviewer _John Smith_ and a score of _45_

## Full Example
This example covers Authors writing Books, which have Reviews. Full details can be found in the tests directory of this 
package.
### Models and Schemas
Refer to the Models and Migrations directories under tests/Stubs for full details.
#### Author
| Column/Attribute | Type            | Notes                                                     |
|------------------|-----------------|-----------------------------------------------------------|
| id               | unsigned bigint | Primary Key                                               |
| name             | string          | unique constraint added for use in a custom function test |
- an Author `HasMany` Books
- an Author `HasManyThrough` Reviews (through Books)
#### Book
| Column/Attribute | Type             | Notes                 |
|------------------|------------------|-----------------------|
| id               | unsigned bigint  | Primary Key           |
| title            | string           | title of the book     |
| author_id        | unsigned bigint  | Foreign Key to Author |
- a Book `BelongsTo` an Author 
- a Book `HasMany` Reviews
#### Review
| Column/Attribute | Type            | Notes                  |
|------------------|-----------------|------------------------|
| id               | unsigned bigint | Primary Key            |
| reviewer         | string          | name of the reviewer   |
| score            | integer         | the score (out of 100) |
| book_id          | unsigned bigint | Foreign Key to Book    |
- a Review `BelongsTo` a Book

### Factories
The `AuthorFactory` and `ReviewFactory` classes are completely standard (aside from using the `FromSettings` trait), but 
the `BookFactory` has two custom functions to showcase additional functionality of this package:
```php
 /**
 * Custom function to find or create the author, based on the given name
 *
 * @param string $name
 * @return $this
 */
public function forAuthor(string $name): self
{
    $author = Author::firstOrCreate([
        "name" => $name
    ]);

    return $this->state(["author_id" => $author->id]);
}

/**
 * Custom function to add reviews with a perfect score
 *
 * @param string ...$names
 * @return $this
 */
public function perfectReviews(string ...$names): self
{
    return $this->has(
        Review::factory()
            ->count(count($names))
            ->sequence(fn (Sequence $sequence) => [
                "reviewer" => $names[$sequence->index],
                "score" => 100,
            ])
    );
}
```

### Settings Data
Below is a commented example of an array of settings, where the author is the root of the data:
```php
$data = [
        // the author's name attribute
        "name" => "Bob",
        // the author's HasMany relationship with Book
        "books" => [
            // first book
            [  
                // the book's title attribute
                "title" => "Bob's First Book",
                // the book's HasMany relationship with Review
                "reviews" => [
                    // first review
                    [
                        // the review's reviewer attribute
                        "reviewer" => "Jane Doe",
                        // the review's score attribute
                        "score" => 80,
                    ],
                    // second review
                    [
                        // the review's reviewer attribute
                        "reviewer" => "John Smith",
                        // the review's score attribute
                        "score" => 45,
                    ],
                ],
            ],
            // second book
            [
                // the book's title attribute
                "title" => "Please Don't Review Me",
                // NOTE no reviews 
            ],
        ],
    ];
```
Then we simply call the Author's factory:
```php
$author = Author::factory()->fromSettingsArray(...$data)->create();
```
The end result is 
- One Author with name _Bob_, which has two books:
  - The first book is titled _Bob's First Book_ and has two reviews:
    - one with reviewer _Jane Doe_ and a score of _80_, and 
    - one with reviewer _John Smith_ and a score of _45_.
  - The second book is titled _Please Don't Review Me_ and has zero reviews.

#### BelongsTo
We aren't limited to Has__ relationships, like in the example above. We can also use a Book as the data root, and create 
an author for it:
```php
$data = [
    "title" => "My Book",
    "author" => [
        "name" => "Bob Jones",
    ],
];
Book::factory()->fromSettingsArray(...$data)->create();
```
The end result is one Author named _Bob Jones_, which has one Book titled _My Book_.

#### Custom Functions
But what if we don't want to create a new author, and don't know the author's ID that we could use to set the book's 
author_id value? You could create a custom function on the Book's factory that can find the author by their name, and 
assign the author to the book. See the `forAuthor()` function above for details.
```php
Author::create([
    "name" => "Bob Jones"
]);
// ...
$data = [
    "title" => "My Book",
    "forAuthor" => [
        "name" => "Bob Jones",
    ],
];

$book = Book::factory()->fromSettingsArray(...$data)->create();
```
The end result is again one Author named _Bob Jones_, which has one Book titled _My Book_, but since the Author named
_Bob Jones_ already existed, it would not be created again, and it would not violate our unique name constraint on the 
Authors table.

We can also use a custom function to accept non key-value pair settings. See the `perfectReviews()` function above for 
details.
```php
$data = [
    "title" => "My Book",
    "author" => [
        "name" => "Bob Jones",
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
```
The end result is once again one Author named _Bob Jones_, which has one Book titled _My Book_. However, this time the 
Book has two Reviews: one with reviewer _Jane Doe_, and the other with reviewer _John Smith_, and both with a score of 
_100_ (as set by the `perfectReviews` function).

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
