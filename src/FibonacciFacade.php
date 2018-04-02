<?php

namespace Kadevjo\Fibonacci;

use Illuminate\Support\Facades\Facade;

class FibonacciFacade extends Facade {

    protected static function getFacadeAccessor() {
        return 'fibonacci';
    }
}