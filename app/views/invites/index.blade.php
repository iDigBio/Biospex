@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_invite')
@stop

{{-- Content --}}
@section('content')


        <div class="jumbotron">
        <h4>Group:</h4>
        <h2>{{ $group->name }}</h2>
        
        </div>
        
        


<div class="well clearfix">
    {{ Form::open(array('action' => array('InvitesController@store', $group->id), 'class' => 'form-inline')) }}
    <legend>{{ trans('groups.invite_title', ['group' => $group->name]) }}</legend>
    <p>{{ trans('groups.invite_explained') }}</p>
    <div class="row">
        <div class="input-group col-xs-8 {{ ($errors->has('emails')) ? 'has-error' : '' }}">
            {{ Form::text('emails', null, array('class' => 'form-control', 'placeholder' => trans('groups.invite_emails'))) }}
            {{ ($errors->has('emails') ? $errors->first('emails') : '') }}
            <span class="input-group-btn">
            <span class="glyphicon glyphicon-envelope"></span> 
            {{ Form::submit(trans('buttons.invite'), array('class' => 'btn btn-primary')) }}
            </span>
        </div>
    </div>
    <div class="form-group row">
        <em>{{ trans('groups.separate_emails') }} </em>
    </div>
    {{ Form::close() }}
</div>

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
                        <button class="btn btn-primary btn-xs" type="button" href="{{ URL::route('groups.invites.resend', [$invite->group_id, $invite->id]) }}'" data-token="{{ Session::getToken() }}" data-method="post"><span class="glyphicon glyphicon-envelope"></span> @lang('buttons.resend')</button>
                        <button class="btn btn-default btn-danger action_confirm btn-xs" href="{{ URL::route('groups.invites.destroy', [$invite->group_id, $invite->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="glyphicon glyphicon-remove-circle"></span> @lang('buttons.delete')</button></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

<!--
	The delete button uses Resftulizer.js to restfully submit with "Delete".  The "action_confirm" class triggers an optional confirm dialog.
	Also, I have hardcoded adding the "disabled" class to the Admin group - deleting your own admin access causes problems.
-->
@stop

