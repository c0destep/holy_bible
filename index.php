<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Codestep\HolyBible\Bible;
use Codestep\HolyBible\Books;

$bible = Bible::getInstance();

try {
    print_r($bible->getVerse(Books::GENESIS, 1, 5));
} catch (Exception $e) {
    die($e->getMessage());
}
