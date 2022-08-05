<?php

declare(strict_types=1);

namespace Codestep\HolyBible;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Bible
{
    private static Bible $instance;
    protected string $pathRoot;
    protected string $pathBooks;
    protected bool $useCache;
    protected array $books = [];
    protected array $versions = [];
    protected string $version;
    private string $apiUrl = 'https://www.abibliadigital.com.br/api/';
    private ?string $userToken;

    private function __construct(string $version = 'nvi', bool $useCache = true, string $userToken = null)
    {
        $this->pathRoot = dirname(__DIR__);
        $this->pathBooks = $this->pathRoot . '/storage/books';
        $this->version = $version;
        $this->useCache = $useCache;
        $this->userToken = $userToken;

        if ($this->useCache === true) {
            if (empty($this->versions)) {
                $this->getVersionsApi();
            }
            if (empty($this->books)) {
                $this->getBooksApi();
                $this->getBooksSaved();
            }
        }
    }

    private function getVersionsApi(): void
    {
        try {
            $this->versions = $this->getApi($this->apiUrl . 'versions');
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function getApi(string $url): array
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!is_null($this->userToken)) {
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                "Authorization: Bearer $this->userToken"
            ];

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);

        curl_close($ch);

        if (is_bool($response) && $response === false) {
            throw new Exception(curl_error($ch));
        }

        $decode = json_decode($response, true);

        if (is_null($decode)) {
            throw new Exception('Error json decode');
        }

        return $decode;
    }

    private function getBooksApi(): void
    {
        try {
            $books = $this->getApi($this->apiUrl . 'books');

            foreach ($books as $book) {
                $data = [];
                $filename = Books::from($book['abbrev']['pt'])->value . '.json';

                if (!file_exists($this->pathBooks . '/' . $filename)) {
                    for ($i = 1; $i <= $book['chapters']; $i++) {
                        $data[] = $this->getApi($this->apiUrl . 'verses/' . $this->version . '/' . Books::from($book['abbrev']['pt'])->value . '/' . $i);
                    }

                    $file = fopen($this->pathBooks . '/' . $filename, 'w');

                    fwrite($file, json_encode($data));
                    fclose($file);
                }
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    private function getBooksSaved(): void
    {
        if ($this->hasDirectoryStorage() && $this->hasDirectoryBooks()) {
            $directoryIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->pathBooks, FilesystemIterator::SKIP_DOTS));

            foreach ($directoryIterator as $filePath => $file) {
                if ((pathinfo($filePath)['extension'] === 'json') && !is_null(Books::tryFrom(pathinfo($filePath)['filename']))) {
                    $this->books[pathinfo($filePath)['filename']] = json_decode(file_get_contents($filePath), true);
                }
            }
        }
    }

    private function hasDirectoryStorage(): bool
    {
        $directoryIterator = new FilesystemIterator($this->pathRoot, FilesystemIterator::SKIP_DOTS);

        if ($directoryIterator->valid()) {
            foreach ($directoryIterator as $fileInfo) {
                if ($fileInfo->isDir() && $fileInfo->getFilename() === 'storage' && $fileInfo->isReadable() && $fileInfo->isWritable()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasDirectoryBooks(): bool
    {
        $directoryIterator = new FilesystemIterator($this->pathRoot . '/storage', FilesystemIterator::SKIP_DOTS);

        if ($directoryIterator->valid()) {
            foreach ($directoryIterator as $fileInfo) {
                if ($fileInfo->isDir() && $fileInfo->getFilename() === 'books' && $fileInfo->isReadable() && $fileInfo->isWritable()) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function getInstance(string $version = 'nvi', bool $useCache = true, string $userToken = null): Bible
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($version, $useCache, $userToken);
        }
        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public function getChapter(Books $book = Books::GENESIS, int $chapter = 1): array
    {
        if (!$this->useCache) {
            return $this->getApi($this->apiUrl . 'verses/' . $this->version . '/' . $book->value . '/' . $chapter);
        }

        foreach ($this->books as $index => $fBook) {
            if ($index === $book->value) {
                return $fBook[$chapter - 1];
            }
        }

        return [];
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return Bible
     */
    public function setVersion(string $version): Bible
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserToken(): ?string
    {
        return $this->userToken;
    }

    /**
     * @param string $userToken
     * @return Bible
     */
    public function setUserToken(string $userToken): Bible
    {
        $this->userToken = $userToken;
        return $this;
    }

    /**
     * @return array
     */
    public function getBooks(): array
    {
        if (empty($this->books)) {
            $this->getBooksApi();
            return $this->books;
        }
        return $this->books;
    }

    /**
     * @return array
     */
    public function getVersions(): array
    {
        if (empty($this->versions)) {
            $this->getVersionsApi();
            return $this->versions;
        }
        return $this->versions;
    }

    /**
     * @throws Exception
     */
    public function getVerse(Books $book = Books::GENESIS, int $chapter = 1, int $verse = 1): array
    {
        if (!$this->useCache) {
            return $this->getApi($this->apiUrl . 'verses/' . $this->version . '/' . $book->value . '/' . $chapter . '/' . $verse);
        }

        foreach ($this->books as $index => $fBook) {
            if ($index === $book->value) {
                return $fBook[$chapter - 1]['verses'][$verse - 1];
            }
        }

        return [];
    }

    public function __clone()
    {
    }

    public function __wakeup()
    {
    }
}
