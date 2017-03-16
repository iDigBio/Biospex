@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('projects.projects')
@stop

{{-- Content --}}
@section('content')
    @if ( ! $groups->count())
        <div class="col-md-10 col-md-offset-1">
            {!!  trans('html.welcome') !!}
        </div>
        <div class="col-md-10 col-md-offset-1">
        {!! trans('html.new-user-intro') !!}
        <button class="btn btn-success" title="@lang('buttons.createTitleG')"
                onClick="location.href='{{ route('web.groups.create') }}'"><span
                    class="glyphicon glyphicon-plus"></span> @lang('buttons.create')</button>
    </div>
@else
    <h3>{!! trans('projects.projects') !!}
        <button title="@lang('buttons.createTitleP')" class="btn btn-success"
                onClick="location.href='{{ route('web.projects.create') }}'"><span
                    class="fa fa-plus fa-lg"></span> @lang('buttons.create')</button>
    </h3>
    @include('frontend.projects.partials.project-table')
    <h3>{!! trans('pages.trash') !!}</h3>
    @include('frontend.projects.partials.project-trashed-table')
@endif
@stop