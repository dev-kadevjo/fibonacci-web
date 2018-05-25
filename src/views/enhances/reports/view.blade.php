@extends('voyager::master')

@section('page_title', __('fibonacci.reports.view'))

@section('page_header')
  <div class="page-title">
    <i class="voyager-data"></i>
    {{ __('fibonacci.reports.view') }}
  </div>    
  @include('voyager::multilingual.language-selector')
@stop

@section('css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.2/daterangepicker.min.css" integrity="sha256-DnG3ryf8FsLvOmNjwO9S4+Fpju6QECDbhLbWCtpn7Bc=" crossorigin="anonymous" />
  <link rel="stylesheet" href="{{ asset('css/reports.css') }}">
@stop

@section('content')
  @foreach ($data as $chart)
    <div style="max-width:1100px; max-height: 500px; position: relative;margin: 0 auto 70px auto;">
      @if($chart->source !== 'db')
        <form action="." method="POST" id="filter-chart{{ $chart->id }}" class="filter-chart form-inline" uuid="{{ $chart->id }}">
          <div class="form-group mx-sm-3 mb-2">
            <label for="daterange{{ $chart->id }}" class="sr-only">Date range:</label>
            <input type="text" name="daterange{{ $chart->id }}" placeholder="Range" class="form-control input-range" />
          </div>
          <button type="submit" class="btn btn-primary mb-2">Filter</button>
          <div class="spin-loader lds-dual-ring hidden"></div>          
        </form>
      @endif
      <canvas id="Chart{{ $chart->id }}" width="1100" height="500"></canvas>
    </div>
  @endforeach  
@stop

@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.2/moment.min.js" integrity="sha256-L3S3EDEk31HcLA5C6T2ovHvOcD80+fgqaCDt2BAi92o=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.2/daterangepicker.min.js" integrity="sha256-GcPXfs/1xqyFfkipEaD0ELCQILrZNxynCSNeOoVqdpY=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js" integrity="sha256-CfcERD4Ov4+lKbWbYqXD6aFM9M51gN4GUEtDhkWABMo=" crossorigin="anonymous"></script>
<script>
  var chartData = {!! json_encode($data) !!};
</script>
<script src="{{ asset('js/reports.js') }}"></script>
@stop