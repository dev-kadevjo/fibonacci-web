<?php

namespace Kadevjo\Fibonacci\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;
use Kadevjo\Fibonacci\Models\ApiConfig;
use TCG\Voyager\Http\Controllers\Controller as BaseVoyagerController;

class ManageAPIController extends BaseVoyagerController
{
    use BreadRelationshipParser;

    // API Functions
    public function addAPI(Request $request, $table) {
        $this->authorize('browse_database');
        $data = new ApiConfig;
        $newRow = $data->makeJson(null);
        return view('fibonacci::enhances.api.edit-add-api', compact('table', 'newRow'));
    }

    public function storeAPI(Request $request) {
        $this->authorize('browse_database');
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
            //$this->createObserver($nameModel);
            $this->editModelRW(ucwords($nameModel));

            $data = $newRow->save()
                ? $this->alertSuccess(__('voyager.database.success_created_api'))
                : $this->alertError(__('voyager.database.error_creating_api'));

            return redirect()->route('voyager.bread.index')->with($data);
        } catch (Exception $e) {
            return redirect()->route('voyager.bread.index')->with($this->alertException($e, 'Saving Failed'));
        }
    }

    public function addEditAPI($table) {
        $this->authorize('browse_database');

        $dataRow = ApiConfig::where('table_name', '=', $table)->orderBy('created_at', 'desc')->first();
        return view('fibonacci::enhances.api.edit-add-api', compact('table', 'dataRow'));
    }

    public function updateAPI(Request $request, $id) {
        $this->authorize('browse_database');
        try {
            $targetRow = ApiConfig::where('table_name', '=', ($request->all())['table_name'])->first();
            $targetRow->config = $targetRow->makeJson($request->all());
            $targetRow->creating_o  = ($request->all())['creating_o'];
            $targetRow->created_o  = ($request->all())['created_o'];
            $targetRow->updating_o  = ($request->all())['updating_o'];
            $targetRow->updated_o  = ($request->all())['updated_o'];
            $targetRow->deleting_o  = ($request->all())['deleting_o'];
            $targetRow->deleted_o  = ($request->all())['deleted_o'];
            $targetRow->restoring_o  = ($request->all())['restoring_o'];
            $targetRow->restored_o  = ($request->all())['restored_o'];
            $nameModel = Str::singular($targetRow->table_name);

            $data = $targetRow->save()
                ? $this->alertSuccess(__('voyager.database.success_update_api', ['datatype' => ($request->all())['table_name']]))
                : $this->alertError(__('voyager.database.error_updating_api'));

            return redirect()->route('voyager.bread.index')->with($data);
        } catch (Exception $e) {
            return back()->with($this->alertException($e, __('voyager.generic.update_failed')));
        }
    }

    public function deleteAPI($table) {
        $this->authorize('browse_database');

        // Remove API config
        $delete = ApiConfig::where('table_name', '=', $table)->first();

        $data = $delete->delete()
            ? $this->alertSuccess(__('voyager.database.success_remove_api', ['datatype' => $table]))
            : $this->alertError(__('voyager.database.error_removing_api'));


        return redirect()->route('voyager.bread.index')->with($data);
    }

    public function editModelRW($name){
        $fname = base_path("app/models/".$name.".php");
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
}
