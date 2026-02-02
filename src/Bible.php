<?php
declare(strict_types=1);

namespace HolyBible;

use HolyBible\Client\GuzzleBibleClient;
use HolyBible\Config\BibleConfig;
use HolyBible\Exception\InvalidChapterException;
use HolyBible\Exception\InvalidVerseException;
use HolyBible\Exception\NetworkException;
use HolyBible\Exception\ApiResponseException;
use HolyBible\Service\BibleService;

/**
 * Bible API Facade
 * 
 * This class maintains backward compatibility while delegating to the new service layer
 */
class Bible
{
    private BibleService $service;
    private BibleConfig $config;

    /**
     * @param string      $version   Bible version default is "nvi" [optional]
     * @param string|null $userToken User token provided by API [optional]
     * @param float       $timeout   Request timeout in seconds [optional]
     */
    public function __construct(string $version = 'nvi', ?string $userToken = null, float $timeout = 5.0)
    {
        $this->config = new BibleConfig([
            'version' => $version,
            'user_token' => $userToken,
            'timeout' => $timeout,
            'cache_enabled' => true,
            'cache_ttl' => 3600
        ]);

        $client = new GuzzleBibleClient($timeout, $this->config->getApiUrl());
        $this->service = new BibleService($client, $this->config);
    }

    /**
     * Create Bible instance with custom configuration
     *
     * @param BibleConfig $config Configuration object
     *
     * @return self
     */
    public static function withConfig(BibleConfig $config): self
    {
        $instance = new self();
        $instance->config = $config;

        $client = new GuzzleBibleClient(
            $config->getTimeout(),
            $config->getApiUrl(),
            $config->getRetryPolicy(),
            $config->getLogger()
        );
        $instance->service = new BibleService($client, $config);

        return $instance;
    }

    /**
     * Get all verses from a specific chapter
     *
     * @param Books $book    Bible book
     * @param int   $chapter Book chapter (must be positive)
     *
     * @return array<string, mixed> Chapter data with verses
     * @throws InvalidChapterException If chapter number is invalid
     * @throws NetworkException If network error occurs
     * @throws ApiResponseException If API returns unexpected response
     */
    public function getChapter(Books $book = Books::GENESIS, int $chapter = 1): array
    {
        $chapterDto = $this->service->getChapter($book, $chapter);
        return $chapterDto->raw;
    }

    /**
     * Get a specific verse
     *
     * @param Books $book    Bible book
     * @param int   $chapter Book chapter (must be positive)
     * @param int   $verse   Chapter verse (must be positive)
     *
     * @return array<string, mixed> Verse data
     * @throws InvalidChapterException If chapter number is invalid
     * @throws InvalidVerseException If verse number is invalid
     * @throws NetworkException If network error occurs
     * @throws ApiResponseException If API returns unexpected response
     */
    public function getVerse(Books $book = Books::GENESIS, int $chapter = 1, int $verse = 1): array
    {
        $verseDto = $this->service->getVerse($book, $chapter, $verse);
        return $verseDto->raw;
    }

    /**
     * Get list of all books in the Bible
     *
     * @return array<string, mixed> List of all books
     * @throws NetworkException If network error occurs
     * @throws ApiResponseException If API returns unexpected response
     */
    public function getBooks(): array
    {
        $books = $this->service->getBooks();
        return array_map(fn($book) => $book->raw, $books);
    }

    /**
     * Get list of all available Bible versions
     *
     * @return array<string, mixed> List of all available versions
     * @throws NetworkException If network error occurs
     * @throws ApiResponseException If API returns unexpected response
     */
    public function getAvailableVersions(): array
    {
        $versions = $this->service->getAvailableVersions();
        return array_map(fn($version) => $version->raw, $versions);
    }

    /**
     * Get current Bible version
     *
     * @return string Current bible version
     */
    public function getCurrentVersion(): string
    {
        return $this->service->getVersion();
    }

    /**
     * Set Bible version
     *
     * @param string $version Bible version
     *
     * @return Bible
     */
    public function setVersion(string $version): Bible
    {
        $this->config->setVersion($version);
        $this->service->setVersion($version);
        return $this;
    }

    /**
     * Get current user token
     *
     * @return string|null Current user token. NULL if you don't have one.
     */
    public function getUserToken(): ?string
    {
        return $this->config->getUserToken();
    }

    /**
     * Set user token
     *
     * @param string $userToken User token
     *
     * @return Bible
     */
    public function setUserToken(string $userToken): Bible
    {
        $this->config->setUserToken($userToken);
        return $this;
    }

    /**
     * Get request timeout
     *
     * @return float Timeout in seconds
     */
    public function getTimeout(): float
    {
        return $this->config->getTimeout();
    }

    /**
     * Set request timeout
     *
     * @param float $timeout Timeout in seconds
     *
     * @return Bible
     */
    public function setTimeout(float $timeout): Bible
    {
        $this->config->setTimeout($timeout);
        return $this;
    }

    /**
     * Get underlying service instance
     *
     * @return BibleService
     */
    public function getService(): BibleService
    {
        return $this->service;
    }

    /**
     * Get configuration instance
     *
     * @return BibleConfig
     */
    public function getConfig(): BibleConfig
    {
        return $this->config;
    }
}
