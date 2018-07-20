<?php
namespace Kadevjo\Fibonacci\Observers;

use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Kreait\Firebase\ServiceAccount;
use Kadevjo\Fibonacci\Models\LiveData;
use Illuminate\Support\Facades\Storage;


class LiveDataObserver
{
    private static function database()
    {
        $serviceAccount = ServiceAccount::fromJsonFile(public_path().'/'.Storage::url(env('FIREBASE_JSON')));
        $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->withDatabaseUri(env('FIREBASE_DB_URI'))
        ->create();
        return $firebase->getDatabase();
    }

    public function creating(LiveData $data)
    {
        $livedata = $this->database()->getReference('data/'.$data->folder)->push([
            'name' => $data->name,
            'type' => $data->type,
            'state' => $data->state
            ]);
        //$livedata = $this->database()->getReference('data/'.$data->folder)->push($data->metadata);
        $data->key= $livedata->getKey();
        return $data;
    }

    public function updating(LiveData $data)
    {
        //$this->database()->getReference('data/'.$data->folder.'/'.$data->key)->update($data->makeHidden(['created_at','updated_at','key','id','folder'])->toArray());
    }

    /**
     * Listen to the User deleting event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleting(LiveData $data)
    {
        if($data->type == 'temporary'){
            $this->database()->getReference('data/'.$data->folder.'/'.$data->key)->remove();
        }
    }
}
