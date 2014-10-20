@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.create') }} {{ trans('projects.project') }}
@stop

{{-- Content --}}
@section('content')
{{ Breadcrumbs::render('projects.create') }}
<h4>{{ trans('pages.create') }} {{ trans('projects.project') }}</h4>
<div class="well">
    {{ Form::open(array(
    'action' => array('ProjectsController@store'),
    'method' => 'post',
    'enctype' => 'multipart/form-data',
    'class' => 'form-horizontal',
    'role' => 'form'
    )) }}

    @include('partials.project-fields')

    <div class="form-group">
        <button title="@lang('buttons.addTargetTitle')" id="add_target" class="btn btn-default" type="button">@lang('buttons.target_add')</button>
        <button title="@lang('buttons.removeTargetTitle')" id="remove_target" class="btn btn-default" type="button">@lang('buttons.target_remove')</button>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {{ Form::hidden('targetCount', 0, array('id' => 'targetCount')) }}
            {{ Form::submit(trans('buttons.create'), array('class' => 'btn btn-primary')) }}
            {{ Form::button(trans('buttons.cancel'), ['class' => 'btn btn-large btn-primary btn-danger', 'onClick' => "location.href='$cancel'"]) }}
        </div>
    </div>
    {{ Form::close()}}
</div>
@stop