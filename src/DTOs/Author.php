<?php

namespace Mbsoft\OpenAlex\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class Author extends Data
{
    public function __construct(
        public string $id,
        #[MapInputName('ids.orcid')]
        public ?string $orcid,
        public string $display_name,
        // FIX: These properties are not present when an author is nested in a Work's authorships.
        // Making them nullable allows the DTO to be created in all contexts.
        public ?int $works_count,
        public ?int $cited_by_count,
        #[MapInputName('summary_stats.h_index')]
        public ?int $h_index,
        public ?Institution $last_known_institution,
    ) {}
}
