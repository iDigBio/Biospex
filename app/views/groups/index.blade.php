@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group')
@stop

{{-- Content --}}
@section('content')
<div class="row">
{{ Breadcrumbs::render('groups') }}
    <div class="col-md-10 col-md-offset-1">
    <h3>{{ trans('groups.your_groups') }}</h3>
    <p>{{ trans('groups.group_explained') }}</p>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <th>@lang('pages.name')</th>
                    <th>@lang('groups.group_options')</th>
                </thead>
                <tbody>
                @foreach ($groups as $group)
                    <tr>
                        <td>{{ $group->name }}</td>
                        <td>
                            <button title="@lang('buttons.viewTitle')" class="btn btn-default btn-info btn-xs" type="button" onClick="location.href='{{ URL::action('GroupsController@show', [$group->id]) }}'"><span class="glyphicon glyphicon-eye-open"></span> @lang('buttons.view')</button>
                            <button title="@lang('buttons.editTitle')" class="btn btn-default btn-warning btn-xs" {{ ($group->user_id == $user->id || $isSuperUser) ? '' : 'disabled' }} onClick="location.href='{{ action('GroupsController@edit', array($group->id)) }}'"><span class="glyphicon glyphicon-cog"></span> @lang('buttons.edit')</button>
                            <button title="@lang('buttons.inviteTitle')" class="btn btn-default btn-primary btn-xs" type="button" onClick="location.href='{{ URL::action('groups.invites.index', [$group->id]) }}'"><span class="glyphicon glyphicon-user"></span> @lang('buttons.invite')</button>
                            <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-xs" {{ ($group->user_id == $user->id  || $isSuperUser) ? '' : 'disabled' }} type="button" data-method="delete" href="{{ URL::action('GroupsController@destroy', array($group->id)) }}"><span class="glyphicon glyphicon-remove-circle"></span> @lang('buttons.delete')</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-10 col-md-offset-1">
        <h4>{{ trans('groups.group_make') }}</h4>
        <button title="@lang('buttons.createTitleG')" class="btn btn-success" onClick="location.href='{{ URL::action('GroupsController@create') }}'"><span class="glyphicon glyphicon-plus"></span> @lang('buttons.create')</button>
    </div>
</div>
<!--  
	The delete button uses Resftulizer.js to restfully submit with "Delete".  The "action_confirm" class triggers an optional confirm dialog.
	Also, I have hardcoded adding the "disabled" class to the Admin group - deleting your own admin access causes problems.
-->
@stop

