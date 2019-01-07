@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.groups')
@stop

{{-- Content --}}
@section('content')

    <div class="jumbotron">
        <h3>{{ trans('pages.groups') }}
            <button title="@lang('pages.createTitleG')" class="btn btn-success"
                    onClick="location.href='{{ route('admin.groups.create') }}'"><span
                        class="fa fa-plus fa-lg"></span> @lang('pages.create')</button>
        </h3>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h3>{{ trans('pages.active') }}</h3>
            @include('front.groups.partials.group-table')
        </div>
    </div>
@stop

