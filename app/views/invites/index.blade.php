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
    {{ Form::open(array('action' => array('InvitesController@store', $group->id), 'class' => 'form-inline')) }}
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
<div class="well clearfix">
    <div class="col-md-10 col-md-offset-1">
        <h4>@lang('groups.existing_invites')</h4>
        @if ( ! empty($invites->first()))
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>@lang('groups.email')</th>
                    <th class="nowrap">@lang('groups.invite_options')</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($invites as $invite)
                <tr>
                    <td>{{ $invite->email }} </td>
                    <td class="nowrap">
                        <button class="btn btn-primary" type="button" href="{{ URL::route('groups.invites.resend', [$invite->group_id, $invite->id]) }}'" data-token="{{ Session::getToken() }}" data-method="post">@lang('buttons.resend')</button>
                        <button class="btn btn-default btn-danger action_confirm" href="{{ URL::route('groups.invites.destroy', [$invite->group_id, $invite->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete">@lang('buttons.delete')</button></td>
                    <td></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
<!--
	The delete button uses Resftulizer.js to restfully submit with "Delete".  The "action_confirm" class triggers an optional confirm dialog.
	Also, I have hardcoded adding the "disabled" class to the Admin group - deleting your own admin access causes problems.
-->
@stop

