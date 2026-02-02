<?php declare(strict_types=1);

namespace HolyBible\DTO;

/**
 * Verse Data Transfer Object
 */
class VerseDTO
{
    /**
     * @param int                  $number Verse number
     * @param string               $text   Verse text
     * @param array<string, mixed> $raw    Raw API response
     */
    public function __construct(
        public readonly int $number,
        public readonly string $text,
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
            number: $data['number'] ?? 0,
            text: $data['text'] ?? '',
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
            'number' => $this->number,
            'text' => $this->text
        ];
    }
}
