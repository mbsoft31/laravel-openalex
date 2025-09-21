<?php

namespace Mbsoft\OpenAlex\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;

class Author extends Data
{
    public function __construct(
        public string $id,
        #[MapInputName('ids.orcid')]
        public ?string $orcid,
        public string $display_name,
        public int $works_count,
        public int $cited_by_count,
        #[MapInputName('summary_stats.h_index')]
        public int $h_index,
        public ?Institution $last_known_institution,
    ) {
    }
}
