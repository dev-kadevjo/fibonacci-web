<?php

namespace Kadevjo\Fibonacci\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait Loggable {

    public static function bootLoggable()
    {
        static::creating(function ($model) {
            Loggable::loggable($model,"CREATE");
        });

        static::updating(function ($model) {
            Loggable::loggable($model,"UPDATE");
        });

        static::deleting(function ($model) {
            Loggable::loggable($model,"DELETE");
        });
    }

    static private function activeGuard(){

        foreach(array_keys(config('auth.guards')) as $guard){

            if(auth()->guard($guard)->check()) return $guard;

        }
        return null;
    }

    public static function loggable($model,$method){
        try {
            $clase_base = "system";
            $source = "other";
            $author = "system";
            $entity = class_basename($model);

            if(Auth::check()){
                $clase_base = class_basename(Auth::user());
                $source = static::activeGuard();
                $author = is_null(Auth::user())?null:Auth::user()->email;
            }

            $data =
                [
                    "entity"=>$entity,
                    "action"=>$method,
                    "source"=>$source,
                    "author"=>$author,
                    "id_table"=>$model->id,
                    "old" => json_encode($model->getDirty()),
                    "new" =>json_encode($model)
                ];
            $log = new  \Kadevjo\Fibonacci\Models\Log();
            $log->forceFill($data)->save();

         } catch (\Exception $th) {
             \Log::debug($th);
         }

    }


}
