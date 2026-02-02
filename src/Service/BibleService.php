<?php declare(strict_types=1);

namespace HolyBible\Service;

use HolyBible\Books;
use HolyBible\Cache\CacheInterface;
use HolyBible\Client\BibleClientInterface;
use HolyBible\Config\BibleConfig;
use HolyBible\DTO\BookDTO;
use HolyBible\DTO\ChapterDTO;
use HolyBible\DTO\VerseDTO;
use HolyBible\DTO\VersionDTO;
use HolyBible\Exception\InvalidChapterException;
use HolyBible\Exception\InvalidVerseException;

/**
 * Service layer for Bible API operations
 */
class BibleService
{
    private BibleClientInterface $client;
    private CacheInterface $cache;
    private string $version;
    private int $cacheTtl;

    /**
     * @param BibleClientInterface $client HTTP client
     * @param BibleConfig          $config Configuration
     */
    public function __construct(BibleClientInterface $client, BibleConfig $config)
    {
        $this->client = $client;
        $this->cache = $config->getCache();
        $this->version = $config->getVersion();
        $this->cacheTtl = $config->getCacheTtl();

        $this->client->setUserToken($config->getUserToken());
        $this->client->setTimeout($config->getTimeout());
    }

    /**
     * Get all books in the Bible
     *
     * @return BookDTO[]
     */
    public function getBooks(): array
    {
        $cacheKey = 'books';

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null && is_array($cached)) {
            return $cached;
        }

        $data = $this->client->get('books');
        $books = [];

        foreach ($data as $bookData) {
            if (is_array($bookData)) {
                $books[] = BookDTO::fromArray($bookData);
            }
        }

        $this->cache->set($cacheKey, $books, $this->cacheTtl);

        return $books;
    }

    /**
     * Get all available Bible versions
     *
     * @return VersionDTO[]
     */
    public function getAvailableVersions(): array
    {
        $cacheKey = 'versions';

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null && is_array($cached)) {
            return $cached;
        }

        $data = $this->client->get('versions');
        $versions = [];

        foreach ($data as $versionData) {
            if (is_array($versionData)) {
                $versions[] = VersionDTO::fromArray($versionData);
            }
        }

        $this->cache->set($cacheKey, $versions, $this->cacheTtl);

        return $versions;
    }

    /**
     * Get chapter with all verses
     *
     * @param Books $book    Bible book
     * @param int   $chapter Chapter number
     *
     * @return ChapterDTO
     * @throws InvalidChapterException
     */
    public function getChapter(Books $book, int $chapter): ChapterDTO
    {
        $this->validateChapter($chapter);

        $cacheKey = "chapter:{$this->version}:{$book->value}:{$chapter}";

        $cached = $this->cache->get($cacheKey);
        if ($cached instanceof ChapterDTO) {
            return $cached;
        }

        $uri = "verses/{$this->version}/{$book->value}/{$chapter}";
        $data = $this->client->get($uri);

        $chapterDto = ChapterDTO::fromArray($data);
        $this->cache->set($cacheKey, $chapterDto, $this->cacheTtl);

        return $chapterDto;
    }

    /**
     * Get specific verse
     *
     * @param Books $book    Bible book
     * @param int   $chapter Chapter number
     * @param int   $verse   Verse number
     *
     * @return VerseDTO
     * @throws InvalidChapterException
     * @throws InvalidVerseException
     */
    public function getVerse(Books $book, int $chapter, int $verse): VerseDTO
    {
        $this->validateChapter($chapter);
        $this->validateVerse($verse);

        $cacheKey = "verse:{$this->version}:{$book->value}:{$chapter}:{$verse}";

        $cached = $this->cache->get($cacheKey);
        if ($cached instanceof VerseDTO) {
            return $cached;
        }

        $uri = "verses/{$this->version}/{$book->value}/{$chapter}/{$verse}";
        $data = $this->client->get($uri);

        $verseDto = VerseDTO::fromArray($data);
        $this->cache->set($cacheKey, $verseDto, $this->cacheTtl);

        return $verseDto;
    }

    /**
     * Set Bible version
     *
     * @param string $version Version code
     *
     * @return void
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * Get current Bible version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Validate chapter number
     *
     * @param int $chapter Chapter number
     *
     * @throws InvalidChapterException
     */
    private function validateChapter(int $chapter): void
    {
        if ($chapter < 1) {
            throw new InvalidChapterException('Chapter number must be positive, got: ' . $chapter);
        }
    }

    /**
     * Validate verse number
     *
     * @param int $verse Verse number
     *
     * @throws InvalidVerseException
     */
    private function validateVerse(int $verse): void
    {
        if ($verse < 1) {
            throw new InvalidVerseException('Verse number must be positive, got: ' . $verse);
        }
    }
}
