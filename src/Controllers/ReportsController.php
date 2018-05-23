<?php

namespace Kadevjo\Fibonacci\Controllers;

use Illuminate\Http\Request;
use TCG\Voyager\Http\Controllers\Controller as BaseVoyagerController;

use GuzzleHttp\Client;
use Spatie\Analytics\Period;
use Spatie\Url\Url;
use Analytics;
use Carbon\Carbon;
use Kadevjo\Fibonacci\Models\Report;

class ReportsController extends BaseVoyagerController{
  public function all(Request $request){
    $data = Report::all();
    foreach ($data as $value) {      
      if($value->source === 'analytics'){
        $value->query = $this->getData($value->query);
      } else {
        $value->query = 'consulta';
      }
    }

    return view('fibonacci::enhances.reports.view', compact('data'));
  }

  public function manage(Request $request){
    // Check permission
    //$this->authorize('browse', 'Kadevjo\Fibonacci\Models\Report');    
    $Rows = Report::orderBy('updated_at','desc')->get();    
    return view('fibonacci::enhances.reports.manage', compact('Rows'));
  }

  public function store(Request $request) {
      //Voyager::canOrFail('browse_reports');        
      try {
        $newRow = new Report;
        $newRow->name = $request->name;
        $newRow->type = $request->type;
        $newRow->source = $request->source;
        $newRow->fields = $request->fields;
        $newRow->query = $request->query_explorer;

        $data = $newRow->save()
          ? $this->alertSuccess(__('fibonacci.reports.success_created'))
          : $this->alertError(__('fibonacci.reports.error_creating'));
        
        return redirect(config('voyager.prefix').'/reports/manage')->with($data);
      } catch (Exception $e) {
        return redirect(config('voyager.prefix').'/reports/manage')->with($this->alertException($e, 'Saving Failed'));
      }
    }  

    public function update(Request $request) {
      //Voyager::canOrFail('browse_reports');
      try {            
        $targetRow = Report::findOrFail( $request->v_id );
        $targetRow->name = $request->v_name;
        $targetRow->type = $request->v_type;
        $targetRow->source = $request->v_source;
        $targetRow->fields = $request->v_fields;
        $targetRow->query = $request->v_query;

        $data = $targetRow->save()
          ? $this->alertSuccess(__('fibonacci.reports.success_update', ['datatype' => 'table_name']))
          : $this->alertError(__('fibonacci.reports.error_updating'));
        
        return redirect(config('voyager.prefix').'/reports/manage')->with($data);
      } catch (Exception $e) {
        return back()->with($this->alertException($e, __('voyager.generic.update_failed')));
      }
    }

    public function delete($id) {
      //Voyager::canOrFail('browse_reports');
      // Remove Report
      $delete = Report::findOrFail( $id );
      
      $data = $delete->delete()
        ? $this->alertSuccess(__('fibonacci.reports.success_remove'))
        : $this->alertError(__('fibonacci.reports.error_removing'));
      
      return redirect(config('voyager.prefix').'/reports/manage')->with($data);
    }

    public function getData($query,$Sdate=null,$Edate=null){
      $url = Url::fromString($query);
      $result = explode('&', $url->getQuery());       
      foreach ($result as $value) {
          $tmp = explode('=', $value);
          $others[ $tmp[0] ] = $tmp[1];
      }                
      $startDate = ($Sdate) ? new Carbon($Sdate) : new Carbon($others['start-date']);
      $endDate = ($Edate) ? new Carbon($Edate) : new Carbon($others['end-date']);
      $period = Period::create($startDate, $endDate);
      $metrics = $others['metrics'];

      // Removing unnecessary data
      unset($others['ids']);
      unset($others['start-date']);
      unset($others['end-date']);
      unset($others['metrics']);

      $analyticsData = Analytics::performQuery($period,$metrics,$others);

      return $analyticsData->rows;
    }

    public function ajax(Request $request){
      $id = $request->uuid;
      $sdate = $request->sdate;
      $edate = $request->edate;
      $data = Report::find($id);

      $response = array(
        "id" => $id,
        "type" => $data->type,
        "data" => $this->getData($data->query,$sdate,$edate)
      );

      return json_encode($response);
    }
}