<?php

namespace Tests;

use HolyBible\Bible;
use PHPUnit\Framework\TestCase;

class BibleTest extends TestCase
{
    public function testGetBooks(): void
    {
        $bible = new Bible();
        $books = $bible->getBooks();

        $this->assertIsArray($books, 'Error return not array');

        $this->assertArrayHasKey('error', $books, $books['error']);
    }
}
