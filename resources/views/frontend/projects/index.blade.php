@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('projects.projects')
@stop

{{-- Content --}}
@section('content')
<div class="jumbotron">
    <h3>{!! trans('projects.projects') !!}
        <button title="@lang('buttons.createTitleP')" class="btn btn-success"
                onClick="location.href='{{ route('webauth.projects.create') }}'"><span
                    class="fa fa-plus fa-lg"></span> @lang('buttons.create')</button>
    </h3>
</div>
@include('frontend.projects.partials.project-table')
<h3>{!! trans('pages.trash') !!}</h3>
@include('frontend.projects.partials.project-trashed-table')
@stop