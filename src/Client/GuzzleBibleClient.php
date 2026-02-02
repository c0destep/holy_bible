<?php declare(strict_types=1);

namespace HolyBible\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use HolyBible\Exception\ApiResponseException;
use HolyBible\Exception\NetworkException;
use HolyBible\Retry\RetryPolicy;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Guzzle-based HTTP client for Bible API with retry and logging
 */
class GuzzleBibleClient implements BibleClientInterface
{
    private const API_URL = 'https://www.abibliadigital.com.br/api/';

    private ?Client $client = null;
    private ?string $userToken = null;
    private float $timeout;
    private string $apiUrl;
    private RetryPolicy $retryPolicy;
    private LoggerInterface $logger;

    /**
     * @param float           $timeout     Timeout in seconds
     * @param string          $apiUrl      API base URL
     * @param RetryPolicy     $retryPolicy Retry policy
     * @param LoggerInterface $logger      PSR-3 logger
     */
    public function __construct(
        float $timeout = 5.0,
        string $apiUrl = self::API_URL,
        ?RetryPolicy $retryPolicy = null,
        ?LoggerInterface $logger = null
    ) {
        $this->timeout = $timeout;
        $this->apiUrl = $apiUrl;
        $this->retryPolicy = $retryPolicy ?? RetryPolicy::default();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @inheritDoc
     */
    public function get(string $uri): array
    {
        $fullUrl = $this->apiUrl . $uri;
        $attempt = 0;

        while (true) {
            try {
                $this->logger->debug('Making API request', [
                    'uri' => $uri,
                    'attempt' => $attempt + 1,
                    'max_attempts' => $this->retryPolicy->maxAttempts
                ]);

                $client = $this->getClient();
                $options = $this->buildRequestOptions();

                $response = $client->get($fullUrl, $options);

                if ($response->getStatusCode() !== 200) {
                    $this->logger->warning('API returned non-200 status', [
                        'status_code' => $response->getStatusCode(),
                        'uri' => $uri
                    ]);

                    throw new ApiResponseException(
                        'API returned status code ' . $response->getStatusCode() . ': ' . $response->getBody()->getContents()
                    );
                }

                $content = $response->getBody()->getContents();
                $data = json_decode($content, true);

                if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                    $this->logger->error('Failed to decode JSON response', [
                        'uri' => $uri,
                        'json_error' => json_last_error_msg()
                    ]);

                    throw new ApiResponseException('Failed to decode JSON response: ' . json_last_error_msg());
                }

                $this->logger->info('API request successful', [
                    'uri' => $uri,
                    'attempt' => $attempt + 1
                ]);

                /** @var array<string, mixed> */
                $result = $data ?? [];
                return $result;

            } catch (ConnectException $e) {
                // Network/connection errors - retryable
                if ($this->retryPolicy->shouldRetry($attempt)) {
                    $delay = $this->retryPolicy->getDelay($attempt);

                    $this->logger->warning('Connection error, retrying', [
                        'uri' => $uri,
                        'attempt' => $attempt + 1,
                        'delay_ms' => $delay,
                        'error' => $e->getMessage()
                    ]);

                    if ($delay > 0) {
                        usleep($delay * 1000);
                    }

                    $attempt++;
                    continue;
                }

                $this->logger->error('Connection error, max retries exceeded', [
                    'uri' => $uri,
                    'attempts' => $attempt + 1,
                    'error' => $e->getMessage()
                ]);

                throw new NetworkException(
                    'Network error: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                );

            } catch (RequestException $e) {
                // HTTP errors (4xx, 5xx) - some are retryable
                $statusCode = $e->getResponse()?->getStatusCode() ?? 0;
                $isRetryable = $statusCode >= 500 || $statusCode === 429; // Server errors and rate limiting

                if ($isRetryable && $this->retryPolicy->shouldRetry($attempt)) {
                    $delay = $this->retryPolicy->getDelay($attempt);

                    $this->logger->warning('HTTP error, retrying', [
                        'uri' => $uri,
                        'status_code' => $statusCode,
                        'attempt' => $attempt + 1,
                        'delay_ms' => $delay
                    ]);

                    if ($delay > 0) {
                        usleep($delay * 1000);
                    }

                    $attempt++;
                    continue;
                }

                $this->logger->error('HTTP error', [
                    'uri' => $uri,
                    'status_code' => $statusCode,
                    'error' => $e->getMessage()
                ]);

                throw new NetworkException(
                    'Network error: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                );

            } catch (GuzzleException $e) {
                // Other Guzzle errors
                $this->logger->error('Guzzle error', [
                    'uri' => $uri,
                    'error' => $e->getMessage()
                ]);

                throw new NetworkException(
                    'Network error: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function setUserToken(?string $token): void
    {
        $this->userToken = $token;
        $this->logger->debug('User token updated', [
            'has_token' => $token !== null
        ]);
    }

    /**
     * @inheritDoc
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
        $this->client = null; // Reset to apply new timeout

        $this->logger->debug('Timeout updated', [
            'timeout' => $timeout
        ]);
    }

    /**
     * Set retry policy
     *
     * @param RetryPolicy $retryPolicy Retry policy
     *
     * @return void
     */
    public function setRetryPolicy(RetryPolicy $retryPolicy): void
    {
        $this->retryPolicy = $retryPolicy;

        $this->logger->debug('Retry policy updated', [
            'enabled' => $retryPolicy->enabled,
            'max_attempts' => $retryPolicy->maxAttempts
        ]);
    }

    /**
     * Set logger
     *
     * @param LoggerInterface $logger PSR-3 logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Build request options
     *
     * @return array<string, mixed>
     */
    private function buildRequestOptions(): array
    {
        $options = [];

        if ($this->userToken !== null) {
            $options['headers'] = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->userToken
            ];
        }

        return $options;
    }

    /**
     * Get or create Guzzle client instance
     *
     * @return Client
     */
    private function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client([
                'timeout' => $this->timeout
            ]);
        }

        return $this->client;
    }
}
