<?php declare(strict_types=1);

namespace HolyBible\Retry;

/**
 * Retry policy configuration
 */
class RetryPolicy
{
    /**
     * @param int   $maxAttempts      Maximum number of retry attempts
     * @param int   $initialDelayMs   Initial delay in milliseconds
     * @param float $multiplier       Backoff multiplier
     * @param int   $maxDelayMs       Maximum delay in milliseconds
     * @param bool  $enabled          Whether retry is enabled
     */
    public function __construct(
        public readonly int $maxAttempts = 3,
        public readonly int $initialDelayMs = 100,
        public readonly float $multiplier = 2.0,
        public readonly int $maxDelayMs = 5000,
        public readonly bool $enabled = true
    ) {
    }

    /**
     * Create disabled retry policy
     *
     * @return self
     */
    public static function disabled(): self
    {
        return new self(enabled: false);
    }

    /**
     * Create default retry policy
     *
     * @return self
     */
    public static function default(): self
    {
        return new self();
    }

    /**
     * Create aggressive retry policy
     *
     * @return self
     */
    public static function aggressive(): self
    {
        return new self(
            maxAttempts: 5,
            initialDelayMs: 50,
            multiplier: 1.5,
            maxDelayMs: 3000
        );
    }

    /**
     * Calculate delay for given attempt
     *
     * @param int $attempt Attempt number (0-based)
     *
     * @return int Delay in milliseconds
     */
    public function getDelay(int $attempt): int
    {
        if (!$this->enabled || $attempt === 0) {
            return 0;
        }

        $delay = (int) ($this->initialDelayMs * pow($this->multiplier, $attempt - 1));
        return min($delay, $this->maxDelayMs);
    }

    /**
     * Check if should retry
     *
     * @param int $attempt Current attempt number (0-based)
     *
     * @return bool
     */
    public function shouldRetry(int $attempt): bool
    {
        return $this->enabled && $attempt < $this->maxAttempts;
    }
}
