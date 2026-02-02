# Holy Bible API - PHP Library

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg)](https://phpstan.org/)

A modern and robust PHP library to access the Digital Bible API with support for caching, retry logic, logging, and typed DTOs.

## ✨ Features

- 🚀 **Smart Cache** - Up to 400x faster with automatic caching
- 🔄 **Auto Retry** - Exponential backoff for temporary failures
- 📝 **PSR-3 Logging** - Full logging for debugging
- 🎯 **Type-Safe** - DTOs with readonly properties (PHP 8.1+)
- 🏗️ **Layered Architecture** - Client/Service/Facade
- ⚙️ **Flexible Configuration** - Arrays, environment variables, or objects
- ✅ **100% Tested** - 17 unit tests with mocks
- 🔒 **PHPStan Level 8** - Rigorous static analysis
- 🔙 **Backward Compatible** - Works with existing code

## 📦 Installation

```bash
composer require c0destep/holy_bible
```

## 🚀 Quick Usage

### Basic (Backward Compatible)

```php
use HolyBible\Bible;
use HolyBible\Books;

$bible = new Bible();

// Get chapter
$chapter = $bible->getChapter(Books::JOHN, 3);
foreach ($chapter['verses'] as $verse) {
    echo "{$verse['number']}. {$verse['text']}\n";
}

// Get specific verse
$verse = $bible->getVerse(Books::JOHN, 3, 16);
echo $verse['text'];

// List all books
$books = $bible->getBooks();

// List available versions
$versions = $bible->getAvailableVersions();
```

### With Advanced Configuration

```php
use HolyBible\Bible;
use HolyBible\Config\BibleConfig;
use HolyBible\Retry\RetryPolicy;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create logger
$logger = new Logger('bible');
$logger->pushHandler(new StreamHandler('bible.log', Logger::DEBUG));

// Configure
$config = new BibleConfig([
    'version' => 'nvi',
    'timeout' => 10.0,
    'cache_enabled' => true,
    'cache_ttl' => 7200,  // 2 hours
    'retry_policy' => RetryPolicy::aggressive(),
    'logger' => $logger
]);

$bible = Bible::withConfig($config);
```

### Using DTOs (Type-Safe)

```php
$service = $bible->getService();

// Returns ChapterDTO
$chapter = $service->getChapter(Books::PSALMS, 23);

echo "Book: {$chapter->book->name}\n";
echo "Chapter: {$chapter->number}\n";
echo "Verses: {$chapter->getVerseCount()}\n";

foreach ($chapter->verses as $verse) {
    echo "{$verse->number}. {$verse->text}\n";
}

// Fetch specific verse
$verse1 = $chapter->getVerse(1);
if ($verse1) {
    echo $verse1->text;
}
```

### Error Handling

```php
use HolyBible\Exception\{
    InvalidChapterException,
    InvalidVerseException,
    NetworkException,
    ApiResponseException
};

try {
    $chapter = $bible->getChapter(Books::GENESIS, 1);
    
} catch (InvalidChapterException $e) {
    // Invalid input (chapter < 1)
    echo "Invalid chapter: " . $e->getMessage();
    
} catch (InvalidVerseException $e) {
    // Invalid input (verse < 1)
    echo "Invalid verse: " . $e->getMessage();
    
} catch (NetworkException $e) {
    // Network error/timeout
    echo "Connection error: " . $e->getMessage();
    // Retry was already attempted automatically
    
} catch (ApiResponseException $e) {
    // API Error (Invalid JSON, etc)
    echo "API Error: " . $e->getMessage();
}
```

## ⚙️ Configuration

### Via Array

```php
$config = new BibleConfig([
    'version' => 'acf',              // Bible version
    'user_token' => 'your-token',    // Auth token (optional)
    'timeout' => 15.0,               // Timeout in seconds
    'cache_enabled' => true,         // Enable cache
    'cache_ttl' => 3600,             // Cache TTL in seconds
    'cache_dir' => '/custom/path',   // Cache directory (optional)
    'retry_enabled' => true,         // Enable retry
    'retry_policy' => RetryPolicy::default(),  // Retry policy
    'logger' => $myLogger            // PSR-3 logger (optional)
]);
```

### Via Environment Variables

```bash
export BIBLE_VERSION=nvi
export BIBLE_USER_TOKEN=abc123
export BIBLE_TIMEOUT=10.0
export BIBLE_CACHE_ENABLED=true
export BIBLE_CACHE_TTL=7200
export BIBLE_RETRY_ENABLED=true
```

```php
// Automatically reads from environment variables
$config = new BibleConfig();
$bible = Bible::withConfig($config);
```

### Fluent Interface

```php
$config = new BibleConfig();
$config->setVersion('acf')
       ->setTimeout(20.0)
       ->setCacheTtl(1800)
       ->setRetryPolicy(RetryPolicy::aggressive());
```

## 🔄 Retry Logic

The library implements automatic retry with exponential backoff:

### Pre-configured Policies

```php
use HolyBible\Retry\RetryPolicy;

// Default: 3 attempts, 2x backoff
$policy = RetryPolicy::default();

// Aggressive: 5 attempts, 1.5x backoff
$policy = RetryPolicy::aggressive();

// Disabled
$policy = RetryPolicy::disabled();

// Custom
$policy = new RetryPolicy(
    maxAttempts: 4,
    initialDelayMs: 200,
    multiplier: 2.5,
    maxDelayMs: 10000
);
```

### When Retry is Applied

- ✅ Connection errors (timeout, DNS, etc.)
- ✅ Server 5xx errors
- ✅ 429 Error (rate limiting)
- ❌ 4xx Errors (except 429)
- ❌ Local validation errors

## 📝 Logging

Full support for PSR-3 logging:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('bible');
$logger->pushHandler(new StreamHandler('bible.log', Logger::DEBUG));

$config = new BibleConfig(['logger' => $logger]);
$bible = Bible::withConfig($config);

// Automatic logs:
// - DEBUG: Every HTTP request
// - INFO: Successful requests
// - WARNING: Retries, temporary errors
// - ERROR: Definitive failures
```

## 💾 Cache

### File Cache (Default)

```php
use HolyBible\Cache\FileCache;

$cache = new FileCache('/var/cache/bible');
$config = new BibleConfig([
    'cache' => $cache,
    'cache_ttl' => 86400  // 24 hours
]);
```

### Custom Cache

Implement `CacheInterface`:

```php
use HolyBible\Cache\CacheInterface;

class RedisCache implements CacheInterface
{
    public function get(string $key): mixed { /* ... */ }
    public function set(string $key, mixed $value, int $ttl = 3600): bool { /* ... */ }
    public function has(string $key): bool { /* ... */ }
    public function delete(string $key): bool { /* ... */ }
    public function clear(): bool { /* ... */ }
}

$config = new BibleConfig(['cache' => new RedisCache()]);
```

### Disable Cache

```php
$config = new BibleConfig(['cache_enabled' => false]);
```

## 📚 Available DTOs

### BookDTO
```php
$book->name;       // "Genesis"
$book->abbrev;     // "gn"
$book->chapters;   // 50
$book->testament;  // "VT"
```

### ChapterDTO
```php
$chapter->book;           // BookDTO
$chapter->number;         // 3
$chapter->verses;         // VerseDTO[]
$chapter->getVerse(16);   // VerseDTO|null
$chapter->getVerseCount(); // int
```

### VerseDTO
```php
$verse->number;  // 16
$verse->text;    // "For God so loved the world..."
```

### VersionDTO
```php
$version->version;  // "nvi"
$version->name;     // "New International Version"
```

## 🧪 Tests

```bash
# Run all tests
./vendor/bin/phpunit

# With coverage
./vendor/bin/phpunit --coverage-html coverage

# PHPStan
./vendor/bin/phpstan analyse src tests
```

## 📊 Statistics

- **17 unit tests** (100% passing)
- **46 assertions**
- **PHPStan level 8** (0 errors)
- **~1,900 lines of code**
- **18 classes**
- **2 interfaces**
- **5 custom exceptions**

## 🏗️ Architecture

```
src/
├── Cache/          # Cache system
├── Client/         # HTTP client layer
├── Config/         # Configuration
├── DTO/            # Data Transfer Objects
├── Exception/      # Custom exceptions
├── Retry/          # Retry logic
├── Service/        # Business logic
├── Bible.php       # Facade (Public API)
└── Books.php       # Books Enum
```

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the project
2. Create a branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Credits

- API provided by [A Bíblia Digital](https://www.abibliadigital.com.br/)
- Developed by [c0destep](https://github.com/c0destep)

## 📞 Support

- 🐛 [Report Bug](https://github.com/c0destep/holy_bible/issues)
- 💡 [Request Feature](https://github.com/c0destep/holy_bible/issues)
- 📖 [Documentation](https://github.com/c0destep/holy_bible/wiki)

---

**Made with ❤️ in PHP**
