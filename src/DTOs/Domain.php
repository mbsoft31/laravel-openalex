<?php

namespace Mbsoft\OpenAlex\DTOs;

use Spatie\LaravelData\Data;

class Domain extends Data
{
    public function __construct(public string $id, public string $display_name) {}
}
