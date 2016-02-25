@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('projects.projects')
@stop

{{-- Content --}}
@section('content')
    @if ($user->groups->isEmpty())
        <div class="col-md-10 col-md-offset-1">
            <h3>{{ trans('welcome.welcome') }}</h3>
        </div>
        <div class="col-md-10 col-md-offset-1">

            {!! trans('welcome.intro') !!}
            {!! trans('welcome.ready') !!}
            <button class="btn btn-success" title="@lang('buttons.createTitleG')"
                    onClick="location.href='{{ route('groups.get.create') }}'"><span
                        class="glyphicon glyphicon-plus"></span> @lang('buttons.create')</button>

        </div>
    @else
        <div class="jumbotron">
            <h3>{{ trans('projects.projects') }}
            <button title="@lang('buttons.createTitleP')" class="btn btn-success"
                    onClick="location.href='{{ route('projects.get.create') }}'"><span
                        class="fa fa-plus fa-lg"></span> @lang('buttons.create')</button>
            </h3>
        </div>
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table-sort">
                    <thead>
                    <tr>
                        <th class="sorter-false"></th>
                        <th>@lang('pages.title')</th>
                        <th>@lang('pages.group')</th>
                        <th class="nowrap sorter-false">@lang('projects.project_options')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($user->groups as $group)
                        @foreach ($group->projects as $project)
                            <tr>
                                <td><span id="collapse{{ $project->id }}" class="fa fa-folder fa-2x pointer"
                                          data-toggle="collapse" data-target="#{{ $project->id }}"></span></td>
                                <td><a href="{{ route('projects.get.show', [$project->id]) }}">{{ $project->title }}</a>
                                </td>
                                <td><a href="{{ route('groups.get.show', [$group->id]) }}">{{ $group->label }}</a></td>
                                <td class="buttons-sm">
                                    <button title="@lang('buttons.viewTitle')" class="btn btn-primary btn-xs"
                                            type="button"
                                            onClick="location.href='{{ route('projects.get.show', [$project->id]) }}'"><span
                                                class="fa fa-eye fa-lg"></span> @lang('buttons.view')</button>
                                    <button title="@lang('buttons.dataTitle')" class="btn btn-inverse btn-xs"
                                            type="button"
                                            onClick="location.href='{{ route('projects.get.import', [$project->id]) }}'">
                                        <span class="fa fa-plus fa-lg"></span> @lang('buttons.data')</button>
                                    <button title="@lang('buttons.dataViewTitle')" class="btn btn-info btn-xs"
                                            type="button"
                                            onClick="location.href='{{ route('projects.get.explore', [$project->id]) }}'">
                                        <span class="fa fa-search fa-lg"></span> @lang('buttons.dataView')</button>
                                    <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-xs"
                                            type="button"
                                            onClick="location.href='{{ route('projects.get.duplicate', [$project->id]) }}'">
                                        <span class="fa fa-share-alt fa-lg"></span> @lang('buttons.duplicate')</button>
                                    <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs"
                                            type="button"
                                            onClick="location.href='{{ route('projects.get.edit', [$project->id]) }}'"><span
                                                class="fa fa-cog fa-lg"></span> @lang('buttons.edit')</button>
                                    @if ($user->id == $group->user_id)
                                        <button title="@lang('buttons.deleteTitle')"
                                                class="btn btn-default btn-danger action_confirm btn-xs"
                                                href="{{ route('projects.delete.delete', [$project->id]) }}"
                                                data-token="{{ Session::getToken() }}" data-method="delete"><span
                                                    class="fa fa-remove fa-lg"></span> @lang('buttons.delete')</button>
                                    @endif
                                </td>
                            </tr>
                            <tr style="display: none">
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
    @endif
@stop