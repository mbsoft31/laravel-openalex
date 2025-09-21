<?php

namespace Mbsoft\OpenAlex\DTOs;

use Spatie\LaravelData\Data;

class Subfield extends Data
{
    public function __construct(public string $id, public string $display_name) {}
}
