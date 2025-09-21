<?php

namespace Mbsoft\OpenAlex\DTOs;

use Spatie\LaravelData\Data;

class Location extends Data
{
    public function __construct(
        public ?bool $is_oa,
        public ?string $landing_page_url,
        public ?string $pdf_url,
        public ?string $license,
        public ?string $version,
        // The Source object is nested inside the Location object
        public ?Source $source,
    ) {}
}
