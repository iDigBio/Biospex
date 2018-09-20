@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('pages.projects')
@stop

{{-- Content --}}
@section('content')
<div class="jumbotron">
    <h3>{!! trans('pages.projects') !!}
        <button title="@lang('pages.createTitleP')" class="btn btn-success"
                onClick="location.href='{{ route('admin.projects.create') }}'"><span
                    class="fa fa-plus fa-lg"></span> @lang('pages.create')</button>
    </h3>
</div>
@include('frontend.projects.partials.project-table')
@stop