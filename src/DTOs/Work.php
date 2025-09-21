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
        public ?string $doi,
        public string $display_name,
        public int $publication_year,
        public string $type,
        public int $cited_by_count,
        #[MapInputName('authorships')]
        /** @var Collection<Authorship> */
        public Collection $authors,
        public ?Source $primary_location,
        /** @var Collection<Topic> */
        public Collection $topics,
        #[MapInputname('abstract_inverted_index')]
        public ?array $abstract,
        public array $referenced_works,
    ) {}

    /**
     * NEW: Reconstructs the plain text abstract from the inverted index.
     */
    public function getAbstract(): ?string
    {
        if (is_null($this->abstract)) {
            return null;
        }

        $abstractLength = 0;
        foreach ($this->abstract as $word => $positions) {
            $abstractLength = max($abstractLength, ...$positions);
        }
        $abstractLength++;

        $result = array_fill(0, $abstractLength, '');
        foreach ($this->abstract as $word => $positions) {
            foreach ($positions as $pos) {
                $result[$pos] = $word;
            }
        }

        return implode(' ', $result);
    }

    /**
     * NEW: Generates a BibTeX citation entry for the work.
     */
    public function toBibTeX(): string
    {
        $authorList = $this->authors
            ->map(fn (Authorship $a) => $a->author->display_name)
            ->join(' and ');

        $citationKey = sprintf(
            '%s%s',
            $this->authors->first()?->author->display_name ?? 'Unknown',
            $this->publication_year
        );

        return <<<BIBTEX
@article{{$citationKey},
    author  = "{{$authorList}}",
    title   = "{{$this->display_name}}",
    journal = "{{$this->primary_location?->display_name}}",
    year    = "{{$this->publication_year}}",
    doi     = "{{$this->doi}}"
}
BIBTEX;
    }
}
