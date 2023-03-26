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
     * @var string Bible version
     */
    private string $version;
    /**
     * @var string|null User token provided by API
     */
    private ?string $userToken;

    /**
     * @param string $version Bible version default is "nvi" [optional]
     * @param string|null $userToken User token provided by API [optional]
     */
    public function __construct(string $version = 'nvi', string $userToken = null)
    {
        $this->version = $version;
        $this->userToken = $userToken;
    }

    /**
     * @param Books $book Bible book
     * @param int $chapter Book chapter
     * @return string[] List of all verses of the respective book and chapter
     */
    public function getChapter(Books $book = Books::GENESIS, int $chapter = 1): array
    {
        return $this->getContentApi('verses/' . $this->version . DIRECTORY_SEPARATOR . $book->value . DIRECTORY_SEPARATOR . $chapter);
    }

    /**
     * @param string $uri API route
     * @return string[] List with data
     */
    public function getContentApi(string $uri): array
    {
        try {
            $client = new Client([
                'timeout' => 2.0
            ]);

            if (!is_null($this->userToken)) {
                $response = $client->get(self::API_URL . $uri, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->userToken
                    ]
                ]);
            } else {
                $response = $client->get(self::API_URL . $uri);
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
     * @return string Current bible version
     */
    public function getCurrentVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version Bible version
     * @return Bible
     */
    public function setVersion(string $version): Bible
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string|null Current user token. NULL if you don't have one.
     */
    public function getUserToken(): ?string
    {
        return $this->userToken;
    }

    /**
     * @param string $userToken User token
     * @return Bible
     */
    public function setUserToken(string $userToken): Bible
    {
        $this->userToken = $userToken;
        return $this;
    }

    /**
     * @return string[] List of all books in the bible
     */
    public function getBooks(): array
    {
        return $this->getContentApi('books');
    }

    /**
     * @return string[] List of all available versions of the Bible
     */
    public function getAvailableVersions(): array
    {
        return $this->getContentApi('versions');
    }

    /**
     * @param Books $book Bible book
     * @param int $chapter Book chapter
     * @param int $verse Chapter verse
     * @return string[] Verse text
     */
    public function getVerse(Books $book = Books::GENESIS, int $chapter = 1, int $verse = 1): array
    {
        return $this->getContentApi('verses/' . $this->version . DIRECTORY_SEPARATOR . $book->value . DIRECTORY_SEPARATOR . $chapter . DIRECTORY_SEPARATOR . $verse);
    }
}
