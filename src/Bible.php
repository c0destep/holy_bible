<?php declare(strict_types=1);

namespace HolyBible;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Bible
 */
class Bible
{
    /**
     * API URL
     */
    private const API_URL = 'https://www.abibliadigital.com.br/api/';

    /**
     * @var string
     */
    private string $version;
    /**
     * @var string|null
     */
    private ?string $userToken;

    /**
     * @param string $version Default NVI (Nova Versão Internacional)
     * @param string|null $userToken
     */
    public function __construct(string $version = 'nvi', string $userToken = null)
    {
        $this->version = $version;
        $this->userToken = $userToken;
    }

    /**
     * @param Books $book
     * @param int $chapter
     * @return string[]
     */
    public function getChapter(Books $book = Books::GENESIS, int $chapter = 1): array
    {
        return $this->getContentApi('verses/' . $this->version . DIRECTORY_SEPARATOR . $book->value . DIRECTORY_SEPARATOR . $chapter);
    }

    /**
     * @param string $uri
     * @return string[]
     */
    public function getContentApi(string $uri): array
    {
        try {
            $client = new Client([
                'base_url' => self::API_URL,
                'timeout' => 2.0
            ]);

            if (!is_null($this->userToken)) {
                $response = $client->get($uri, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->userToken
                    ]
                ]);
            } else {
                $response = $client->get($uri);
            }

            if ($response->getStatusCode() !== 200) {
                return [
                    'error' => $response->getBody()->getContents()
                ];
            }

            return json_decode($response->getBody()->getContents(), true) ?? ['error' => 'json decode fail'];
        } catch (GuzzleException $guzzleException) {
            return [
                'error' => $guzzleException->getMessage()
            ];
        }
    }

    /**
     * @return string
     */
    public function getCurrentVersion(): string
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
     * @return string[]
     */
    public function getBooks(): array
    {
        return $this->getContentApi('books');
    }

    /**
     * @return string[]
     */
    public function getAvailableVersions(): array
    {
        return $this->getContentApi('versions');
    }

    /**
     * @param Books $book
     * @param int $chapter
     * @param int $verse
     * @return string[]
     */
    public function getVerse(Books $book = Books::GENESIS, int $chapter = 1, int $verse = 1): array
    {
        return $this->getContentApi('verses/' . $this->version . DIRECTORY_SEPARATOR . $book->value . DIRECTORY_SEPARATOR . $chapter . DIRECTORY_SEPARATOR . $verse);
    }
}
