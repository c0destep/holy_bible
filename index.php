<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Codestep\HolyBible\Bible;
use Codestep\HolyBible\Books;

$bible = Bible::getInstance();

try {
    print_r($bible->getChapter(Books::FIRST_CORINTHIANS, 2));
} catch (Exception $e) {
    die($e->getMessage());
}
