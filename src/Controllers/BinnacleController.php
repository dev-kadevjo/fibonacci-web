<?php

namespace Kadevjo\Fibonacci\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Http\Controllers\Controller as BaseVoyagerController;

use Kadevjo\Fibonacci\Models\Binnacle;

class BinnacleController extends BaseVoyagerController{
  public function binnacle(){
    // Check permission
    $Rows = Binnacle::all();    
    return view('fibonacci::enhances.binnacle', compact('Rows'));
  }
}