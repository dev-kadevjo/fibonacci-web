<?php
namespace Kadevjo\Fibonacci\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;


class Notification extends Model
{
    protected $table = "notification";

    use SerializesModels;

    public function broadcastOn()
    {
        return [];
    }

    public function client()
    {
        return $this->belongsTo('Kadevjo\Fibonacci\Models\Client');
    }
}
