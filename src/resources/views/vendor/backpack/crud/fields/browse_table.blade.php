@php
$max = isset($field['max']) && (int) $field['max'] > 0 ? $field['max'] : -1;
$min = isset($field['min']) && (int) $field['min'] > 0 ? $field['min'] : -1;
$item_name = strtolower(isset($field['entity_singular']) && !empty($field['entity_singular']) ? $field['entity_singular'] : $field['label']);

$items = old($field['name']) ? (old($field['name'])) : (isset($field['value']) ? ($field['value']) : (isset($field['default']) ? ($field['default']) : ''));

// make sure not matter the attribute casting
// the $items variable contains a properly defined JSON
if (is_string($items))
    $items = json_decode($items) ?: [];

$items = json_encode(array_filter($items));

@endphp

<div ng-app="backPackTableApp" ng-controller="tableControllerDouble" @include('crud::inc.field_wrapper_attributes') >

    @if( $field['label'] != '' )
    <label>{!! $field['label'] !!}</label>
    @endif

    @include('crud::inc.field_translatable_icon')

    <input class="array-json" type="hidden" id="{{ $field['name'] }}" name="{{ $field['name'] }}">

    <div class="array-container form-group">

        <table class="table table-bordered table-striped m-b-0" ng-init="field = '#{{ $field['name'] }}'; items = {{ $items }}; max = {{$max}}; min = {{$min}}; maxErrorTitle = '{{trans('backpack::crud.table_cant_add', ['entity' => $item_name])}}'; maxErrorMessage = '{{trans('backpack::crud.table_max_reached', ['max' => $max])}}'">


            <thead>
                <tr>
                    @foreach ( $field['columns'] as $column)
                        <th style="font-weight: 600!important; {{ isset($column['width']) ? 'width: '.$column['width'].'px' : '' }}">
                            {{$column['label'] }}
                        </th>
                        @if($column['type'] == 'browse')
                        <th style="width: 30px" class="text-center" ng-if="max == -1 || max > 1"> {{-- <i class="fa fa-cloud-upload"></i> --}} </th>
                        @endif
                    @endforeach
                    <th style="width: 30px" class="text-center" ng-if="max == -1 || max > 1"> {{-- <i class="fa fa-sort"></i> --}} </th>
                    <th style="width: 30px" class="text-center" ng-if="max == -1 || max > 1"> {{-- <i class="fa fa-trash"></i> --}} </th>
                </tr>
            </thead>

            <tbody ui-sortable="sortableOptions" ng-model="items" class="table-striped">

                <tr ng-repeat="item in items" class="array-row">

                        @foreach ($field['columns'] as $key => $column)
                        <td>
                            @if( $column['type'] == 'input' || $column['type'] == 'browse' )
                            <input class="form-control input-sm" type="text" ng-model="item.{{ $key }}" />

                            @elseif( $column['type'] == 'textarea' )
                            <textarea class="form-control input-sm" type="text" ng-model="item.{{ $key }}"></textarea>

                            @elseif( $column['type'] == 'select' )
                            <select class="form-control input-sm" ng-model="item.{{ $key }}">
                                @foreach($column['options'] as $key => $value)
                                <option value="{{ $key }}">{{ __($value) }}</option>
                                @endforeach
                            </select>

                            @endif
                        </td>

                        @if($column['type'] == 'browse')
                            @php
                            $mimes = "''";
                            if (isset($column['mimes'])) {
                                if (is_array($column['mimes'])) {
                                    $column['mimes'] = join("', '", $column['mimes']);
                                }

                                $mimes = "['" . $column['mimes'] . "']";
                            }
                            @endphp
                            <td>
                                <button
                                    type="button"
                                    ng-click="browseItem('{{ $field['name'] }}', {{ $mimes }}, item,'{{ $key }}',$index)"
                                    class="btn btn-sm btn-default popup_selector" />
                                <i class="fa fa-cloud-upload"></i></button>
                            </td>
                        @endif

                        @endforeach

                    <td ng-if="max == -1 || max > 1">
                        <span class="btn btn-sm btn-default sort-handle"><span class="sr-only">sort item</span>
                        <i class="fa fa-sort" role="presentation" aria-hidden="true"></i></span>
                    </td>
                    <td ng-if="max == -1 || max > 1">
                        <button ng-hide="min > -1 && $index < min" class="btn btn-sm btn-default" type="button" ng-click="removeItem(item);"><span class="sr-only">delete item</span>
                        <i class="fa fa-trash" role="presentation" aria-hidden="true"></i></button>
                    </td>

                </tr>

            </tbody>

        </table>

        <div class="array-controls btn-group m-t-10">
            <button ng-if="max == -1 || items.length < max" class="btn btn-sm btn-default" type="button" ng-click="addItem()"><i class="fa fa-plus"></i> {{trans('backpack::crud.add')}} {{ $item_name }}</button>
        </div>

    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <link href="{{ asset('vendor/backpack/colorbox/example2/colorbox.css') }}" rel="stylesheet" type="text/css" />
        <style>
            table textarea {
                resize: vertical;
            }
            #cboxContent, #cboxLoadedContent, .cboxIframe {
                background: transparent;
            }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script type="text/javascript" src="{{ asset('vendor/backpack/colorbox/jquery.colorbox-min.js') }}"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.5.8/angular.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/angular-ui-sortable/0.14.3/sortable.min.js"></script>
        <script>
            window.angularApp = window.angularApp || angular.module('backPackTableApp', ['ui.sortable'], function($interpolateProvider){
                $interpolateProvider.startSymbol('<%');
                $interpolateProvider.endSymbol('%>');
            });

            window.angularApp.controller('tableControllerDouble', function($scope){

                $scope.sortableOptions = {
                    handle: '.sort-handle'
                };

                $scope.addItem = function(){

                    if( $scope.max > -1 ){
                        if( $scope.items.length < $scope.max ){
                            var item = {};
                            $scope.items.push(item);
                        } else {
                            new PNotify({
                                title: $scope.maxErrorTitle,
                                text: $scope.maxErrorMessage,
                                type: 'error'
                            });
                        }
                    }
                    else {
                        var item = {};
                        $scope.items.push(item);
                    }
                }

                $scope.removeItem = function(item){
                    var index = $scope.items.indexOf(item);
                    $scope.items.splice(index, 1);
                }

                $scope.browseItem = function(elem, mimes, item, field_selected ,index){

                    var args = {
                            'index': index,
                            'element': elem,
                            'mimes': mimes,
                            'field_selected':field_selected
                        },

                        triggerUrl = "{{ url(config('elfinder.route.prefix').'/popup') }}/" + JSON.stringify(args);
                    $.colorbox({
                        href: triggerUrl,
                        fastIframe: true,
                        iframe: true,
                        width: '80%',
                        height: '80%'
                    });
                }

                $scope.$watch('items', function(a, b){

                    if( $scope.min > -1 ){
                        while($scope.items.length < $scope.min){
                            $scope.addItem();
                        }
                    }

                    if( typeof $scope.items != 'undefined' && $scope.items.length ){

                        if( typeof $scope.field != 'undefined'){
                            if( typeof $scope.field == 'string' ){
                                $scope.field = $($scope.field);
                            }
                            $scope.field.val( angular.toJson($scope.items) );
                        }
                    }
                }, true);

                if( $scope.min > -1 ){
                    for(var i = 0; i < $scope.min; i++){
                        $scope.addItem();
                    }
                }
            });

            angular.element(document).ready(function(){
                angular.forEach(angular.element('[ng-app]'), function(ctrl){
                    var ctrlDom = angular.element(ctrl);
                    if( !ctrlDom.hasClass('ng-scope') ){
                        angular.bootstrap(ctrl, [ctrlDom.attr('ng-app')]);
                    }
                });
            })

            // function to update the file selected by elfinder
            // Check if the caller is not from a table object
            function processSelectedFile(filePath, requestingField) {
                let isTable = true;

                try {
                    var request = JSON.parse(requestingField);
                } catch(e) {
                    isTable =  false;
                }

                if(!isTable){
                    $('#' + requestingField).val(filePath);
                }else{
                    var elem = document.getElementById(request.element);
                    var scope = angular.element(elem).scope();
                    scope.items[request.index][request.field_selected]  = filePath.replace(/\\/g, '/');
                    scope.$digest();
                }
            }

        </script>

    @endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
