<?php
namespace Kadevjo\Fibonacci\Models;

use Illuminate\Database\Eloquent\Model;

class Binnacle extends Model
{
    protected $table = "notification_device";

    public function client()
    {
        return $this->belongsTo('Kadevjo\Fibonacci\Models\Client');
    }

}
