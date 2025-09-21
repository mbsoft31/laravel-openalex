<?php

namespace Mbsoft\OpenAlex\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class Work extends Data
{
    public function __construct(
        public string $id,
        #[MapInputName('ids.doi')]
        public ?string $doi,
        public string $display_name,
        public int $publication_year,
        public string $type,
        public int $cited_by_count,
        #[MapInputName('authorships')]
        /** @var DataCollection<Authorship> */
        public DataCollection $authors,
        public ?Source $primary_location,
        /** @var DataCollection<Topic> */
        public DataCollection $topics,
        #[MapInputname('abstract_inverted_index')]
        public ?array $abstract,
        public array $referenced_works,
    ) {
    }
}
