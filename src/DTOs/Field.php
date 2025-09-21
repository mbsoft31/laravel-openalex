<?php

namespace Mbsoft\OpenAlex\DTOs;

use Spatie\LaravelData\Data;

class Field extends Data
{
    public function __construct(public string $id, public string $display_name)
    {
    }
}
