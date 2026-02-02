<?php declare(strict_types=1);

namespace HolyBible\Cache;

/**
 * Null cache implementation (no caching)
 */
class NullCache implements CacheInterface
{
    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return true;
    }
}
