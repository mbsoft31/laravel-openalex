<?php

namespace Mbsoft\OpenAlex\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class Institution extends Data
{
    public function __construct(
        public string $id,
        #[MapInputName('ids.ror')]
        public string $ror,
        public string $display_name,
        public string $country_code,
        public string $type,
        public int $works_count,
        public int $cited_by_count,
    ) {}
}
