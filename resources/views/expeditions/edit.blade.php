@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.edit') }} {{ trans('expeditions.expedition') }}
@stop

{{-- Content --}}
@section('content')
{{ Breadcrumbs::render('projects.expeditions.inside', $expedition) }}
<h2>{{ $expedition->project->title }}</h2>
<h4>{{ trans('pages.edit') }} {{ trans('expeditions.expedition') }}</h4>
<div class="well">
    {{ Form::open(array(
    'action' => array('ExpeditionsController@update', $expedition->project->id, $expedition->id),
    'method' => 'put',
    'class' => 'form-horizontal',
    'role' => 'form'
    )) }}

    @include('partials.expedition-fields', compact('expedition'))

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {{ Form::hidden('project_id', $expedition->project->id) }}
            {{ Form::hidden('id', $expedition->id) }}
            {{ Form::submit(trans('buttons.update'), array('class' => 'btn btn-primary'))}}
			{{ Form::button(trans('buttons.cancel'), ['class' => 'btn btn-large btn-primary btn-danger', 'onClick' => "location.href='$cancel'"]) }}
        </div>
    </div>
    {{ Form::close()}}
</div>

@stop