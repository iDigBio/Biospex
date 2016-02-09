@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent

@stop

{{-- Content --}}
@section('content')
    <div class="jumbotron">
        <h2>Expeditions</h2>
    </div>

    <div class="row">
        <div class="col-md-12">
            {{ var_dump($results) }}
        </div>
    </div>
@stop