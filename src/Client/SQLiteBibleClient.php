<?php

declare(strict_types=1);

namespace HolyBible\Client;

use HolyBible\Exception\ApiResponseException;
use PDO;
use PDOException;

/**
 * SQLite client for Bible data
 */
class SQLiteBibleClient implements BibleClientInterface
{
    private PDO $pdo;

    public function __construct(string $dbPath)
    {
        try {
            $this->pdo = new PDO("sqlite:$dbPath");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new ApiResponseException("Could not connect to SQLite database: " . $e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function get(string $uri): array
    {
        $parts = explode('/', trim($uri, '/'));
        $endpoint = $parts[0];

        $data = match ($endpoint) {
            'books' => $this->getBooks(),
            'versions' => $this->getVersions(),
            'verses' => $this->getVerses($parts),
            default => throw new ApiResponseException("Unknown endpoint: $endpoint"),
        };

        /** @var array<mixed> $data */
        return $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getBooks(): array
    {
        $stmt = $this->pdo->query(
            "
            SELECT b.abbrev, b.name, t.name as testament,
            (SELECT COUNT(DISTINCT chapter) FROM verses WHERE book_id = b.id) as chapters
            FROM books b
            JOIN testaments t ON b.testament_id = t.id
        "
        );

        if (!$stmt) {
            throw new ApiResponseException("Failed to execute books query");
        }

        $books = [];
        foreach ($stmt->fetchAll() as $row) {
            $books[] = [
                'abbrev'    => ['pt' => $row['abbrev']],
                'name'      => $row['name'],
                'chapters'  => (int)$row['chapters'],
                'testament' => $row['testament'] === 'Velho Testamento' ? 'VT' : 'NT'
            ];
        }

        return $books;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getVersions(): array
    {
        $stmt = $this->pdo->query("SELECT DISTINCT version FROM verses");

        if (!$stmt) {
            throw new ApiResponseException("Failed to execute versions query");
        }

        $versions = [];
        foreach ($stmt->fetchAll() as $row) {
            $versions[] = [
                'version' => $row['version'],
                'name'    => strtoupper($row['version'])
            ];
        }
        return $versions;
    }

    /**
     * @param array<int, string> $parts
     *
     * @return array<string, mixed>
     */
    private function getVerses(array $parts): array
    {
        // verses/{version}/{book}/{chapter}
        // verses/{version}/{book}/{chapter}/{verse}
        if (count($parts) < 4) {
            throw new ApiResponseException("Invalid verses URI");
        }

        $version = $parts[1];
        $bookAbbrev = $parts[2];
        $chapter = (int)$parts[3];
        $verseNumber = isset($parts[4]) ? (int)$parts[4] : null;

        $stmt = $this->pdo->prepare("SELECT id FROM books WHERE abbrev = ?");
        $stmt->execute([$bookAbbrev]);
        $bookId = $stmt->fetchColumn();

        if (!$bookId && $bookId !== 0) {
            throw new ApiResponseException("Book not found: $bookAbbrev");
        }

        if ($verseNumber !== null) {
            return $this->getSingleVerse($version, $bookId, $bookAbbrev, $chapter, $verseNumber);
        }

        return $this->getChapter($version, $bookId, $bookAbbrev, $chapter);
    }

    /**
     * @param int|string $bookId
     *
     * @return array<string, mixed>
     */
    private function getSingleVerse(string $version, $bookId, string $bookAbbrev, int $chapter, int $verseNumber): array
    {
        $stmt = $this->pdo->prepare(
            "
            SELECT verse as number, text
            FROM verses
            WHERE version = ? AND book_id = ? AND chapter = ? AND verse = ?
        "
        );
        $stmt->execute([$version, $bookId, $chapter, $verseNumber]);
        $verse = $stmt->fetch();

        if (!$verse) {
            throw new ApiResponseException("Verse not found");
        }

        return $verse;
    }

    /**
     * @param int|string $bookId
     *
     * @return array<string, mixed>
     */
    private function getChapter(string $version, $bookId, string $bookAbbrev, int $chapter): array
    {
        $stmt = $this->pdo->prepare(
            "
            SELECT b.name, t.name as testament
            FROM books b
            JOIN testaments t ON b.testament_id = t.id
            WHERE b.id = ?
        "
        );
        $stmt->execute([$bookId]);
        $bookInfo = $stmt->fetch();

        $stmt = $this->pdo->prepare(
            "
            SELECT verse as number, text
            FROM verses
            WHERE version = ? AND book_id = ? AND chapter = ?
            ORDER BY verse
        "
        );
        $stmt->execute([$version, $bookId, $chapter]);
        $verses = $stmt->fetchAll();

        return [
            'book'    => [
                'abbrev'    => ['pt' => $bookAbbrev],
                'name'      => $bookInfo['name'],
                'testament' => $bookInfo['testament'] === 'Velho Testamento' ? 'VT' : 'NT'
            ],
            'chapter' => ['number' => $chapter],
            'verses'  => $verses
        ];
    }

    public function setUserToken(?string $token): void
    {
    }

    public function setTimeout(float $timeout): void
    {
    }
}
