@extends('voyager::master')

@section('page_title', __('voyager.generic.viewing').' Binnacle')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="icon voyager-file-text"></i> Binnacle
        </h1>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Author</th>                                       
                                        <th>Entity</th>                                       
                                        <th>Source</th>                                       
                                        <th>Action</th>                                       
                                        <th>Table id</th>                                       
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($Rows as $data)
                                    <tr>
                                        <td>{{ $data->author }}</td>
                                        <td>{{ $data->entity }}</td>
                                        <td>{{ $data->source }}</td>
                                        <td>{{ $data->action }}</td>
                                        <td>{{ $data->id_table }}</td>
                                        <td>{{ $data->created_at }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="{{ voyager_asset('lib/css/responsive.dataTables.min.css') }}">
@stop

@section('javascript')

<script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>
<script>
    var table = $('#dataTable').DataTable({!! json_encode(
    array_merge([
        "order" => [],
        "language" => __('voyager.datatable'),
    ],
    config('voyager.dashboard.data_tables', []))
, true) !!});
</script>

@stop
