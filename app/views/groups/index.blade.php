@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group')
@stop

{{-- Content --}}
@section('content')
<h4>{{ trans('groups.your_groups') }}</h4>
<p>{{ trans('groups.group_explained') }}</p>
<div class="row">
    <div class="col-md-10">
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
                            <button class="btn btn-default btn-info" type="button" onClick="location.href='{{ URL::action('GroupsController@show', [$group->id]) }}'">@lang('buttons.view')</button>
                            <button class="btn btn-default btn-warning" {{ ($group->user_id == $user->id || $isSuperUser) ? '' : 'disabled' }} onClick="location.href='{{ action('GroupsController@edit', array($group->id)) }}'">@lang('buttons.edit')</button>
                            <button class="btn btn-default btn-danger action_confirm" {{ ($group->user_id == $user->id  || $isSuperUser) ? '' : 'disabled' }} type="button" data-method="delete" href="{{ URL::action('GroupsController@destroy', array($group->id)) }}">@lang('buttons.delete')</button>
                            <button class="btn btn-default btn-primary" type="button" onClick="location.href='{{ URL::action('groups.invites.index', [$group->id]) }}'">@lang('buttons.invite')</button>
                         </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-10">
        <h4>{{ trans('groups.group_make') }}</h4>
        <button class="btn btn-primary" onClick="location.href='{{ URL::action('GroupsController@create') }}'">@lang('buttons.create')</button>
    </div>
</div>
<!--  
	The delete button uses Resftulizer.js to restfully submit with "Delete".  The "action_confirm" class triggers an optional confirm dialog.
	Also, I have hardcoded adding the "disabled" class to the Admin group - deleting your own admin access causes problems.
-->
@stop

