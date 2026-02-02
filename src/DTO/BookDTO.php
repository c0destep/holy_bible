<?php declare(strict_types=1);

namespace HolyBible\DTO;

/**
 * Book Data Transfer Object
 */
class BookDTO
{
    /**
     * @param string                $abbrev Book abbreviation
     * @param string                $name   Book name
     * @param int                   $chapters Number of chapters
     * @param string                $testament Testament (VT or NT)
     * @param array<string, mixed>  $raw    Raw API response
     */
    public function __construct(
        public readonly string $abbrev,
        public readonly string $name,
        public readonly int $chapters,
        public readonly string $testament,
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
            abbrev: $data['abbrev']['pt'] ?? '',
            name: $data['name'] ?? '',
            chapters: $data['chapters'] ?? 0,
            testament: $data['testament'] ?? '',
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
            'abbrev' => $this->abbrev,
            'name' => $this->name,
            'chapters' => $this->chapters,
            'testament' => $this->testament
        ];
    }
}
