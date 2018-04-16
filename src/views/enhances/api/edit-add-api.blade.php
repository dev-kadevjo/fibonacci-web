@extends('voyager::master')

@section('page_title', __('fibonacci.database.edit_api_for_table', ['table' => $table]))

@section('page_header')
    <div class="page-title">
        <i class="voyager-data"></i>
        {{ __('fibonacci.database.edit_api_for_table', ['table' => $table]) }}
    </div>    
    @include('voyager::multilingual.language-selector')
@stop


@section('content')
    <div class="page-content container-fluid" id="voyagerBreadEditAdd">
        <div class="row">
            <div class="col-md-12">

                <form action="@if (!isset($dataRow)){{ route('fibonacci.database.api.store', $table) }}@else{{ route('fibonacci.database.api.update', $dataRow->id) }}@endif"
                      method="POST" role="form">
                    <input type="hidden" value="{{ $table }}" name="table_name">
                    
                    @if(isset($dataRow))                        
                        {{ method_field("PUT") }}                    
                    @endif
                    <!-- CSRF TOKEN -->
                    {{ csrf_field() }}

                    <div class="panel panel-primary panel-bordered">
                        <div class="panel-heading">
                            <h3 class="panel-title panel-icon"><i class="voyager-window-list"></i>{{ ucfirst($table) }}  {{ __('fibonacci.database.api_info') }}</h3>
                            <div class="panel-actions">
                                <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>

                        <div class="panel-body">
                            <div class="row fake-table-hd">
                                <div class="col-xs-4">{{ __('fibonacci.database.visibility') }}</div>                                
                                <div class="col-xs-4">{{ __('fibonacci.database.api_enable') }}</div>
                                <div class="col-xs-4">{{ __('fibonacci.database.api_secure') }}</div>                                
                            </div>

                            <div id="bread-items">
                                
                                @if (isset($dataRow))
                                    @foreach (json_decode($dataRow->config) as $key => $value)
                                    <div class="row row-dd">                                                                        

                                        <div class="col-xs-4">
                                            <h4><strong>{{ ucfirst($key) }}</strong></h4>                                        
                                        </div>
                                        <div class="col-xs-4">
                                            <input type="checkbox"
                                                   id="allow_{{ $key }}"
                                                   name="allow_{{ $key }}" @if($value->enable){{ 'checked="checked"' }}@endif>                                        
                                        </div>
                                        <div class="col-xs-4">
                                            <input type="checkbox"
                                                   id="secure_{{ $key }}"
                                                   name="secure_{{ $key }}" @if($value->secure){{ 'checked="checked"' }}@endif>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    @foreach (json_decode($newRow) as $key => $value)
                                        <div class="row row-dd">
                                            <div class="col-xs-4"><h4><strong>{{ ucfirst($key) }}</strong></h4></div>
                                            <div class="col-xs-4"><input type="checkbox" id="allow_{{ $key }}" name="allow_{{ $key }}"></div>
                                            <div class="col-xs-4"><input type="checkbox" id="secure_{{ $key }}" name="secure_{{ $key }}"></div>
                                        </div>                                        
                                    @endforeach
                                @endif

                            </div>

                        </div><!-- .panel-body -->
                    </div><!-- .panel -->


                    <div class="panel panel-primary panel-bordered">

                        <div class="panel-heading">
                            <h3 class="panel-title panel-icon"><i class="voyager-window-list"></i> {{ ucfirst($table) }} {{ __('fibonacci.database.api_code') }}</h3>
                            <div class="panel-actions">
                                <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>

                        <div class="panel-body">
                            <!--<div class="col-xs-12">
                                <div class="form-group">
                                    @if (isset($dataRow))
                                        <label><input type="radio" {{ ($dataRow->execution==1)?'checked':'' }} name="exc" value="1"> Before</label><br>
                                        <label><input type="radio" {{ ($dataRow->execution==2)?'checked':'' }} name="exc" value="2"> After</label>
                                    @else
                                        <label><input type="radio" checked name="exc" value="1"> Before</label><br>
                                        <label><input type="radio" name="exc" value="2"> After</label>
                                    @endif
                                </div>
                                <textarea class="custom_code form-control" name="code" id="code" rows="15">
                                    @if (isset($dataRow))
                                        {{ trim($dataRow->custom_code) }}
                                    @endif
                                </textarea>
                            </div>-->
                            <div class="col-xs-12">
                                <div class="form-group">
                                <!--<label class="label label-default">Time:</label> -->
                                <select class="btn btn-primary" id = "timeSelect">
                                    <option value="Before">Before</option>
                                    <option value="After">After</option>
                                </select>                                
                               <!-- <label class="label label-default">Action:</label> -->
                                <select class="btn btn-info" id="actioSelect" >
                                    <option value="Create">Create</option>
                                    <option value="Update">Update</option>
                                    <option value="Delete">Delete</option>
                                    <option value="Restore">Restore</option>
                                </select>                               
                                
                                <button type="button" class="btn btn-primary" id ="actionButtomadd">Add Action</button>
                                </div>
                               
                            </div>
                            
                            <div class="col-xs-12 grpActive" id="AfterCreate"   {{ empty($dataRow->creating_o)!=false?' style=display:none':' style=display:block' }}>  
                                <div class="form-group">
                                <br>
                                 <h4>After Create</h4>    
                                </div>                         
                                <textarea class="custom_code form-control" name="creating_o" id="creating_o" rows="15">
                                    @if (isset($dataRow))
                                        {{ trim($dataRow->creating_o) }}
                                    @endif
                                </textarea>
                            </div>
                            <div class="col-xs-12 grpActive" id="BeforeCreate" {{ empty($dataRow->created_o)!=false?' style=display:none':' style=display:block' }}>  
                                <div class="form-group">
                                <br>
                                 <h4>Before Create</h4>    
                                </div>                         
                                <textarea class="custom_code form-control" name="created_o" id="created_o" rows="15">
                                    @if (isset($dataRow))
                                        {{ trim($dataRow->created_o) }}
                                    @endif
                                </textarea>
                            </div>
                            <div class="col-xs-12 grpActive" id = "AfterUpdate"  {{ empty($dataRow->updating_o)!=false?' style=display:none':' style=display:block' }}>  
                                <div class="form-group">
                                <br>
                                 <h4>After Update</h4>    
                                </div>                         
                                <textarea class="custom_code form-control" name="updating_o" id="updating_o" rows="15">
                                    @if (isset($dataRow))
                                        {{ trim($dataRow->updating_o) }}
                                    @endif
                                </textarea>
                            </div>
                            <div class="col-xs-12 grpActive" id= "BeforeUpdate" {{ empty($dataRow->updated_o)!=false?' style=display:none':' style=display:block' }}>  
                                <div class="form-group">
                                <br>
                                 <h4>Before Update</h4>    
                                </div>                         
                                <textarea class="custom_code form-control" name="updated_o" id="updated_o" rows="15">
                                    @if (isset($dataRow))
                                        {{ trim($dataRow->updated_o) }}
                                    @endif
                                </textarea>
                            </div>
                            <div class="col-xs-12 grpActive" id="AfterDelete" {{ empty($dataRow->deleting_o)!=false?' style=display:none':' style=display:block' }}>  
                                <div class="form-group">
                                <br>
                                 <h4>After Delete</h4>    
                                </div>                         
                                <textarea class="custom_code form-control" name="deleting_o" id="deleting_o" rows="15">
                                    @if (isset($dataRow))
                                        {{ trim($dataRow->deleting_o) }}
                                    @endif
                                </textarea>
                            </div>
                            <div class="col-xs-12 grpActive" id = "BeforeDelete"  {{ empty($dataRow->deleted_o)!=false?' style=display:none':' style=display:block' }}>  
                                <div class="form-group">
                                <br>
                                 <h4>Before Delete</h4>    
                                </div>                         
                                <textarea class="custom_code form-control" name="deleted_o" id="deleted_o" rows="15">
                                    @if (isset($dataRow))
                                        {{ trim($dataRow->deleted_o) }}
                                    @endif
                                </textarea>
                            </div>
                            <div class="col-xs-12 grpActive" id = "AfterRestore"  {{ empty($dataRow->restoring_o)!=false?' style=display:none':' style=display:block' }}>  
                                <div class="form-group">
                                <br>
                                 <h4>After Restore</h4>    
                                </div>                         
                                <textarea class="custom_code form-control" name="restoring_o" id="restoring_o" rows="15">
                                    @if (isset($dataRow))
                                        {{ trim($dataRow->restoring_o) }}
                                    @endif
                                </textarea>
                            </div>
                            <div class="col-xs-12 grpActive" id = "BeforeRestore" {{ empty($dataRow->restored_o)!=false? ' style=display:none ':' style=display:block ' }} >  
                                <div class="form-group">
                                <br>
                                 <h4>Before Restore</h4>    
                                </div>                         
                                <textarea class="custom_code form-control" name="restored_o" id="restored_o" rows="15">
                                    @if (isset($dataRow))
                                        {{ trim($dataRow->restored_o) }}
                                    @endif
                                </textarea>
                            </div>
                            
                            
                        </div><!-- .panel-body -->
                    </div><!-- .panel -->


                    <button type="submit" class="btn pull-right btn-primary">{{ __('voyager.generic.submit') }}</button>

                </form>
            </div><!-- .col-md-12 -->
        </div><!-- .row -->
    </div><!-- .page-content -->

