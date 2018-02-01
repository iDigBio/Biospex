@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('groups.groups')
@stop

{{-- Content --}}
@section('content')

    <div class="jumbotron">
        <h3>{{ trans('groups.groups') }}
            <button title="@lang('buttons.createTitleG')" class="btn btn-success"
                    onClick="location.href='{{ route('webauth.groups.create') }}'"><span
                        class="fa fa-plus fa-lg"></span> @lang('buttons.create')</button>
        </h3>
    </div>
    <div class="row">
        <div class="col-md-6">
            <h3>{{ trans('pages.active') }}</h3>
            @include('frontend.groups.partials.group-table')
        </div>
        <div class="col-md-6">
            <h3>{{ trans('pages.trash') }}</h3>
            @include('frontend.groups.partials.group-trashed-table')
        </div>
    </div>
@stop

