<?php
namespace Kadevjo\Fibonacci\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = "notification";

    public function client()
    {
        return $this->belongsTo('Kadevjo\Fibonacci\Models\Client');
    }
}
