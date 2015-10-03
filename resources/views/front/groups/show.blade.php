@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('groups.group_view')
@stop

{{-- Content --}}
@section('content')
{!! Breadcrumbs::render('groups.show', $group) !!}
    <div class="jumbotron">
        <h4>Group:</h4>
        <h2>{{ $group->name }}</h2>
    </div>
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
                <td>{!! Html::mailto($group->owner->email, $group->owner->profile->first_name.' '.$group->owner->profile->last_name) !!}</td>
                <td>
                    <ul>
                        @foreach ($group->users as $user)
                        <li>{!! Html::mailto($user->email, $user->profile->first_name.' '.$user->profile->last_name) !!}</li>
                        @endforeach
                    </ul>
                </td>
                <td><ul>
                        @foreach ($group->projects as $project)
                        <li>{!! link_to_route('projects.show', $project->title, $project->id) !!}</li>
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
		<button title="@lang('buttons.editTitle')" class="btn btn-warning btn-sm" onClick="location.href='{{ route('groups.edit', array($group->id)) }}'"><span class="fa fa-cog fa-lrg"></span> @lang('buttons.edit')</button>
	</div> 

@stop
