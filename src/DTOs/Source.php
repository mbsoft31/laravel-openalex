<?php

namespace Mbsoft\OpenAlex\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class Source extends Data
{
    public function __construct(
        public string $id,
        #[MapInputName('ids.issn_l')]
        public ?string $issn_l,
        public string $display_name,
        public ?string $publisher,
        public string $type,
        public ?string $homepage_url,
        public int $works_count,
        public int $cited_by_count,
    ) {
    }
}
