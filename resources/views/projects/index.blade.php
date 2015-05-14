@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('projects.projects')
@stop

{{-- Content --}}
@section('content')
{{ Breadcrumbs::render('projects') }}
<div class="row">
    @if ( ! $groups->isEmpty())
    <div class="col-md-10 col-md-offset-1">
        <h3>{{ trans('projects.projects') }}</h3>
        <button title="@lang('buttons.createTitleP')" class="btn btn-success" onClick="location.href='{{ URL::route('projects.create') }}'"><span class="glyphicon glyphicon-plus"></span>  @lang('buttons.create')</button>
    </div>
    <div class="col-md-10 col-md-offset-1">
		<div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th></th>
                    <th>@lang('pages.title')</th>
                    <th>@lang('pages.description')</th>
                    <th>@lang('pages.group')</th>
                    <th class="nowrap">@lang('projects.project_options')</th>
                </tr>
                </thead>
                <tbody>
				@foreach ($groups as $group)
					@foreach ($group->Projects as $project)
					<tr>
						<td><span id="collapse{{ $project->id }}" class="glyphicon glyphicon-folder-close pointer" data-toggle="collapse" data-target="#{{ $project->id }}"></span></td>
						<td><a href="{{ URL::route('projects.show', [$project->id]) }}">{{ $project->title }}</a></td>
						<td>{{ $project->description_short }} </td>
						<td><a href="{{ URL::route('groups.show', [$group->id]) }}">{{ $group->name }}</a></td>
						<td class="nowrap">
							<button title="@lang('buttons.viewTitle')" class="btn btn-info btn-xs" type="button" onClick="location.href='{{ URL::route('projects.show', [$project->id]) }}'"><span class="glyphicon glyphicon-eye-open"></span> @lang('buttons.view')</button>
							<button title="@lang('buttons.dataTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ URL::route('projects.data', [$project->id]) }}'"><span class="glyphicon glyphicon-plus-sign"></span> @lang('buttons.data')</button>
							<button title="@lang('buttons.dataViewTitle')" class="btn btn-info btn-xs" type="button" onClick="location.href='{{ URL::route('projects.subjects.show', [$project->id]) }}'"><span class="glyphicon glyphicon-search"></span> @lang('buttons.dataView')</button>
							<button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ URL::route('projects.duplicate', [$project->id]) }}'"><span class="glyphicon glyphicon-share-alt"></span> @lang('buttons.duplicate')</button>
							<button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button" onClick="location.href='{{ URL::route('projects.edit', [$project->id]) }}'"><span class="glyphicon glyphicon-cog"></span> @lang('buttons.edit')</button>
							@if ($user->id == $group->user_id || $isSuperUser)
							<button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-xs" href="{{ URL::route('projects.destroy', [$project->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="glyphicon glyphicon-remove-circle"></span> @lang('buttons.delete')</button>
							@endif
						</td>
					</tr>
					<tr>
						<td></td>
						<td colspan="4">
							<span id="{{ $project->id }}" class="collapse out"></span></td>
					</tr>
					@endforeach
				@endforeach
                </tbody>
            </table>
        </div>
    </div>
	@else
	<div class="col-md-10 col-md-offset-1">
        <h3>{{ trans('welcome.welcome') }}</h3>
    </div>
    <div class="col-md-10 col-md-offset-1">
    
        {{ trans('welcome.intro') }}
		{{ trans('welcome.ready') }}
	    <button class="btn btn-success" title="@lang('buttons.createTitleP')" onClick="location.href='{{ URL::route('projects.create') }}'"><span class="glyphicon glyphicon-plus"></span>  @lang('buttons.create')</button>
  
	</div>
	@endif
    
</div>
@stop