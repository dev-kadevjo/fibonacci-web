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

class APIController extends BaseVoyagerController
{
    use BreadRelationshipParser;

    public function __construct(Request $request){

        if(count($request->segments())>0){
            $slug = $this->getSlug($request);
            $this->middleware('auth:api')->only( $this->makeSecure($slug) );
        }
    }
    
    // Browse
    public function index(Request $request){
        
        $slug = $this->getSlug($request);

        if( !$this->checkAPI($slug,'browse') ) return  response()->json(array('error'=>'Action not allowed') );
        
        $modelClass = $this->getModel($slug);
        
        if($request->has('filter'))
        {
            $filters = json_decode($request->input('filter'));
            
            $query = $modelClass::query();

            foreach ($filters as $filter)
            {
                call_user_func_array( array($query, $filter->method), $filter->parameters );
            }
            
            $response = $query->get();
        }
        else
        {
            $response = $modelClass::get();
        }

        return $response;
    }

    // Read
    public function show(Request $request, $id){
        $slug = $this->getSlug($request);
        if( !$this->checkAPI($slug,'read') ) return response()->json( array('error'=>'Action not allowed') );
        
        $modelClass = $this->getModel($slug);
        $model = $modelClass::find($id);
        return $model??response()->json(array('error'=>'WHOOPS! Nothing here, please try again'));  
    }

    // Udate
    public function update(Request $request, $id){ 
        $slug = $this->getSlug($request); // table name
        if( !$this->checkAPI($slug,'edit') ) return response()->json( array('error'=>'Action not allowed') );

        $modelClass = $this->getModel($slug);
        $update = $modelClass::find($id);

        $requestData = $request->all();
        // Check for images to upload
        foreach ($requestData as $key => $value) {
            if( $request->hasFile($key) ){
                $requestData[$key] = $this->upload($key, $value, $slug);
                // Delete old image in storage
                $oldImage = $update->where('id', $id)->first();                
                if (Storage::disk(config('voyager.storage.disk'))->exists($oldImage->{$key})) {
                    Storage::disk(config('voyager.storage.disk'))->delete($oldImage->{$key});
                }
            }
        }
        
        $restrict = config('voyager.restrict');
        foreach ($requestData as $key => $value) {
            if( in_array($key, $restrict))
                unset($requestData[$key]);
        }
        
        if( $update->forceFill($requestData)->save() ){
            return response()->json( array('state'=>'success') );
        }else{
            return response()->json( array('state'=>'error') );
        }               
    }
    
    // Insert    
    public function store(Request $request){
        $slug = $this->getSlug($request);
        if( !$this->checkAPI($slug,'add') ) return response()->json( array('error'=>'Action not allowed') );

        $modelClass = $this->getModel($slug);
        $requestData = $request->all();

        // Check for images to upload
        foreach ($requestData as $key => $value) {
            if( $request->hasFile($key) ){
                $requestData[$key] = $this->upload($key, $value, $slug); 
            }            
        }

        $restrict = config('voyager.restrict');
        foreach ($requestData as $key => $value) {
            if($restrict && in_array($key, $restrict))
                unset($requestData[$key]);
        }
        
        if( $modelClass->forceFill($requestData)->save() ){
            return response()->json( array('state'=>'success') );
        }else{
            return response()->json( array('state'=>'error') );
        }           
    }

    private function upload($name,$image,$slug){
        $file = $image;
        $dataType = DataType::where('name','=',$slug)->first();        
        $folder = $dataType ? $dataType->slug : $slug;
        $path = $folder.'/'.date('FY').'/';

        $filename = Str::random(20);
        // Make sure the filename does not exist, if it does, just regenerate
        while (Storage::disk(config('voyager.storage.disk'))->exists($path.$filename.'.'.$file->getClientOriginalExtension())) {
            $filename = Str::random(20);
        }
        $fullPath = $path.$filename.'.'.$file->getClientOriginalExtension();       
  
        $resize_width = 1800;
        $resize_height = null;
        $image = Image::make($file)->resize(
            $resize_width,
            $resize_height,
            function (Constraint $constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            }
        )->encode($file->getClientOriginalExtension(), 75);
        Storage::disk(config('voyager.storage.disk'))->put($fullPath, (string) $image, 'public');

        return $fullPath;
    }

    // Delete
    public function destroy(Request $request, $id){
        $slug = $this->getSlug($request);        
        if( !$this->checkAPI($slug,'delete') ) return response()->json( array('error'=>'Action not allowed') );
        
        $modelClass = $this->getModel($slug);
        $remove = $modelClass::find($id);

        if( $remove->delete() ){
            return response()->json( array('state'=>'success') );
        }else{
            return response()->json( array('state'=>'error') );
        } 
    }


    // Get model by table name
    private function getModel($table){
        $name = studly_case(str_singular($table));
        try {
            $entity = "App\\".$name;
            $modelClass =  \App::make($entity);
        } catch (Exception $e) {
            $entity = "TCG\Voyager\Models\\".$name;
            $modelClass =  \App::make($entity);
        }
        return $modelClass;
    }
    // Check if api was configured
    private function checkAPI($table,$action){
        $api = ApiConfig::where('table_name','=',$table)->first();
        if(!$api) return false;

        $options = json_decode($api->config);
        return $options->{$action}->enable;        
    }
    // Create array to auth:api
    private function makeSecure($table){
        $secure = array();
        $api = ApiConfig::where('table_name','=',$table)->first();
        if($api){            
            $options = json_decode($api->config);

            if( $options->browse->secure ) array_push($secure, 'index');
            if( $options->read->secure ) array_push($secure, 'show');
            if( $options->edit->secure ) array_push($secure, 'update');
            if( $options->add->secure ) array_push($secure, 'store');
            if( $options->delete->secure ) array_push($secure, 'destroy');
        } 
        return $secure;
    }



    /**
     * Remove translations, images and files related to a BREAD item.
     *
     * @param \Illuminate\Database\Eloquent\Model $dataType
     * @param \Illuminate\Database\Eloquent\Model $data
     *
     * @return void
     */
    protected function cleanup($dataType, $data)
    {
        // Delete Translations, if present
        if (is_bread_translatable($data)) {
            $data->deleteAttributeTranslations($data->getTranslatableAttributes());
        }

        // Delete Images
        $this->deleteBreadImages($data, $dataType->deleteRows->where('type', 'image'));

        // Delete Files
        foreach ($dataType->deleteRows->where('type', 'file') as $row) {
            $files = json_decode($data->{$row->field});
            if ($files) {
                foreach ($files as $file) {
                    $this->deleteFileIfExists($file->download_link);
                }
            }
        }
    }

    /**
     * Delete all images related to a BREAD item.
     *
     * @param \Illuminate\Database\Eloquent\Model $data
     * @param \Illuminate\Database\Eloquent\Model $rows
     *
     * @return void
     */
    public function deleteBreadImages($data, $rows)
    {
        foreach ($rows as $row) {
            $this->deleteFileIfExists($data->{$row->field});

            $options = json_decode($row->details);

            if (isset($options->thumbnails)) {
                foreach ($options->thumbnails as $thumbnail) {
                    $ext = explode('.', $data->{$row->field});
                    $extension = '.'.$ext[count($ext) - 1];

                    $path = str_replace($extension, '', $data->{$row->field});

                    $thumb_name = $thumbnail->name;

                    $this->deleteFileIfExists($path.'-'.$thumb_name.$extension);
                }
            }
        }

        if ($rows->count() > 0) {
            event(new BreadImagesDeleted($data, $rows));
        }
    }

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
            $content = str_replace("{", '{  
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