<?php

namespace CodingWisely\Taskallama\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CodingWisely\Taskallama\Taskallama
 */
class Taskallama extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CodingWisely\Taskallama\Taskallama::class;
    }
}
