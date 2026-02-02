<?php declare(strict_types=1);

namespace HolyBible\Config;

use HolyBible\Cache\CacheInterface;
use HolyBible\Cache\FileCache;
use HolyBible\Cache\NullCache;
use HolyBible\Retry\RetryPolicy;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Configuration class for Bible API
 */
class BibleConfig
{
    private string $version;
    private ?string $userToken;
    private float $timeout;
    private bool $cacheEnabled;
    private int $cacheTtl;
    private CacheInterface $cache;
    private string $apiUrl;
    private RetryPolicy $retryPolicy;
    private LoggerInterface $logger;

    /**
     * @param array<string, mixed> $config Configuration array
     */
    public function __construct(array $config = [])
    {
        $this->version = $config['version'] ?? $this->getEnv('BIBLE_VERSION', 'nvi');
        $this->userToken = $config['user_token'] ?? $this->getEnv('BIBLE_USER_TOKEN', null);
        $this->timeout = (float) ($config['timeout'] ?? $this->getEnv('BIBLE_TIMEOUT', '5.0'));
        $this->cacheEnabled = (bool) ($config['cache_enabled'] ?? $this->getEnv('BIBLE_CACHE_ENABLED', 'true') === 'true');
        $this->cacheTtl = (int) ($config['cache_ttl'] ?? $this->getEnv('BIBLE_CACHE_TTL', '3600'));
        $this->apiUrl = $config['api_url'] ?? $this->getEnv('BIBLE_API_URL', 'https://www.abibliadigital.com.br/api/');

        // Initialize cache
        if (isset($config['cache']) && $config['cache'] instanceof CacheInterface) {
            $this->cache = $config['cache'];
        } elseif ($this->cacheEnabled) {
            $cacheDir = $config['cache_dir'] ?? '';
            $this->cache = new FileCache($cacheDir);
        } else {
            $this->cache = new NullCache();
        }

        // Initialize retry policy
        if (isset($config['retry_policy']) && $config['retry_policy'] instanceof RetryPolicy) {
            $this->retryPolicy = $config['retry_policy'];
        } else {
            $retryEnabled = (bool) ($config['retry_enabled'] ?? $this->getEnv('BIBLE_RETRY_ENABLED', 'true') === 'true');
            $this->retryPolicy = $retryEnabled ? RetryPolicy::default() : RetryPolicy::disabled();
        }

        // Initialize logger
        $this->logger = $config['logger'] ?? new NullLogger();
    }

    /**
     * Get environment variable with default
     *
     * @param string      $key     Environment variable name
     * @param string|null $default Default value
     *
     * @return string|null
     */
    private function getEnv(string $key, ?string $default): ?string
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }

    /**
     * Create config from array
     *
     * @param array<string, mixed> $config Configuration array
     *
     * @return self
     */
    public static function fromArray(array $config): self
    {
        return new self($config);
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function getUserToken(): ?string
    {
        return $this->userToken;
    }

    public function setUserToken(?string $userToken): self
    {
        $this->userToken = $userToken;
        return $this;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function setTimeout(float $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function isCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }

    public function setCacheEnabled(bool $cacheEnabled): self
    {
        $this->cacheEnabled = $cacheEnabled;
        return $this;
    }

    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }

    public function setCacheTtl(int $cacheTtl): self
    {
        $this->cacheTtl = $cacheTtl;
        return $this;
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function setCache(CacheInterface $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function setApiUrl(string $apiUrl): self
    {
        $this->apiUrl = $apiUrl;
        return $this;
    }

    public function getRetryPolicy(): RetryPolicy
    {
        return $this->retryPolicy;
    }

    public function setRetryPolicy(RetryPolicy $retryPolicy): self
    {
        $this->retryPolicy = $retryPolicy;
        return $this;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }
}
