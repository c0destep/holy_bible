<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Codestep\HolyBible\Bible;

$bible = Bible::getInstance();

try {
    print_r($bible->getVerse());
} catch (Exception $e) {
    die($e->getMessage());
}
