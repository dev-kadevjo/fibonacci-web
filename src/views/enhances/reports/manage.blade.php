@extends('voyager::master')

@section('page_title', __('voyager.generic.viewing').' Reports')

@section('page_header')
    <div class="page-title">
        <i class="voyager-data"></i>
        {{ __('fibonacci.reports.title', ['name' => 'Reports']) }}
        <a href="#!" class="btn-sm btn-success" id="new_report">New Report</a>
    </div>    
    @include('voyager::multilingual.language-selector')
@stop

@section('css')
  <link rel="stylesheet" href="{{ asset('css/reports.css') }}">
@stop

@section('content')
    <div class="page-content container-fluid" id="voyagerBreadEditAdd">
      <div class="row">            
              
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
          <div class="table-responsive">
            <table id="dataTable" class="table table-hover">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Source</th>
                  <th>Report</th>
                </tr>
              </thead>
              <tbody>                            
                @foreach($Rows as $data)
                <tr>
                  <td class="name">{{ $data->name }}</td>
                  <td class="type">{{ $data->type }}</td>
                  <td class="typeSource">{{ $data->source }}</td>
                  <td>
                    <a data-id="{{ $data->id }}" data-name="{{ $data->name }}" title="Delete Report" class="btn-sm btn-danger delete" href="#!">Delete</a>
                    <a href="#!" class="btn-sm btn-warning view_report">Edit</a>                                    
                    <input type="hidden" class="h_id" value="{{ $data->id }}">
                    <input type="hidden" class="h_query" value="{{ $data->query }}">
                    <input type="hidden" class="h_created" value="{{ $data->created_at }}">
                    <input type="hidden" class="h_updated" value="{{ $data->updated_at }}">
                    <input type="hidden" class="h_fields" value="{{ $data->fields }}">
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
          <form method="post" action="{{ url(config('voyager.prefix').'/reports/update') }}" role="form">
            {{ csrf_field() }}
            <table id="table_view_report">
              <tr>
                <td>Name: </td> <td colspan="3"> <input disabled required type="text" class="v_name form-control" name="v_name" id="v_name"> </td>
              </tr>
              <tr>
                <td>Type: </td> 
                <td> 
                  <select name="v_type" class="v_type select_r" id="v_type" required disabled>
                    <option value="line">Area</option>                                    
                    <option value="horizontalBar">Bar</option>
                    <option value="bar">Column</option>
                    <option value="doughnut">Pie</option>
                  </select>
                </td>
                <td>Source: </td> 
                <td> 
                  <select name="v_source" class="v_source select_r" id="v_source" required disabled>
                    <option value="db">Database</option>
                    <option value="analytics">Google Analytics</option>
                  </select>
                </td>
              </tr>
              <tr>
                <td>Created at: </td> <td><span class="v_created">--</span></td>
                <td>Updated at: </td> <td><span class="v_updated">--</span></td>
              </tr>
              <tr>
                <td>Fields</td>
                <td colspan="3">
                  <input disabled required type="text" class="v_fields form-control" name="v_fields" id="v_fields">
                </td>
              </tr>
              <tr>
                <td colspan="4">Query</td>
              </tr>
              <tr>
                <td colspan="4">
                  <input type="hidden" class="v_id" id="v_id" name="v_id">
                  <textarea disabled required name="v_query" id="v_query" class="v_query form-control" rows="10"></textarea>
                </td>
              </tr>
              <tr>
                <td colspan="4" class="text-right">
                  <button class="v_button btn btn-primary" disabled type="submit">Save</button>
                </td>
              </tr>
            </table>
          </form>
        </div>            
      </div><!-- .row -->

    </div><!-- .page-content -->
    <div class="modal modal-danger fade" tabindex="-1" id="delete_builder_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager.generic.close') }}"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i>  <span id="msg_modal_delete">{!! __('fibonacci.reports.delete_quest', ['table' => '<span id="delete_builder_name"></span>']) !!}</span> </h4>
                </div>
                <div class="modal-footer">
                    <form action="{{ url(config('voyager.prefix').'/reports/delete/') }}" id="delete_builder_form" method="POST">
                        {{ method_field('DELETE') }}
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="submit" class="btn btn-danger" value="{{ __('fibonacci.reports.delete') }}">
                    </form>
                    <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ __('voyager.generic.cancel') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal modal-success fade" tabindex="-1" id="new_builder_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager.generic.close') }}"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i>  <span id="msg_modal_delete">{!! __('fibonacci.reports.new_report', ['table' => '<span id="delete_builder_name"></span>']) !!}</span> </h4>
                </div>
                <div class="modal-footer">
                    <form action="{{ url(config('voyager.prefix').'/reports/store') }}"  method="POST">                        
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="form-group text-left">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Report name" required>
                        </div>
                        <div class="form-group text-left">
                            <label for="type">Type</label>
                            <select name="type" class="type select_r" id="type" required>
                                <option value="line">Area</option>
                                <option value="horizontalBar">Bar</option>
                                <option value="bar">Column</option>
                                <option value="doughnut">Pie</option>
                            </select>
                        </div>
                        <div class="form-group text-left">
                          <label for="source">Source (analytics - BD)</label>
                          <select name="source" class="type select_r" id="source" required>
                            <option value="db">Database</option>
                            <option value="analytics">Google Analytics</option>
                          </select>
                        </div>
                        <div class="form-group text-left">
                            <label for="name">Fields</label>
                            <input type="text" class="form-control" id="fields" name="fields" placeholder="Ex. field, field, field...." required>
                        </div>
                        <div class="form-group text-left">
                            <label for="query_explorer">Query</label>
                            <textarea rows="10" class="form-control" name="query_explorer" id="query_explorer" placeholder="Query" required></textarea>
                        </div>

                        <button type="button" class="btn btn-outline" data-dismiss="modal">{{ __('voyager.generic.cancel') }}</button>
                        <input type="submit" class="btn btn-primary" value="{{ __('fibonacci.reports.create') }}">
                    </form>                    
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop


