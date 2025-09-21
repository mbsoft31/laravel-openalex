<?php

namespace Mbsoft\OpenAlex\DTOs;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class Work extends Data
{
    public function __construct(
        public string $id,
        #[MapInputName('ids.doi')]
        public ?string        $doi,
        public string         $display_name,
        public int            $publication_year,
        public string         $type,
        public int            $cited_by_count,
        /** @var \Mbsoft\OpenAlex\DTOs\Authorship[]|Optional */
        public array|Optional $authorships,
        public ?Location      $primary_location,
        /** @var \Mbsoft\OpenAlex\DTOs\Topic[]|Optional */
        public array|Optional $topics,
        /** @var string[]|Optional */
        public array|Optional $referenced_works,
        #[MapInputName('abstract_inverted_index')]
        public ?array         $abstract,
    )
    {
    }

    /**
     * Reconstructs the abstract from the inverted index.
     */
    public function getAbstract(): ?string
    {
        if (is_null($this->abstract)) {
            return null;
        }

        $wordPositions = [];
        foreach ($this->abstract as $word => $positions) {
            foreach ($positions as $position) {
                $wordPositions[$position] = $word;
            }
        }
        ksort($wordPositions);

        return implode(' ', $wordPositions);
    }

    /**
     * Generates a BibTeX citation string.
     */
    public function toBibTeX(): string
    {
        $authorships = ($this->authorships instanceof Optional) ? [] : $this->authorships;

        // FINAL FIX 1: Use an empty string for the author list if empty.
        $authorList = empty($authorships)
            ? ''
            : implode(' and ', array_map(fn($authorship) => $authorship->author->display_name, $authorships));

        $lastName = 'Unknown';
        if (!empty($authorships)) {
            $firstAuthorParts = explode(' ', $authorships[0]->author->display_name);
            $lastName = end($firstAuthorParts);
        }
        $citationKey = $lastName . $this->publication_year;

        $journal = $this->primary_location?->source->display_name ?? 'Unknown Journal';

        $doi = str_replace('https://doi.org/', '', $this->doi ?? '');

        return <<<BIBTEX
@article{{$citationKey},
    author  = "{$authorList}",
    title   = "{$this->display_name}",
    journal = "{$journal}",
    year    = "{$this->publication_year}",
    doi     = "{$doi}"
}
BIBTEX;
    }
}
