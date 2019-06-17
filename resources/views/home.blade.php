@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">Stock Checker</div>

        <div class="card-body" id="stock_quote">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <stock
            ref="stock"
            crud-fetch-url ='{{ $controller->action("fetch_objects", [true]) }}'
            base-url = '{{ $controller->action() }}'
            :crud-field-configs = "{{ json_encode($controller->field_configs()) }}"
            crud-model-name = "{{ $controller->model_name() }}"
            ></stock>
        </div>
    </div>
@endsection
