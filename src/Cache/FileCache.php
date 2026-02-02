<?php declare(strict_types=1);

namespace HolyBible\Cache;

/**
 * File-based cache implementation
 */
class FileCache implements CacheInterface
{
    private string $cacheDir;

    /**
     * @param string $cacheDir Directory to store cache files
     */
    public function __construct(string $cacheDir = '')
    {
        if ($cacheDir === '') {
            $cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'holy_bible_cache';
        }

        $this->cacheDir = $cacheDir;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): mixed
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $data = unserialize($content);

        if (!is_array($data) || !isset($data['expires'], $data['value'])) {
            return null;
        }

        if ($data['expires'] < time()) {
            $this->delete($key);
            return null;
        }

        return $data['value'];
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $file = $this->getFilePath($key);
        $data = [
            'expires' => time() + $ttl,
            'value' => $value
        ];

        $result = file_put_contents($file, serialize($data), LOCK_EX);
        return $result !== false;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return true;
        }

        return unlink($file);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        if (!is_dir($this->cacheDir)) {
            return true;
        }

        $files = glob($this->cacheDir . DIRECTORY_SEPARATOR . '*.cache');
        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Get file path for cache key
     *
     * @param string $key Cache key
     *
     * @return string File path
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        return $this->cacheDir . DIRECTORY_SEPARATOR . $hash . '.cache';
    }

    /**
     * Get cache directory path
     *
     * @return string Cache directory
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }
}
