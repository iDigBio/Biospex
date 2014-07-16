@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_view')
@stop

{{-- Content --}}
@section('content')
<h4>{{ $group->name }}</h4>
<div class="well clearfix">
    <div class="table-responsive">
        <table class="table table-striped table-hover dataTable">
            <thead>
            <tr>
                <th>{{ trans('groups.group_owner') }}</th>
                <th>{{ trans('users.users') }}</th>
                <th>{{ trans('projects.projects') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{ HTML::mailto($group->owner->email, $group->owner->first_name.' '.$group->owner->last_name) }}</td>
                <td>
                    <ul>
                        @foreach ($group->users as $user)
                        <li>{{ HTML::mailto($user->email, $user->first_name.' '.$user->last_name) }}</li>
                        @endforeach
                    </ul>
                </td>
                <td><ul>
                        @foreach ($group->projects as $project)
                        <li>{{ HTML::linkAction('ProjectsController@show', $project->title, [$group->id, $project->id]) }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    @if ($viewPermissions)
	<div class="col-md-10">
	    <strong>{{ trans('pages.permissions') }}:</strong>
	    <ul>
	    	@foreach ($group->permissions as $key => $value)
	    		<li>{{ str_replace('_', ' ', ucfirst($key)) }}</li>
	    	@endforeach
	    </ul>
	</div>
    @endif
	<div class="col-md-2">
		<button class="btn btn-warning" onClick="location.href='{{ action('GroupsController@edit', array($group->id)) }}'">@lang('buttons.edit')</button>
	</div> 
</div>
@stop
