@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
@lang('projects.projects')
@stop

{{-- Content --}}
@section('content')
{!! Breadcrumbs::render('projects') !!}
    @if ( ! $groups->isEmpty())
    <div class="jumbotron">
        <h3>{{ trans('projects.projects') }}</h3>
        <button title="@lang('buttons.createTitleP')" class="btn btn-success" onClick="location.href='{{ route('projects.create') }}'"><span class="fa fa-plus fa-lg"></span>  @lang('buttons.create')</button>
    </div>
    <div class="col-md-12">
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
						<td><span id="collapse{{ $project->id }}" class="fa fa-folder fa-lg pointer" data-toggle="collapse" data-target="#{{ $project->id }}"></span></td>
						<td><a href="{{ route('projects.show', [$project->id]) }}">{{ $project->title }}</a></td>
						<td>{{ $project->description_short }} </td>
						<td><a href="{{ route('groups.show', [$group->id]) }}">{{ $group->name }}</a></td>
						<td nowrap>
							<button title="@lang('buttons.viewTitle')" class="btn btn-primary btn-sm" type="button" onClick="location.href='{{ route('projects.show', [$project->id]) }}'"><span class="fa fa-eye fa-lg"></span> @lang('buttons.view')</button>
							<button title="@lang('buttons.dataTitle')" class="btn btn-inverse btn-sm" type="button" onClick="location.href='{{ route('projects.import', [$project->id]) }}'"><span class="fa fa-plus fa-lg"></span> @lang('buttons.data')</button>
							<button title="@lang('buttons.dataViewTitle')" class="btn btn-info btn-sm" type="button" onClick="location.href='{{ route('projects.subjects.show', [$project->id]) }}'"><span class="fa fa-search fa-lg"></span> @lang('buttons.dataView')</button>
							<button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-sm" type="button" onClick="location.href='{{ route('projects.duplicate', [$project->id]) }}'"><span class="fa fa-share-alt fa-lg"></span> @lang('buttons.duplicate')</button>
							<button title="@lang('buttons.editTitle')" class="btn btn-warning btn-sm" type="button" onClick="location.href='{{ route('projects.edit', [$project->id]) }}'"><span class="fa fa-cog fa-lg"></span> @lang('buttons.edit')</button>
							@if ($user->id == $group->user_id || $isSuperUser)
							<button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-sm" href="{{ route('projects.destroy', [$project->id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="fa fa-remove fa-lg"></span> @lang('buttons.delete')</button>
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
    
        {!! trans('welcome.intro') !!}
		{!! trans('welcome.ready') !!}
	    <button class="btn btn-success" title="@lang('buttons.createTitleP')" onClick="location.href='{{ route('projects.create') }}'"><span class="glyphicon glyphicon-plus"></span>  @lang('buttons.create')</button>
  
	</div>
	@endif
@stop