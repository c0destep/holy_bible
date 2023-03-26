<?php declare(strict_types=1);

use HolyBible\Bible;
use HolyBible\Books;

require_once 'vendor/autoload.php';

$bible = new Bible();

print_r($bible->getChapter(Books::FIRST_CORINTHIANS, 2));
