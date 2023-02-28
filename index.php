<?php declare(strict_types=1);

use HolyBible\Bible;

require_once 'vendor/autoload.php';

var_dump((new Bible())->getAvailableVersions());
