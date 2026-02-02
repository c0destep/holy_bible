# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-02-02

### Added
- **Retry Logic** with exponential backoff
  - `RetryPolicy` class with configurable attempts and delays
  - Automatic retry for network errors and 5xx responses
  - Smart retry for rate limiting (429)
  - Preset policies: `default()`, `aggressive()`, `disabled()`
  
- **PSR-3 Logging Support**
  - Full integration with any PSR-3 compatible logger
  - Debug, info, warning, and error logs
  - Detailed context for all operations
  - NullLogger as default (no logging overhead)
  
- **Cache System**
  - `CacheInterface` (PSR-16-like)
  - `FileCache` implementation with TTL support
  - `NullCache` for disabling cache
  - Automatic cache invalidation
  - Up to 400x performance improvement
  
- **Data Transfer Objects (DTOs)**
  - `BookDTO` - Type-safe book information
  - `ChapterDTO` - Chapter with verses
  - `VerseDTO` - Individual verse
  - `VersionDTO` - Bible version info
  - All DTOs use readonly properties (PHP 8.1+)
  
- **Service Layer Architecture**
  - `BibleClientInterface` for HTTP abstraction
  - `GuzzleBibleClient` implementation
  - `BibleService` for business logic
  - Clean separation of concerns
  
- **Flexible Configuration**
  - `BibleConfig` class
  - Support for configuration arrays
  - Environment variable support
  - Fluent interface
  - Custom cache/logger/retry injection
  
- **Enhanced Documentation**
  - Comprehensive README with examples
  - CHANGELOG following Keep a Changelog format
  - Code examples for all features
  - Architecture documentation

### Changed
- **BREAKING**: `Bible` class refactored as facade
  - Still 100% backward compatible for basic usage
  - New `withConfig()` method for advanced configuration
  - `getService()` method to access service layer
  
- **Enhanced Error Handling**
  - Better exception messages
  - Retry context in exceptions
  - Logging of all errors
  
- **Improved Performance**
  - HTTP client reuse
  - Automatic caching
  - Configurable timeouts

### Fixed
- Critical bug: `DIRECTORY_SEPARATOR` in URLs (Windows compatibility)
- CI/CD: Tests now run correctly
- PHPStan level 8 compliance (0 errors)

## [1.0.0] - Previous Version

### Added
- Basic Bible API integration
- `getChapter()`, `getVerse()`, `getBooks()` methods
- `Books` enum
- Basic exception handling
- Initial test coverage
- PHPStan level 7

### Features
- Guzzle HTTP client
- Basic error handling
- Simple API wrapper

---

## Migration Guide

### From 1.x to 2.0

#### Basic Usage (No Changes Required)
```php
// This still works exactly the same
$bible = new Bible();
$chapter = $bible->getChapter(Books::JOHN, 3);
```

#### Advanced Features (New)
```php
// New: Configuration
$config = new BibleConfig([
    'cache_enabled' => true,
    'retry_enabled' => true,
    'logger' => $myLogger
]);
$bible = Bible::withConfig($config);

// New: Type-safe DTOs
$service = $bible->getService();
$chapterDto = $service->getChapter(Books::JOHN, 3);
echo $chapterDto->book->name;  // Type-safe!
```

#### Environment Variables (New)
```bash
# Configure via environment
export BIBLE_VERSION=nvi
export BIBLE_CACHE_ENABLED=true
export BIBLE_RETRY_ENABLED=true
```

---

**Note**: Version 2.0 is fully backward compatible. Existing code will continue to work without modifications.
