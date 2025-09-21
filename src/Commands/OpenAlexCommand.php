<?php

namespace Mbsoft\OpenAlex\Commands;

use Illuminate\Console\Command;

class OpenAlexCommand extends Command
{
    public $signature = 'laravel-openalex';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
