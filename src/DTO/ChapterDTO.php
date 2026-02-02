<?php declare(strict_types=1);

namespace HolyBible\DTO;

/**
 * Chapter Data Transfer Object
 */
class ChapterDTO
{
    /**
     * @param BookDTO              $book    Book information
     * @param int                  $number  Chapter number
     * @param VerseDTO[]           $verses  Array of verses
     * @param array<string, mixed> $raw     Raw API response
     */
    public function __construct(
        public readonly BookDTO $book,
        public readonly int $number,
        public readonly array $verses,
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
        $verses = [];
        if (isset($data['verses']) && is_array($data['verses'])) {
            foreach ($data['verses'] as $verseData) {
                $verses[] = VerseDTO::fromArray($verseData);
            }
        }

        $bookData = $data['book'] ?? [];
        $book = BookDTO::fromArray($bookData);

        return new self(
            book: $book,
            number: $data['chapter']['number'] ?? 0,
            verses: $verses,
            raw: $data
        );
    }

    /**
     * Get verse by number
     *
     * @param int $number Verse number
     *
     * @return VerseDTO|null
     */
    public function getVerse(int $number): ?VerseDTO
    {
        foreach ($this->verses as $verse) {
            if ($verse->number === $number) {
                return $verse;
            }
        }
        return null;
    }

    /**
     * Get total number of verses
     *
     * @return int
     */
    public function getVerseCount(): int
    {
        return count($this->verses);
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'book' => $this->book->toArray(),
            'number' => $this->number,
            'verses' => array_map(fn($v) => $v->toArray(), $this->verses)
        ];
    }
}
