<?php

namespace Kadevjo\Fibonacci\Models;

use Illuminate\Database\Eloquent\Model;
use Kadevjo\Fibonacci\Traits\Loggable; 

class Report extends Model{ 
    use Loggable;    
    protected $table = 'reports';
    protected $guarded = [];

    protected static function boot()
    {
      parent::boot();
    } 
}
