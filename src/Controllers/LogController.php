<?php

namespace Kadevjo\Fibonacci\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Http\Controllers\Controller as BaseVoyagerController;

use Kadevjo\Fibonacci\Models\Log;

class LogController extends BaseVoyagerController{
  public function log(){
    // Check permission
    $Rows = Log::all();    
    return view('fibonacci::enhances.log', compact('Rows'));
  }
}