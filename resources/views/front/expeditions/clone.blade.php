@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.clone') }} {{ trans('expeditions.expedition') }}
@stop

{{-- Content --}}
@section('content')
{{ Breadcrumbs::render('projects.expeditions.inside', $expedition) }}
<h4>{{ trans('projects.project') }}: {{ $expedition->project->title }}</h4>
<h3>{{ trans('pages.clone') }} {{ trans('expeditions.expedition') }}</h3>
<div class="well">
    {{ Form::open(array(
    'action' => array('ExpeditionsController@update', $expedition->project->id),
    'method' => 'post',
    'class' => 'form-horizontal',
    'role' => 'form'
    )) }}

    @include('front.partials.expedition-fields', compact('expedition'))

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {{ Form::hidden('project_id', $expedition->project->id) }}
			{{ Form::hidden('id', $expedition->id) }}
            {{ Form::submit(trans('buttons.create'), array('class' => 'btn btn-primary'))}}
			{{ Form::button(trans('buttons.cancel'), ['class' => 'btn btn-large btn-primary btn-danger', 'onClick' => "location.href='$cancel'"]) }}
        </div>
    </div>
    {{ Form::close()}}
</div>

@stop