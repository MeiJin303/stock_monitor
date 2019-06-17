@extends('layouts.app')

@php
$list_row_links_num = count($controller->links_for_list_row());
if ($controller->can_show())    $list_row_links_num++;
if ($controller->can_edit())    $list_row_links_num++;
if ($controller->can_remove())  $list_row_links_num++;
if ($controller->can_copy())    $list_row_links_num++;
@endphp

@section('content')
<crud-vuetable
fetch-url ='{{ $controller->action("fetch_objects", [true]) }}'
base-url = '{{ $controller->action() }}'
list_row_links_num = {{ $list_row_links_num }}
:can-search = {{ $controller->can_search() }}
:can-filter = {{ $controller->can_filter() }}
:can-add = {{ $controller->can_add() }}
:can-show = {{ $controller->can_show() }}
:can-edit = {{ $controller->can_edit() }}
:can-remove = {{ $controller->can_remove() }}
:can-copy = {{ $controller->can_copy() }}
:field-configs = "{{ json_encode($controller->field_configs()) }}"
model-name = "{{ $controller->model_name() }}"
></crud-vuetable>
@endsection
