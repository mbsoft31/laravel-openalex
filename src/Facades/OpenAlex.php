<?php

namespace Mbsoft\OpenAlex\Facades;

use Illuminate\Support\Facades\Facade;
use Mbsoft\OpenAlex\Builder;

/**
 * @method static Builder works()
 * @method static Builder authors()
 * @method static Builder sources()
 * @method static Builder institutions()
 * @method static Builder topics()
 *
 * @see \Mbsoft\OpenAlex\OpenAlex
 */
class OpenAlex extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'openalex';
    }
}
