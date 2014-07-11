@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.home')}}
@stop

{{-- Content --}}
@section('content')

<div class="jumbotron">
    <div class="container">
        <h1>{{trans('pages.sitename')}}</h1>
        <p>{{trans('pages.site_description')}}</p>
    </div>
</div>

@stop