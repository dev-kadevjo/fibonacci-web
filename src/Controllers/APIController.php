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
            $this->middleware('auth:api')->only( $this->makeSecure($slug) );
        }
    }

    public function getSlug(Request $request)
    {
        if (isset($this->slug)) {
            $slug = $this->slug;
        } else {
            $slug = explode ('/',Route::getFacadeRoot()->current()->uri())[1];

            //$slug = explode('.', $request->route()->getName())[1];
        }
        return $slug;
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
            if($restrict && in_array($key, $restrict))
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
}
