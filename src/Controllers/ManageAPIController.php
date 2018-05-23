<?php

namespace Kadevjo\Fibonacci\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use TCG\Voyager\Database\DatabaseUpdater;
use TCG\Voyager\Database\Schema\Column;
use TCG\Voyager\Database\Schema\Identifier;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Database\Schema\Table;
use TCG\Voyager\Database\Types\Type;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataDeleted;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Events\BreadImagesDeleted;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;
use TCG\Voyager\Events\BreadAdded;
use TCG\Voyager\Events\BreadDeleted;
use TCG\Voyager\Events\BreadUpdated;
use TCG\Voyager\Events\TableAdded;
use TCG\Voyager\Events\TableDeleted;
use TCG\Voyager\Events\TableUpdated;
use TCG\Voyager\Models\DataRow;
use Kadevjo\Fibonacci\Models\ApiConfig;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;
use TCG\Voyager\Http\Controllers\Controller as BaseVoyagerController;
use Illuminate\Filesystem\Filesystem;

class ManageAPIController extends BaseVoyagerController
{
    use BreadRelationshipParser;

    // API Functions 
    public function addAPI(Request $request, $table) {
        Voyager::canOrFail('browse_database');
        $data = new ApiConfig;
        $newRow = $data->makeJson(null);
        return view('fibonacci::enhances.api.edit-add-api', compact('table', 'newRow'));
    }
    
    public function storeAPI(Request $request) {
        Voyager::canOrFail('browse_database');        
        try {
            $newRow = new ApiConfig;
            $newRow->config = $newRow->makeJson($request->all());
            $newRow->table_name = ($request->all())['table_name'];
            $newRow->creating_o  = ($request->all())['creating_o'];
            $newRow->created_o  = ($request->all())['created_o'];
            $newRow->updating_o  = ($request->all())['updating_o'];
            $newRow->updated_o  = ($request->all())['updated_o'];
            $newRow->deleting_o  = ($request->all())['deleting_o'];
            $newRow->deleted_o  = ($request->all())['deleted_o'];
            $newRow->restoring_o  = ($request->all())['restoring_o'];
            $newRow->restored_o  = ($request->all())['restored_o'];          
            $nameModel = Str::singular($newRow->table_name);
            $this->createObserver($nameModel); 
            $this->editModelRW(ucwords($nameModel));
            $this->editObserver($nameModel,$newRow);

            $data = $newRow->save()
                ? $this->alertSuccess(__('voyager.database.success_created_api'))
                : $this->alertError(__('voyager.database.error_creating_api'));

            return redirect()->route('voyager.database.index')->with($data);
        } catch (Exception $e) {
            return redirect()->route('voyager.database.index')->with($this->alertException($e, 'Saving Failed'));
        }
    }      

    public function addEditAPI($table) {
        Voyager::canOrFail('browse_database');

        $dataRow = ApiConfig::where('table_name', '=', $table)->orderBy('created_at', 'desc')->first();
        
        return view('fibonacci::enhances.api.edit-add-api', compact('table', 'dataRow'));
    }

    public function updateAPI(Request $request, $id) {      
        Voyager::canOrFail('browse_database');
        
        try {            
            $targetRow = ApiConfig::where('table_name', '=', ($request->all())['table_name'])->first();
            $targetRow->config = $targetRow->makeJson($request->all());
            //$targetRow->custom_code = ($request->all())['code'];
            //$targetRow->execution = ($request->all())['exc'];
            $targetRow->creating_o  = ($request->all())['creating_o'];
            $targetRow->created_o  = ($request->all())['created_o'];
            $targetRow->updating_o  = ($request->all())['updating_o'];
            $targetRow->updated_o  = ($request->all())['updated_o'];
            $targetRow->deleting_o  = ($request->all())['deleting_o'];
            $targetRow->deleted_o  = ($request->all())['deleted_o'];
            $targetRow->restoring_o  = ($request->all())['restoring_o'];
            $targetRow->restored_o  = ($request->all())['restored_o'];
            $nameModel = Str::singular($targetRow->table_name);
            $this->createObserver($nameModel); 
            $this->editObserver($nameModel,$targetRow);

            $data = $targetRow->save()
                ? $this->alertSuccess(__('voyager.database.success_update_api', ['datatype' => ($request->all())['table_name']]))
                : $this->alertError(__('voyager.database.error_updating_api'));
            
            return redirect()->route('voyager.database.index')->with($data);
        } catch (Exception $e) {
            return back()->with($this->alertException($e, __('voyager.generic.update_failed')));
        }
    }

    public function deleteAPI($table) {
        Voyager::canOrFail('browse_database');

        // Remove API config
        $delete = ApiConfig::where('table_name', '=', $table)->first();
        
        $data = $delete->delete()
            ? $this->alertSuccess(__('voyager.database.success_remove_api', ['datatype' => $table]))
            : $this->alertError(__('voyager.database.error_removing_api'));
        
        
        return redirect()->route('voyager.database.index')->with($data);
    }

    public function createObserver($model){
        $observersDir = app_path('Observers');
        $fileData = new Filesystem();
        if (!$fileData->exists($observersDir)) {
            $fileData->makeDirectory($observersDir);
        }        
        $observerStubContent = file_get_contents(__DIR__.'/../stubs/observer.stub', true); 
        $observerStString = ["$model",ucwords($model)];
        $observerConstans = ["DummyVariable","DummyClass"];
        $observerText = str_replace($observerConstans, $observerStString, $observerStubContent);
        $observerDirectory = ucwords($model)."Observer";
        $fileData->put(app_path('Observers' . '/' . $observerDirectory . '.php'), $observerText);

       
    }

    public function editModelRW($name){
        $fname = base_path("app/".$name.".php");      
        $fhandle = fopen($fname,"r");
        $content = fread($fhandle,filesize($fname));
        $triStrign = str_replace(' ', '', "\App\ $name::observe(\App\Observers\ $name Observer::class);");
        
        if (strchr($content,"protected static function boot()",true) === false) {
            $content = str_replace("use Loggable;", 'use Loggable; 
            /**
            * The "booting" method of the model. 
            *
            * @return void
            */
            protected static function boot()
            {
                parent::boot();
                '.$triStrign.'       
            }', $content);
            $fhandle = fopen($fname,"w");
            fwrite($fhandle,$content);
        }
        fclose($fhandle);
    }

    public function editObserver($model,$data){
        $observerDirectory = ucwords($model)."Observer";
        $fname = base_path("App/Observers/".$observerDirectory.".php");      
        $fhandle = fopen($fname,"r");
        $content = fread($fhandle,filesize($fname));
        $observerStString = [($data->creating_o)==""?"//code-creating":" ".$data->creating_o, 
                            ($data->created_o)==""?"//code-created":" ".$data->created_o,
                            ($data->updating_o)==""?"//code-updating":" ".$data->updating_o,
                            ($data->updated_o)==""?"//code-updated":" ".$data->updated_o,
                            ($data->deleting_o)==""?"//code-deleting":" ".$data->deleting_o,
                            ($data->deleted_o)==""?"//code->deleted":" ".$data->deleted_o,
                            ($data->restoring_o)==""?"//code-restoring":" ".$data->restoring_o,
                            ($data->restored_o)==""?"//code-restored":" ".$data->restored_o
                        ];
        $observerConstans = ["//code-creating","//code-created","//code-updating","//code-updated","//code-deleting","//code-deleted","//code-restoring","//code-restored"];
        $observerText = str_replace($observerConstans, $observerStString, $content); 
        $fhandle = fopen($fname,"w");
        fwrite($fhandle,$observerText);     
        fclose($fhandle);  
    }

   

}