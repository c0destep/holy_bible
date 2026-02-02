<?php declare(strict_types=1);

namespace HolyBible\Cache;

/**
 * Cache interface for Bible API responses
 */
interface CacheInterface
{
    /**
     * Get cached value by key
     *
     * @param string $key Cache key
     *
     * @return mixed|null Cached value or null if not found/expired
     */
    public function get(string $key): mixed;

    /**
     * Set cached value with TTL
     *
     * @param string $key   Cache key
     * @param mixed  $value Value to cache
     * @param int    $ttl   Time to live in seconds
     *
     * @return bool True on success
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Check if key exists and is not expired
     *
     * @param string $key Cache key
     *
     * @return bool True if exists and valid
     */
    public function has(string $key): bool;

    /**
     * Delete cached value
     *
     * @param string $key Cache key
     *
     * @return bool True on success
     */
    public function delete(string $key): bool;

    /**
     * Clear all cached values
     *
     * @return bool True on success
     */
    public function clear(): bool;
}
