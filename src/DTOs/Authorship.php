<?php

namespace Mbsoft\OpenAlex\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class Authorship extends Data
{
    public function __construct(
        public Author $author,
        /** @var DataCollection<Institution> */
        public DataCollection $institutions,
    ) {
    }
}
