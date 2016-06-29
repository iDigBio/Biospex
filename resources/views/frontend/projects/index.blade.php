@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('projects.projects')
@stop

{{-- Content --}}
@section('content')
    @if ($groups->isEmpty())
        <div class="col-md-10 col-md-offset-1">
            <h3>{{ trans('welcome.welcome') }}</h3>
        </div>
        <div class="col-md-10 col-md-offset-1">

            {!! trans('welcome.intro') !!}
            {!! trans('welcome.ready') !!}
            <button class="btn btn-success" title="@lang('buttons.createTitleG')"
                    onClick="location.href='{{ route('web.groups.create') }}'"><span
                        class="glyphicon glyphicon-plus"></span> @lang('buttons.create')</button>

        </div>
    @else
        <div class="jumbotron">
            <h3>{{ trans('projects.projects') }}
                <button title="@lang('buttons.createTitleP')" class="btn btn-success"
                        onClick="location.href='{{ route('web.projects.create') }}'"><span
                            class="fa fa-plus fa-lg"></span> @lang('buttons.create')</button>
            </h3>
        </div>
        <div class="table-responsive">

            <table class="table-sort th-center">
                <thead>
                <tr>
                    <th class="sorter-false"></th>
                    <th>@lang('pages.title')</th>
                    <th>@lang('pages.group')</th>
                    <th class="nowrap sorter-false">@lang('projects.project_options')</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($groups as $group)
                    @foreach ($group->projects as $project)
                        <tr id="test{{ $project->id }}">
                            <td class="folder-space"><span id="{{ $project->id }}"
                                                           class="toggle fa fa-folder fa-2x pointer"></span></td>
                            <td><a href="{{ route('web.projects.show', [$project->id]) }}">{{ $project->title }}</a>
                            </td>
                            <td><a href="{{ route('web.groups.show', [$group->id]) }}">{{ $group->name }}</a></td>
                            <td class="buttons-sm">
                                <button title="@lang('buttons.viewTitle')" class="btn btn-primary btn-xs"
                                        type="button"
                                        onClick="location.href='{{ route('web.projects.show', [$project->id]) }}'"><span
                                            class="fa fa-eye fa-lg"></span> @lang('buttons.view')</button>
                                <button title="@lang('buttons.dataTitle')" class="btn btn-inverse btn-xs"
                                        type="button"
                                        onClick="location.href='{{ route('web.imports.import', [$project->id]) }}'">
                                    <span class="fa fa-plus fa-lg"></span> @lang('buttons.data')</button>
                                <button title="@lang('buttons.dataViewTitle')" class="btn btn-info btn-xs"
                                        type="button"
                                        onClick="location.href='{{ route('projects.get.explore', [$project->id]) }}'">
                                    <span class="fa fa-search fa-lg"></span> @lang('buttons.dataView')</button>
                                <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-xs"
                                        type="button"
                                        onClick="location.href='{{ route('web.projects.duplicate', [$project->id]) }}'">
                                    <span class="fa fa-share-alt fa-lg"></span> @lang('buttons.duplicate')</button>
                                <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs"
                                        type="button"
                                        onClick="location.href='{{ route('web.projects.edit', [$project->id]) }}'"><span
                                            class="fa fa-cog fa-lg"></span> @lang('buttons.edit')</button>
                                @can('delete', $group)
                                    <button title="@lang('buttons.deleteTitle')"
                                            class="btn btn-danger btn-xs delete-form"
                                            data-method="delete"
                                            data-confirm="Are you sure you wish to delete?"
                                            data-href="{{ route('web.projects.delete', [$project->id]) }}"><span
                                                class="fa fa-remove fa-lg"></span> @lang('buttons.delete')</button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>

        </div>
    @endif
@stop