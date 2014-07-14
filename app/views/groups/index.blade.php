@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group')
@stop

{{-- Content --}}
@section('content')
<h4>Available Groups</h4>
<div class="row">
  <div class="col-md-10 col-md-offset-1">
	<div class="table-responsive">
		<table class="table table-striped table-hover">
			<thead>
				<th>@lang('pages.name')</th>
				<th>@lang('groups.group_options')</th>
                <th>@lang('projects.project_options')</th>
			</thead>
			<tbody>
            @foreach ($groups as $group)
                @if (in_array($group->id, array(1,2)) && ! $isSuperUser)
                @else
                    <tr>
                        <td><a href="groups/{{ $group->id }}">{{ $group->name }}</a></td>
                        <td>
                            <button class="btn btn-default" {{ ($group->user_id == $user->id || $isSuperUser) ? '' : 'disabled' }} onClick="location.href='{{ action('GroupsController@edit', array($group->id)) }}'">@lang('buttons.edit')</button>
                            <button class="btn btn-default btn-danger action_confirm" {{ ($group->user_id == $user->id  || $isSuperUser) ? '' : 'disabled' }} type="button" data-method="delete" href="{{ URL::action('GroupsController@destroy', array($group->id)) }}">@lang('buttons.delete')</button>
                         </td>
                        @if (($group->name == 'Admins' || $group->name == 'Users'))
                        <td></td>
                        @else
                        <td>
                            <button class="btn btn-primary" onClick="location.href='{{ URL::route('groups.projects.create', [$group->id]) }}'">@lang('pages.create')</button>
                        </td>
                        @endif
                    </tr>
                @endif
			@endforeach
			</tbody>
		</table> 
	</div>
	 <button class="btn btn-primary" onClick="location.href='{{ URL::action('GroupsController@create') }}'">@lang('buttons.create')</button>
   </div>
</div>
<!--  
	The delete button uses Resftulizer.js to restfully submit with "Delete".  The "action_confirm" class triggers an optional confirm dialog.
	Also, I have hardcoded adding the "disabled" class to the Admin group - deleting your own admin access causes problems.
-->
@stop

