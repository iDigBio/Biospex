@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_invite')
@stop

{{-- Content --}}
@section('content')
<h4>{{ trans('groups.invite_title', ['group' => $group->name]) }}</h4>
<p>{{ trans('groups.invite_explained') }}</p>
<div class="well clearfix">
    {{ Form::open(array('action' => array('GroupsController@sendInvite', $group->id), 'class' => 'form-inline')) }}
    <div class="row">
        <div class="input-group col-xs-8 {{ ($errors->has('emails')) ? 'has-error' : '' }}">
            {{ Form::text('emails', null, array('class' => 'form-control', 'placeholder' => trans('groups.invite_emails'))) }}
            {{ ($errors->has('emails') ? $errors->first('emails') : '') }}
            <span class="input-group-btn">
            {{ Form::submit(trans('buttons.invite'), array('class' => 'btn btn-primary')) }}
            </span>
        </div>
    </div>
    <div class="form-group row">
        <em>{{ trans('groups.separate_emails') }} </em>
    </div>
    {{ Form::close() }}
</div>
<!--
	The delete button uses Resftulizer.js to restfully submit with "Delete".  The "action_confirm" class triggers an optional confirm dialog.
	Also, I have hardcoded adding the "disabled" class to the Admin group - deleting your own admin access causes problems.
-->
@stop

