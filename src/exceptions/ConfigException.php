<?php

namespace Kadevjo\Fibonacci\Exceptions;

use Exception;

class ConfigException extends Exception
{

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json('Please make sure your enviroment on fibonacci config');
    }
    
}