@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.notifications')
@stop

{{-- Content --}}
@section('content')
    <h3>{{ trans('pages.notifications') }}</h3>
    @include('frontend.notifications.partials.notification-table')
    <h3>{{ trans('pages.trash') }}</h3>
    @include('frontend.notifications.partials.notification-trashed-table')
@stop