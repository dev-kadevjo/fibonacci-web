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
use Illuminate\Support\Facades\Route;

class APIController extends BaseVoyagerController
{
    use BreadRelationshipParser;

    public function __construct(Request $request){
        if(count($request->segments())>0){
            $slug = $this->getSlug($request);
            $this->middleware('auth:'.config('fibonacci.guards'))->only( $this->makeSecure($slug) );
        }
    }

    public function getSlug(Request $request)
    {
        if (isset($this->slug)) {
            $slug = $this->slug;
        } else {
            $slug = explode ('/',Route::getFacadeRoot()->current()->uri())[1];
        }
        return $slug;
    }
    // Browse
    public function index(Request $request){
        $slug = $this->getSlug($request);

        if( !$this->checkAPI($slug,'browse') ) return  response()->json(array('error'=>'Action not allowed'),405);

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
        if( !$this->checkAPI($slug,'read') ) return response()->json( array('error'=>'Action not allowed'),405 );

        $modelClass = $this->getModel($slug);
        $model = $modelClass::find($id);
        return $model??response()->json(array('error'=>'WHOOPS! Nothing here, please try again'),400);
    }

    // Edit
    public function update(Request $request, $id){
        $slug = $this->getSlug($request); // table name
        if( !$this->checkAPI($slug,'edit') ) return response()->json( array('error'=>'Action not allowed'),405 );

        $modelClass = $this->getModel($slug);
        $update = $modelClass::find($id);

        $requestData = $request->all();

        $relations = $modelClass->modelsChildsToStore;

        $rules = array();

        $messages = array();

        if (!is_null($modelClass->rules) &&
            is_array($modelClass->rules))
        {
            $rules = array_merge($rules,$modelClass->rules);
        }

        if (!is_null($modelClass->messages) && is_array($modelClass->messages))
        {
            $messages = array_merge($messages,$modelClass->messages);
        }

        $validator = validator($requestData, $rules, $messages);

        if ($validator->fails())
        {
            return response()->json(["errors"=>$validator->errors()], 400);
        }

        
        $restrict = $relations;
        $relationsData = [];
        foreach ($requestData as $key => $value) {
            if($restrict && key_exists($key, $restrict))
            {
                $relationsData[$key]=$requestData[$key];
                unset($requestData[$key]);
            }
        }

        $restrict = config('voyager.restrict');
        foreach ($requestData as $key => $value) {
            if($restrict && in_array($key, $restrict))
                unset($requestData[$key]);
        }

        if( $update->forceFill($requestData)->save() ){
            return $update;
        }else{
            return response()->json( array('state'=>'error'), 400 );
        }
    }

    // Add
    public function store(Request $request){
        $slug = $this->getSlug($request);
        if( !$this->checkAPI($slug,'add') ) return response()->json( array('error'=>'Action not allowed'),405 );

        $modelClass = $this->getModel($slug);

        $requestData = $request->all();

        $relations = $modelClass->modelsChildsToStore;

        $rules = array();

        $messages = array();

        if (!is_null($modelClass->rules) &&
            is_array($modelClass->rules))
        {
            $rules = array_merge($rules,$modelClass->rules);
        }

        if (!is_null($modelClass->messages) && is_array($modelClass->messages))
        {
            $messages = array_merge($messages,$modelClass->messages);
        }

        $validator = validator($requestData, $rules, $messages);

        if ($validator->fails())
        {
            return response()->json(["errors"=>$validator->errors()],400);
        }

        $restrict = $relations;
        $relationsData = [];
        foreach ($requestData as $key => $value) {
            if($restrict && key_exists($key, $restrict))
            {
                $relationsData[$key]=$requestData[$key];
                unset($requestData[$key]);
            }
        }

        if(count($requestData)==0)
            return response()->json( array('error'=>'Bad request'),400 );

        if( $modelClass->forceFill($requestData)->save() ){

            // added with relation;
            if(is_array($relations)){
                $parentId=$modelClass->id;
                $modelClass->with($relations);
                foreach ($relations as $relation => $relationMetadata) {
                
                    if($relationMetadata){                   
                        $childModelClass = $this->getModel($relation);
                        //dump($relationsData);
                        if( array_key_exists($relation, $relationsData) && !is_null($relationsData[$relation])){

			            $relationsData[$relation][$relationMetadata["parentId"]]=$modelClass->id;
                    //dd($relationsData);
			
                    		$childModelClass->forceFill($relationsData[$relation])->save();
			            }
                    }               
                }
            }
            //
            return $modelClass;
        }else{
            return response()->json( array('state'=>'error'),400 );
        }
    }

    // Delete
    public function destroy(Request $request, $id){
        $slug = $this->getSlug($request);
        if( !$this->checkAPI($slug,'delete') ) return response()->json( array('error'=>'Action not allowed'),405 );

        $modelClass = $this->getModel($slug);
        $remove = $modelClass::find($id);

        if( $remove->delete() ){
            return response()->json( array('state'=>'success') );
        }else{
            return response()->json( array('state'=>'error'),400 );
        }
    }

    // Get model by table name
    private function getModel($table){
        $name = studly_case(str_singular($table));
        try {
            $entity = "App\\".$name;
            $modelClass =  \App::make($entity);
        }catch (Exception $e) {
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

    public function uploadResource(Request $request)
    {
        if($request->hasFile('resource') && $request->file('resource')->isValid())
        {
            $file = $request->file('resource');
            $type = $request->input('type');
            $path = $type.'/'.date('FY');
            $fullPath = \Storage::disk('public')->put($path,$file);
            return response()->json(["resource"=>Voyager::image($fullPath)]);
        }
        else
        {
            return response()->json(["error"=>"Ocurrio un error"],400);
        }
    }
}
