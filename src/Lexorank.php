<?php

namespace Dede\Lexorank;

use Illuminate\Support\Facades\Facade;

class Lexorank extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'lexorank';
    }
}