@stop

@section('javascript')
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.0/themes/smoothness/jquery-ui.css">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>

    <script>
        window.invalidEditors = [];
        var validationAlerts = $('.validation-error');
        validationAlerts.hide();
        $(function () {            
            /**
             * Reorder items
             */
            reOrderItems();

            $('#bread-items').disableSelection();

            $('[data-toggle="tooltip"]').tooltip();

            $('.toggleswitch').bootstrapToggle();

            $('textarea[data-editor]').each(function () {
                var textarea = $(this),
                mode = textarea.data('editor'),
                editDiv = $('<div>').insertBefore(textarea),
                editor = ace.edit(editDiv[0]),
                _session = editor.getSession(),
                valid = false;
                textarea.hide();

                // Validate JSON
                _session.on("changeAnnotation", function(){
                    valid = _session.getAnnotations().length ? false : true;
                    if (!valid) {
                        if (window.invalidEditors.indexOf(textarea.attr('id')) < 0) {
                            window.invalidEditors.push(textarea.attr('id'));
                        }
                    } else {
                        for(var i = window.invalidEditors.length - 1; i >= 0; i--) {
                            if(window.invalidEditors[i] == textarea.attr('id')) {
                               window.invalidEditors.splice(i, 1);
                            }
                        }
                    }
                });

                // Use workers only when needed
                editor.on('focus', function () {
                    _session.setUseWorker(true);
                });
                editor.on('blur', function () {
                    if (valid) {
                        textarea.siblings('.validation-error').hide();
                        _session.setUseWorker(false);
                    } else {
                        textarea.siblings('.validation-error').show();
                    }
                });

                _session.setUseWorker(false);

                editor.setAutoScrollEditorIntoView(true);
                editor.$blockScrolling = Infinity;
                editor.setOption("maxLines", 30);
                editor.setOption("minLines", 4);
                editor.setOption("showLineNumbers", false);
                editor.setTheme("ace/theme/github");
                _session.setMode("ace/mode/json");
                if (textarea.val()) {
                    _session.setValue(JSON.stringify(JSON.parse(textarea.val()), null, 4));
                }

                _session.setMode("ace/mode/" + mode);

                // copy back to textarea on form submit...
                textarea.closest('form').on('submit', function (ev) {
                    if (window.invalidEditors.length) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        validationAlerts.hide();
                        for (var i = window.invalidEditors.length - 1; i >= 0; i--) {
                            $('#'+window.invalidEditors[i]).siblings('.validation-error').show();
                        }
                        toastr.error('{{ __('voyager.json.invalid_message') }}', '{{ __('voyager.json.validation_errors') }}', {"preventDuplicates": true, "preventOpenDuplicates": true});
                    } else {
                        if (_session.getValue()) {
                            // uglify JSON object and update textarea for submit purposes
                            textarea.val(JSON.stringify(JSON.parse(_session.getValue())));
                        }
                    }
                });
            });

        });

        function reOrderItems(){
            $('#bread-items').sortable({
                handle: '.handler',
                update: function (e, ui) {
                    var _rows = $('#bread-items').find('.row_order'),
                        _size = _rows.length;

                    for (var i = 0; i < _size; i++) {
                        $(_rows[i]).val(i+1);
                    }
                },
                create: function (event, ui) {
                    sort();
                }
            });
        }

        function sort() {
            var sortableList = $('#bread-items');
            var listitems = $('div.row.row-dd', sortableList);

            listitems.sort(function (a, b) {
                return (parseInt($(a).find('.row_order').val()) > parseInt($(b).find('.row_order').val()))  ? 1 : -1;
            });
            sortableList.append(listitems);

        }

        /********** Relationship functionality **********/

       $(function () {
            $('.rowDrop').each(function(){
                populateRowsFromTable($(this));
            });

            $('.relationship_type').change(function(){
                if($(this).val() == 'belongsTo'){
                    $(this).parent().parent().find('.relationshipField').show();
                    $(this).parent().parent().find('.relationshipPivot').hide();
                    $(this).parent().parent().find('.relationship_key').show();
                    $(this).parent().parent().find('.hasOneMany').removeClass('flexed');
                    $(this).parent().parent().find('.belongsTo').addClass('flexed');
                } else if($(this).val() == 'hasOne' || $(this).val() == 'hasMany'){
                    $(this).parent().parent().find('.relationshipField').show();
                    $(this).parent().parent().find('.relationshipPivot').hide();
                    $(this).parent().parent().find('.relationship_key').hide();
                    $(this).parent().parent().find('.hasOneMany').addClass('flexed');
                    $(this).parent().parent().find('.belongsTo').removeClass('flexed');
                } else {
                    $(this).parent().parent().find('.relationshipField').hide();
                    $(this).parent().parent().find('.relationshipPivot').css('display', 'flex');
                    $(this).parent().parent().find('.relationship_key').hide();
                }
            });

            $('.btn-new-relationship').click(function(){
                $('#new_relationship_modal').modal('show');
            });

            relationshipTextDataBinding();

            $('.relationship_table').on('change', function(){
                var tbl_selected = $(this).val();
                var rowDropDowns = $(this).parent().parent().find('.rowDrop');
                $(this).parent().parent().find('.rowDrop').each(function(){
                    console.log('1');
                    $(this).data('table', tbl_selected);
                    populateRowsFromTable($(this));
                });
            });

            $('.voyager-relationship-details-btn').click(function(){
                $(this).toggleClass('open');
                if($(this).hasClass('open')){
                    $(this).parent().parent().find('.voyager-relationship-details').slideDown();
                } else {
                    $(this).parent().parent().find('.voyager-relationship-details').slideUp();
                }
            });

        });

        function populateRowsFromTable(dropdown){
            var tbl = dropdown.data('table');
            var selected_value = dropdown.data('selected');
            if(tbl.length != 0){
                $.get('{{ route('voyager.database.index', [], false) }}/' + tbl, function(data){
                    $(dropdown).empty();
                    for (var option in data) {
                       $('<option/>', {
                        value: option,
                        html: option
                        }).appendTo($(dropdown));
                    }

                    if($(dropdown).find('option[value="'+selected_value+'"]').length > 0){
                        $(dropdown).val(selected_value);
                    }
                });
            }
        }

        function relationshipTextDataBinding(){
            $('.relationship_display_name').bind('input', function() {
                $(this).parent().parent().parent().find('.label_relationship p').text($(this).val());
            });
            $('.relationship_table').on('change', function(){
                var tbl_selected_text = $(this).find('option:selected').text();
                $(this).parent().parent().find('.label_table_name').text(tbl_selected_text);
            });
            $('.relationship_table').each(function(){
                var tbl_selected_text = $(this).find('option:selected').text();
                $(this).parent().parent().find('.label_table_name').text(tbl_selected_text);
            });
        }


        /********** End Relationship Functionality **********/
    </script>
     <script>
      $(document).ready(function(){
            $('#actionButtomadd').click(function(){  
                var tim = $( "#timeSelect" ).val();
                var acti = $( "#actioSelect" ).val();
                $('#'+tim+acti) .attr("style", "display:block");
            });      
        });
    </script>
@stop