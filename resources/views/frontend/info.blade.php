@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.server_info')}}
@stop

{{-- Content --}}
@section('content')
<div class="well">
    {{ $info }}
</div>
@stop