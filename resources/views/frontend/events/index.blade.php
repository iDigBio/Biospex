@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('events.events')
@stop

{{-- Content --}}
@section('content')
    <div class="jumbotron">
        <h3>{!! trans('events.events') !!}
            <button title="@lang('buttons.createTitleEv')" class="btn btn-success"
                    onClick="location.href='{{ route('webauth.events.create') }}'"><span
                        class="fa fa-plus fa-lg"></span> @lang('buttons.create')</button>
        </h3>
    </div>
    @include('frontend.events.partials.event-list')
@stop