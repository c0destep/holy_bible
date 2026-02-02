<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use HolyBible\Bible;
use HolyBible\Books;
use HolyBible\Exception\ApiResponseException;
use HolyBible\Exception\InvalidChapterException;
use HolyBible\Exception\InvalidVerseException;
use HolyBible\Exception\NetworkException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BibleTest extends TestCase
{
    /**
     * Helper method to inject a mocked Guzzle client into Bible instance
     */
    private function injectMockClient(Bible $bible, Client $mockClient): void
    {
        $service = $bible->getService();
        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);

        // Create a mock BibleClientInterface that wraps the Guzzle mock
        $mockBibleClient = $this->createMock(\HolyBible\Client\BibleClientInterface::class);
        $mockBibleClient->method('get')->willReturnCallback(function ($uri) use ($mockClient) {
            try {
                $response = $mockClient->get('https://www.abibliadigital.com.br/api/' . $uri);
                $content = $response->getBody()->getContents();

                if ($response->getStatusCode() !== 200) {
                    throw new \HolyBible\Exception\ApiResponseException(
                        'API returned status code ' . $response->getStatusCode()
                    );
                }

                $data = json_decode($content, true);
                if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                    throw new \HolyBible\Exception\ApiResponseException(
                        'Failed to decode JSON response'
                    );
                }

                return $data ?? [];
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                throw new \HolyBible\Exception\NetworkException(
                    'Network error: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        });

        $property->setValue($service, $mockBibleClient);
    }

    public function testGetBooksSuccess(): void
    {
        $mockResponse = [
            ['abbrev' => ['pt' => 'gn'], 'name' => 'Gênesis'],
            ['abbrev' => ['pt' => 'ex'], 'name' => 'Êxodo']
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse) ?: '{}')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $bible = new Bible();
        $this->injectMockClient($bible, $mockClient);

        $books = $bible->getBooks();

        $this->assertCount(2, $books);
        $this->assertArrayHasKey(0, $books);
        /** @phpstan-ignore offsetAccess.notFound */
        $this->assertIsArray($books[0]);
        $this->assertArrayHasKey('name', $books[0]);
        $this->assertEquals('Gênesis', $books[0]['name']);
    }

    public function testGetChapterSuccess(): void
    {
        $mockResponse = [
            'book' => ['name' => 'Gênesis'],
            'chapter' => ['number' => 1],
            'verses' => [
                ['number' => 1, 'text' => 'No princípio...']
            ]
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse) ?: '{}')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $bible = new Bible();
        $this->injectMockClient($bible, $mockClient);

        $chapter = $bible->getChapter(Books::GENESIS, 1);

        $this->assertArrayHasKey('book', $chapter);
        $this->assertArrayHasKey('chapter', $chapter);
        $this->assertIsArray($chapter['book']);
        $this->assertIsArray($chapter['chapter']);
        $this->assertEquals('Gênesis', $chapter['book']['name']);
        $this->assertEquals(1, $chapter['chapter']['number']);
    }

    public function testGetVerseSuccess(): void
    {
        $mockResponse = [
            'book' => ['name' => 'João'],
            'chapter' => 3,
            'number' => 16,
            'text' => 'Porque Deus amou o mundo...'
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse) ?: '{}')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $bible = new Bible();
        $this->injectMockClient($bible, $mockClient);

        $verse = $bible->getVerse(Books::JOHN, 3, 16);

        $this->assertArrayHasKey('book', $verse);
        $this->assertArrayHasKey('number', $verse);
        $this->assertIsArray($verse['book']);
        $this->assertEquals('João', $verse['book']['name']);
        $this->assertEquals(16, $verse['number']);
    }

    public function testGetAvailableVersionsSuccess(): void
    {
        $mockResponse = [
            ['version' => 'nvi', 'name' => 'Nova Versão Internacional'],
            ['version' => 'acf', 'name' => 'Almeida Corrigida Fiel']
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse) ?: '{}')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $bible = new Bible();
        $this->injectMockClient($bible, $mockClient);

        $versions = $bible->getAvailableVersions();

        $this->assertNotEmpty($versions);
        $this->assertCount(2, $versions);
    }

    public function testInvalidChapterThrowsException(): void
    {
        $this->expectException(InvalidChapterException::class);
        $this->expectExceptionMessage('Chapter number must be positive, got: 0');

        $bible = new Bible();
        $bible->getChapter(Books::GENESIS, 0);
    }

    public function testNegativeChapterThrowsException(): void
    {
        $this->expectException(InvalidChapterException::class);
        $this->expectExceptionMessage('Chapter number must be positive, got: -5');

        $bible = new Bible();
        $bible->getChapter(Books::GENESIS, -5);
    }

    public function testInvalidVerseThrowsException(): void
    {
        $this->expectException(InvalidVerseException::class);
        $this->expectExceptionMessage('Verse number must be positive, got: 0');

        $bible = new Bible();
        $bible->getVerse(Books::GENESIS, 1, 0);
    }

    public function testNegativeVerseThrowsException(): void
    {
        $this->expectException(InvalidVerseException::class);
        $this->expectExceptionMessage('Verse number must be positive, got: -10');

        $bible = new Bible();
        $bible->getVerse(Books::GENESIS, 1, -10);
    }

    public function testNetworkErrorThrowsException(): void
    {
        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Network error:');

        $mock = new MockHandler([
            new ConnectException('Connection timeout', new Request('GET', 'test'))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $bible = new Bible();
        $this->injectMockClient($bible, $mockClient);

        $bible->getBooks();
    }

    public function testNon200StatusThrowsException(): void
    {
        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Network error:');

        // Guzzle throws exceptions for 4xx/5xx by default
        $mock = new MockHandler([
            new Response(404, [], 'Not found')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $bible = new Bible();
        $this->injectMockClient($bible, $mockClient);

        $bible->getBooks();
    }

    public function testInvalidJsonThrowsException(): void
    {
        $this->expectException(ApiResponseException::class);
        $this->expectExceptionMessage('Failed to decode JSON response');

        $mock = new MockHandler([
            new Response(200, [], 'invalid json{')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $bible = new Bible();
        $this->injectMockClient($bible, $mockClient);

        $bible->getBooks();
    }

    public function testSetAndGetVersion(): void
    {
        $bible = new Bible('nvi');
        $this->assertEquals('nvi', $bible->getCurrentVersion());

        $bible->setVersion('acf');
        $this->assertEquals('acf', $bible->getCurrentVersion());
    }

    public function testSetAndGetUserToken(): void
    {
        $bible = new Bible();
        $this->assertNull($bible->getUserToken());

        $bible->setUserToken('test-token-123');
        $this->assertEquals('test-token-123', $bible->getUserToken());
    }

    public function testSetAndGetTimeout(): void
    {
        $bible = new Bible();
        $this->assertEquals(5.0, $bible->getTimeout());

        $bible->setTimeout(10.0);
        $this->assertEquals(10.0, $bible->getTimeout());
    }

    public function testConstructorWithCustomParameters(): void
    {
        $bible = new Bible('acf', 'my-token', 15.0);

        $this->assertEquals('acf', $bible->getCurrentVersion());
        $this->assertEquals('my-token', $bible->getUserToken());
        $this->assertEquals(15.0, $bible->getTimeout());
    }

    public function testFluentInterface(): void
    {
        $bible = new Bible();

        $result = $bible->setVersion('acf')
            ->setUserToken('token')
            ->setTimeout(20.0);

        $this->assertInstanceOf(Bible::class, $result);
        $this->assertEquals('acf', $bible->getCurrentVersion());
        $this->assertEquals('token', $bible->getUserToken());
        $this->assertEquals(20.0, $bible->getTimeout());
    }

    public function testGetChapterWithAuthToken(): void
    {
        $mockResponse = ['verses' => []];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse) ?: '{}')
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockClient = new Client(['handler' => $handlerStack]);

        $bible = new Bible('nvi', 'test-token');
        $this->injectMockClient($bible, $mockClient);

        $chapter = $bible->getChapter(Books::GENESIS, 1);

        $this->assertNotEmpty($chapter);
    }
}
