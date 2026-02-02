<?php declare(strict_types=1);

namespace HolyBible\DTO;

/**
 * Version Data Transfer Object
 */
class VersionDTO
{
    /**
     * @param string               $version Version code
     * @param string               $name    Version name
     * @param array<string, mixed> $raw     Raw API response
     */
    public function __construct(
        public readonly string $version,
        public readonly string $name,
        public readonly array $raw = []
    ) {
    }

    /**
     * Create from API response array
     *
     * @param array<string, mixed> $data API response data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            version: $data['version'] ?? '',
            name: $data['name'] ?? '',
            raw: $data
        );
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'name' => $this->name
        ];
    }
}
