<?php

declare(strict_types=1);

namespace Tests\Client;

use HolyBible\Client\SQLiteBibleClient;
use HolyBible\Exception\ApiResponseException;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SQLiteBibleClientTest extends TestCase
{
    private string $dbPath;
    private PDO $pdo;
    private SQLiteBibleClient $client;

    public function testGetBooks(): void
    {
        $books = $this->client->get('books');

        $this->assertCount(2, $books);
        $this->assertEquals('Gênesis', $books[0]['name']);
        $this->assertEquals('gn', $books[0]['abbrev']['pt']);
        $this->assertEquals('VT', $books[0]['testament']);
    }

    public function testGetVersions(): void
    {
        $versions = $this->client->get('versions');

        $this->assertCount(1, $versions);
        $this->assertEquals('nvi', $versions[0]['version']);
        $this->assertEquals('NVI', $versions[0]['name']);
    }

    public function testGetChapter(): void
    {
        $data = $this->client->get('verses/nvi/gn/1');

        $this->assertEquals('Gênesis', $data['book']['name']);
        $this->assertEquals(1, $data['chapter']['number']);
        $this->assertCount(2, $data['verses']);
        $this->assertEquals('No princípio...', $data['verses'][0]['text']);
    }

    public function testGetSingleVerse(): void
    {
        $data = $this->client->get('verses/nvi/jo/3/16');

        $this->assertEquals(16, $data['number']);
        $this->assertEquals('Porque Deus amou...', $data['text']);
    }

    public function testUnknownEndpointThrowsException(): void
    {
        $this->expectException(ApiResponseException::class);
        $this->expectExceptionMessage('Unknown endpoint: invalid');

        $this->client->get('invalid');
    }

    public function testBookNotFoundThrowsException(): void
    {
        $this->expectException(ApiResponseException::class);
        $this->expectExceptionMessage('Book not found: unknown');

        $this->client->get('verses/nvi/unknown/1');
    }

    public function testVerseNotFoundThrowsException(): void
    {
        $this->expectException(ApiResponseException::class);
        $this->expectExceptionMessage('Verse not found');

        $this->client->get('verses/nvi/jo/3/999');
    }

    public function testInvalidVersesUriThrowsException(): void
    {
        $this->expectException(ApiResponseException::class);
        $this->expectExceptionMessage('Invalid verses URI');

        $this->client->get('verses/nvi');
    }

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is not available.');
        }

        $this->dbPath = ':memory:';
        $this->client = new SQLiteBibleClient($this->dbPath);

        // Obter a instância do PDO via reflexão para configurar o banco em memória
        $reflection = new ReflectionClass($this->client);
        $property = $reflection->getProperty('pdo');
        $this->pdo = $property->getValue($this->client);

        $this->setupDatabase();
    }

    private function setupDatabase(): void
    {
        $this->pdo->exec(
            "
            CREATE TABLE testaments (id INTEGER PRIMARY KEY, name TEXT NOT NULL);
            CREATE TABLE books (id INTEGER PRIMARY KEY, name TEXT NOT NULL, abbrev TEXT NOT NULL, testament_id INTEGER, FOREIGN KEY(testament_id) REFERENCES testaments(id));
            CREATE TABLE verses (id INTEGER PRIMARY KEY, version TEXT NOT NULL, chapter INTEGER NOT NULL, verse INTEGER NOT NULL, text TEXT NOT NULL, book_id INTEGER, FOREIGN KEY(book_id) REFERENCES books(id));

            INSERT INTO testaments (id, name) VALUES (1, 'Velho Testamento'), (2, 'Novo Testamento');
            INSERT INTO books (id, name, abbrev, testament_id) VALUES (1, 'Gênesis', 'gn', 1), (2, 'João', 'jo', 2);
            INSERT INTO verses (version, chapter, verse, text, book_id) VALUES
                ('nvi', 1, 1, 'No princípio...', 1),
                ('nvi', 1, 2, 'E a terra...', 1),
                ('nvi', 3, 16, 'Porque Deus amou...', 2);
        "
        );
    }
}
