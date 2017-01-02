@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('groups.groups')
@stop

{{-- Content --}}
@section('content')

    <div class="col-md-8 col-md-offset-2">
    <h3>{{ trans('groups.groups') }}
        <button title="@lang('buttons.createTitleG')" class="btn btn-success"
                onClick="location.href='{{ route('web.groups.create') }}'"><span
                    class="fa fa-plus fa-lg"></span> @lang('buttons.create')</button>
    </h3>
    @include('frontend.groups.partials.group-table')
    </div>
    <div class="col-md-8 col-md-offset-2">
    <h3>{{ trans('pages.trash') }}</h3>
    @include('frontend.groups.partials.group-trashed-table')
    </div>
@stop

