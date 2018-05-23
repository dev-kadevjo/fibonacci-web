@extends('voyager::master')

@section('page_title', __('fibonacci.reports.view'))

@section('page_header')
  <div class="page-title">
    <i class="voyager-data"></i>
    {{ __('fibonacci.reports.view') }}
  </div>    
  @include('voyager::multilingual.language-selector')
@stop


@section('content')
  @foreach ($data as $chart)
    <div style="max-width:1100px; max-height: 500px; position: relative;margin: 0 auto 20px auto;">
      <canvas id="Chart{{ $chart->id }}" width="1100" height="500"></canvas>
    </div>
  @endforeach  
@stop

@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js" integrity="sha256-CfcERD4Ov4+lKbWbYqXD6aFM9M51gN4GUEtDhkWABMo=" crossorigin="anonymous"></script>
<script>
  var chartData = {!! json_encode($data) !!};
</script>
<script src="{{ asset('js/reports.js') }}"></script>
@stop