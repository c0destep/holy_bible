<?php
declare(strict_types=1);

namespace Tests\Integration;

use HolyBible\Bible;
use HolyBible\Books;
use HolyBible\Config\BibleConfig;
use PDO;
use PHPUnit\Framework\TestCase;

class BibleSQLiteIntegrationTest extends TestCase
{
    private string $dbPath;

    public function testBibleFacadeUsesSQLiteClientWhenPathProvided(): void
    {
        $config = new BibleConfig([
            'sqlite_path' => $this->dbPath,
            'version'     => 'nvi'
        ]);

        $bible = Bible::withConfig($config);

        // Teste indireto: se retornar os dados do nosso banco temporário, está usando o SQLiteClient
        $verse = $bible->getVerse(Books::PSALMS, 23, 1);

        $this->assertEquals('O Senhor é o meu pastor...', $verse['text']);
        $this->assertEquals(1, $verse['number']);
    }

    public function testBibleConstructorWithSqlitePathViaEnv(): void
    {
        putenv("BIBLE_SQLITE_PATH={$this->dbPath}");

        try {
            $bible = new Bible('nvi');
            $chapter = $bible->getChapter(Books::PSALMS, 23);

            $this->assertEquals('Salmos', $chapter['book']['name']);
            $this->assertCount(1, $chapter['verses']);
        } finally {
            putenv('BIBLE_SQLITE_PATH');
        }
    }

    protected function setUp(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is not available.');
        }

        $this->dbPath = tempnam(sys_get_temp_dir(), 'bible_test') . '.sqlite';
        $pdo = new PDO("sqlite:{$this->dbPath}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec(
            "
            CREATE TABLE testaments (id INTEGER PRIMARY KEY, name TEXT NOT NULL);
            CREATE TABLE books (id INTEGER PRIMARY KEY, name TEXT NOT NULL, abbrev TEXT NOT NULL, testament_id INTEGER, FOREIGN KEY(testament_id) REFERENCES testaments(id));
            CREATE TABLE verses (id INTEGER PRIMARY KEY, version TEXT NOT NULL, chapter INTEGER NOT NULL, verse INTEGER NOT NULL, text TEXT NOT NULL, book_id INTEGER, FOREIGN KEY(book_id) REFERENCES books(id));

            INSERT INTO testaments (id, name) VALUES (1, 'Velho Testamento');
            INSERT INTO books (id, name, abbrev, testament_id) VALUES (1, 'Salmos', 'sl', 1);
            INSERT INTO verses (version, chapter, verse, text, book_id) VALUES ('nvi', 23, 1, 'O Senhor é o meu pastor...', 1);
        "
        );
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }
    }
}
