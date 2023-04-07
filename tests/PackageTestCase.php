<?php

namespace Brandonkerr\EloquentFromSettings\Tests;

use Brandonkerr\EloquentFromSettings\Tests\Stubs\Migrations\CreateAuthorsTable;
use Brandonkerr\EloquentFromSettings\Tests\Stubs\Migrations\CreateBooksTable;
use Brandonkerr\EloquentFromSettings\Tests\Stubs\Migrations\CreateReviewsTable;
use Orchestra\Testbench\TestCase;

class PackageTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // set up each model's table
        (new CreateAuthorsTable())->up();
        (new CreateBooksTable())->up();
        (new CreateReviewsTable())->up();
    }
}
