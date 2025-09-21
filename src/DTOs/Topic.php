<?php

namespace Mbsoft\OpenAlex\DTOs;

use Spatie\LaravelData\Data;

class Topic extends Data
{
    public function __construct(
        public string $id,
        public string $display_name,
        public Domain $domain,
        public Field $field,
        public ?Subfield $subfield,
    ) {}
}