@section('javascript')
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.0/themes/smoothness/jquery-ui.css">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>

    <!-- DataTables -->        
    <script>
        $(document).ready(function () {            
            var table = $('#dataTable').DataTable({!! json_encode(
                array_merge([
                    "order" => [],
                    "language" => __('voyager.datatable'),
                ],
                config('voyager.dashboard.data_tables', []))
            , true) !!});

            $(".view_report").on('click', function(event){
                event.preventDefault();

                var tr = $(this).parent().parent();
                var val = tr.find('.type').text();
                var valS = tr.find('.typeSource').text();

                var element = document.getElementById('v_type');
                element.value = val;

                $(".v_name").val( tr.find('.name').text() );
                $(".v_type").val( val );
                $(".v_source").val( valS );
                $(".v_created").text( tr.find('.h_created').val() );
                $(".v_updated").text( tr.find('.h_updated').val() );
                $(".v_query").val( tr.find('.h_query').val() );
                $(".v_fields").val( tr.find('.h_fields').val() );
                $(".v_id").val( tr.find('.h_id').val() );

                $('.v_name').removeAttr('disabled');
                $('.v_type').removeAttr('disabled');
                $('.v_source').removeAttr('disabled');
                $('.v_query').removeAttr('disabled');
                $('.v_button').removeAttr('disabled');
                $('.v_fields').removeAttr('disabled');
            });

            $('#dataTable').on('click', '.delete', function (e) {
                e.preventDefault();
                var id = $(this).data('id');
                var name = $(this).data('name');                
                var url = '{{ url(config('voyager.prefix').'/reports/delete') }}';
                $('#delete_builder_name').text(name);
                $('#delete_builder_form')[0].action = url + '/' + id;
                $('#delete_builder_modal').modal('show');
            });

            $("#new_report").on('click', function(e){
                e.preventDefault();
                $('#new_builder_modal').modal('show');
            });
            
        });

    </script>
@stop
