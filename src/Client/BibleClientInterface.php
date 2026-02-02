<?php declare(strict_types=1);

namespace HolyBible\Client;

use HolyBible\Exception\ApiResponseException;
use HolyBible\Exception\NetworkException;

/**
 * Interface for Bible API HTTP client
 */
interface BibleClientInterface
{
    /**
     * Make GET request to API
     *
     * @param string $uri API endpoint URI
     *
     * @return array<string, mixed> Response data
     * @throws NetworkException If network error occurs
     * @throws ApiResponseException If API returns unexpected response
     */
    public function get(string $uri): array;

    /**
     * Set user authentication token
     *
     * @param string|null $token User token
     *
     * @return void
     */
    public function setUserToken(?string $token): void;

    /**
     * Set request timeout
     *
     * @param float $timeout Timeout in seconds
     *
     * @return void
     */
    public function setTimeout(float $timeout): void;
}
