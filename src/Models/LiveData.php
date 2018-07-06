<?php
namespace Kadevjo\Fibonacci\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Kadevjo\Fibonacci\Traits\Loggable;
use Illuminate\Notifications\Notifiable;
use Kadevjo\Fibonacci\Observers\LiveDataObserver;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Kreait\Firebase\ServiceAccount;
use Illuminate\Support\Facades\Storage;

class LiveData extends Model
{
    use SerializesModels, Notifiable;

    protected $table = "live_data";

    protected static function boot(){
        static::observe(LiveDataObserver::class);
    }

    private static function database()
    {
        $serviceAccount = ServiceAccount::fromJsonFile(public_path().'/'.Storage::url(env('FIREBASE_JSON')));
        $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->withDatabaseUri(env('FIREBASE_DB_URI'))
        ->create();
        return $firebase->getDatabase();
    }

    public function getMetadataAttribute($value){
        if(config('fibonacci.firebase')['live-model'] && $this->id && $this->key && $this->folder){
            return json_encode($this->database()->getReference('data/'.$this->folder.'/'.$this->key)->getSnapshot()->getValue());
        }
        return $value;
    }

    public function setMetadataAttribute($value){
        if($this->key){
            if(config('fibonacci.firebase')['override'])
                $this->database()->getReference('data/'.$this->folder.'/'.$this->key.'/content')->set($value);
            else
                $this->database()->getReference('data/'.$this->folder.'/'.$this->key.'/content')->update($value);
            $this->attributes['metadata'] = json_encode($value);    
        }
        return null;
    }

}
