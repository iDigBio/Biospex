@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.help')}}
@stop

{{-- Content --}}
@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <h3>{{ trans('welcome.welcome') }}</h3>
    </div>
    <div class="col-md-10 col-md-offset-1">
            {{ trans('welcome.intro') }}
    </div>
</div>
@stop