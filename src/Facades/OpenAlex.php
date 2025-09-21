<?php

namespace Mbsoft\OpenAlex\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mbsoft\OpenAlex\OpenAlex
 */
class OpenAlex extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Mbsoft\OpenAlex\OpenAlex::class;
    }
}
