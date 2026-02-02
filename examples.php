<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use HolyBible\Bible;
use HolyBible\Books;
use HolyBible\Config\BibleConfig;

echo "=== Holy Bible API - Examples ===\n\n";

// Example 1: Basic usage (backward compatible)
echo "1. Basic Usage:\n";
$bible = new Bible();
try {
    $chapter = $bible->getChapter(Books::JOHN, 3);
    echo "   ✓ Got John chapter 3 with " . count($chapter['verses'] ?? []) . " verses\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Example 2: With custom configuration
echo "\n2. With Configuration:\n";
$config = new BibleConfig([
    'version' => 'nvi',
    'timeout' => 10.0,
    'cache_enabled' => true,
    'cache_ttl' => 7200  // 2 hours
]);
$bible2 = Bible::withConfig($config);
echo "   ✓ Created Bible with custom config\n";

// Example 3: Using DTOs (type-safe)
echo "\n3. Using DTOs (Type-Safe):\n";
$service = $bible->getService();
try {
    $chapterDto = $service->getChapter(Books::PSALMS, 23);
    echo "   ✓ Book: {$chapterDto->book->name}\n";
    echo "   ✓ Chapter: {$chapterDto->number}\n";
    echo "   ✓ Verses: {$chapterDto->getVerseCount()}\n";

    $verse1 = $chapterDto->getVerse(1);
    if ($verse1) {
        echo "   ✓ First verse: " . substr($verse1->text, 0, 50) . "...\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Example 4: Error handling
echo "\n4. Error Handling:\n";
try {
    $bible->getChapter(Books::GENESIS, -1);
} catch (\HolyBible\Exception\InvalidChapterException $e) {
    echo "   ✓ Caught InvalidChapterException: " . $e->getMessage() . "\n";
}

// Example 5: Fluent interface
echo "\n5. Fluent Interface:\n";
$bible3 = new Bible();
$bible3->setVersion('acf')
    ->setTimeout(15.0);
echo "   ✓ Version: {$bible3->getCurrentVersion()}\n";
echo "   ✓ Timeout: {$bible3->getTimeout()}s\n";

// Example 6: Cache demonstration
echo "\n6. Cache Demonstration:\n";
$config2 = new BibleConfig(['cache_enabled' => true]);
$bible4 = Bible::withConfig($config2);

$start = microtime(true);
$bible4->getChapter(Books::MATTHEW, 5);
$time1 = (microtime(true) - $start) * 1000;

$start = microtime(true);
$bible4->getChapter(Books::MATTHEW, 5);  // Cached
$time2 = (microtime(true) - $start) * 1000;

echo "   ✓ First call: " . number_format($time1, 2) . "ms\n";
echo "   ✓ Cached call: " . number_format($time2, 2) . "ms\n";
echo "   ✓ Speed improvement: " . number_format($time1 / $time2, 1) . "x faster\n";

echo "\n=== All examples completed ===\n";
