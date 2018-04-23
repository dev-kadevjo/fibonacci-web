<?php
namespace Kadevjo\Fibonacci\Observers;

use Kadevjo\Fibonacci\Models\Client;
use Kadevjo\Fibonacci\Models\Binnacle;

class BaseObserver
{
    
    /**
     * Listen to the Model created event.
     *
     * @param  Model  $log
     * @return void 
     */
    
    public function created($log){
        $this->VerifySlugAndLog($log,"Created");
    }

    /**
     * Listen to the Model updated event.
     *
     * @param  Model  $log
     * @return void
     */
    public function updated($log)
    {
        $this->VerifySlugAndLog($log,"Updated");
    }

    /**
     * Listen to the Model deleted event.
     *
     * @param  Model  $log
     * @return void
     */
    public function deleted($log)
    {
        $this->VerifySlugAndLog($log,"Deleted");
    }

    /**
     * Listen to the Model restored event.
     *
     * @param  Model  $log
     * @return void
     */
    public function restored($log)
    {
        $this->VerifySlugAndLog($log,"Restored");
    }

    public function VerifySlugAndLog($class,$method)
    {
        if($class && array_key_exists('Kadevjo\Fibonacci\Traits\Loggable',class_uses($class)))
        {
            $entity = class_basename($class);

            if(class_basename(\Auth::user())==class_basename(Client::class))
            {
                $source = "api";
            }
            elseif(class_basename(\Auth::user())==class_basename(\App\User::class))
            {
                $source = "web";
            }
            else
            {
                $source = "other";
            }
            
            $author = is_null(\Auth::user())?null:\Auth::user()->email;
            $id_table = $class->id;
            
            $data = ["entity"=>$entity,"action"=>$method,"source"=>$source,"author"=>$author, "id_table"=>$id_table];
            
            $this->saveLoggable($data);
        }
    }

    // Get model by table name
    private function getModel($table){
        $name = studly_case(str_singular($table));
        try {
            
            $entity = "App\\".$name;
            
            $modelClass =  \App::make($entity);
        }
        catch (\Exception $e) 
        {
            
            $entity = "TCG\Voyager\Models\\".$name;
            
            $modelClass =  \App::make($entity);
        }
        
        return $modelClass;
    }

    public function saveLoggable(array $data){
        
        $binnacle = new Binnacle();
        
        $binnacle->forceFill($data)->save();
    }
}