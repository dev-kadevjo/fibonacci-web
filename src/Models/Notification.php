<?php
namespace Kadevjo\Fibonacci\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Kadevjo\Fibonacci\Traits\Loggable; 


class Notification extends Model
{
    use SerializesModels,Loggable;

    protected $table = "notification";

    protected static function boot()
    {
      parent::boot();
    } 
    
    public function broadcastOn()
    {
        return [];
    }

    public function client()
    {
        return $this->belongsTo('Kadevjo\Fibonacci\Models\Client');
    }
}
