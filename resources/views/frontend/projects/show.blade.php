@extends('frontend.layouts.default')
{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('web.projects.show', $project) !!}
    <div class="jumbotron">
        <h3>{{ $project->title }}</h3>
        <p>{{ $project->description_short }}</p>
    </div>

    <div class="panel panel-primary">
        <div style="padding: 10px;">
            <p class="eyesright"><strong>@lang('pages.project_url')
                    :</strong> {!! link_to_route('home.get.project', $project->title, [$project->slug]) !!}</p>
            <button title="@lang('buttons.dataTitle')" class="btn btn-inverse btn-sm" type="button"
                    onClick="location.href='{{ route('web.imports.import', [$project->id]) }}'"><span
                        class="fa fa-plus fa-lrg"></span> @lang('buttons.data')</button>
            <button title="@lang('buttons.dataViewTitle')" class="btn btn-info btn-sm" type="button"
                    onClick="location.href='{{ route('projects.get.explore', [$project->id]) }}'"><span
                        class="fa fa-search fa-lrg"></span> @lang('buttons.dataView')</button>
            <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-sm" type="button"
                    onClick="location.href='{{ route('web.projects.duplicate', [$project->id]) }}'"><span
                        class="fa fa-copy fa-lrg"></span> @lang('buttons.duplicate')</button>
            <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-sm" type="button"
                    onClick="location.href='{{ route('web.projects.edit', [$project->id]) }}'"><span
                        class="fa fa-cog fa-lrg"></span> @lang('buttons.edit')</button>
            @can('delete', $project)
                <button title="@lang('buttons.deleteTitle')" class="btn btn-danger btn-sm"
                        data-method="delete"
                        data-toggle="confirmation" data-placement="left"
                        data-href="{{ route('web.projects.delete', [$project->id]) }}"><span
                            class="fa fa-remove fa-lrg"></span> @lang('buttons.delete')</button>
                </td>
            @endcan
            <button title="@lang('buttons.advertiseTitle')" class="btn btn-success btn-sm" type="button"
                    onClick="location.href='{{ route('web.advertises.index', [$project->id]) }}'"><span
                        class="fa fa-globe fa-lrg"></span> @lang('buttons.advertise')</button>
        </div>
    </div>

    <h3>{{ trans('pages.expeditions') }}
        <button class="btn btn-success" title="@lang('buttons.createTitleE')"
                onClick="location.href='{{ URL::route('web.expeditions.create', [$project->id]) }}'"><span
                    class="fa fa-plus fa-lrg"></span> @lang('buttons.create')</button>
    </h3>
    <div class="table-responsive">
        <table class="table table-sort dataTable th-center">
            <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Created</th>
                <th>Subjects</th>
                <th>Transcriptions Goal</th>
                <th>Transcriptions Completed</th>
                <th>Percent Complete</th>
                <th class="sorter-false">Options</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($project->expeditions as $expedition)
                <tr>
                    <td>{{ $expedition->title }}</td>
                    <td>{{ $expedition->description }}</td>
                    <td>{{ format_date($expedition->created_at, 'Y-m-d', $user->timezone) }}</td>
                    <td>{{ $expedition->subjectsCount }}</td>
                    @if( ! $expedition->actors->isEmpty())
                        <td>{{ $expedition->stat->transcriptions_total }}</td>
                        <td>{{ $expedition->stat->transcriptions_completed }}</td>
                        <td class="nowrap">
                <span class="complete">
                    <span class="complete{{ round_up_to_any_five($expedition->stat->percent_completed) }}">&nbsp;</span>
                </span> {{ $expedition->stat->percent_completed }}%
                        </td>
                    @else
                        <td class="nowrap" colspan="3">{{ trans('expeditions.processing_not_started') }}</td>
                    @endif
                    <td class="buttons-xs">
                        <button title="@lang('buttons.viewTitle')" class="btn btn-primary btn-xs" type="button"
                                onClick="location.href='{{ route('web.expeditions.show', [$project->id, $expedition->id]) }}'">
                            <span class="fa fa-eye fa-lrg"></span> <!-- @lang('buttons.view') --></button>
                        <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-xs" type="button"
                                onClick="location.href='{{ route('web.expeditions.duplicate', [$project->id, $expedition->id]) }}'">
                            <span class="fa fa-copy fa-lrg"></span> <!-- @lang('buttons.duplicate') --></button>
                        <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button"
                                onClick="location.href='{{ route('web.expeditions.edit', [$project->id, $expedition->id]) }}'">
                            <span class="fa fa-cog fa-lrg"></span> <!-- @lang('buttons.edit') --></button>
                        <button title="@lang('buttons.deleteTitle')"
                                class="btn btn-danger btn-xs"
                                data-method="delete"
                                data-toggle="confirmation" data-placement="left"
                                data-href="{{ route('web.expeditions.delete', [$project->id, $expedition->id]) }}"><span
                                    class="fa fa-remove fa-lrg"></span> <!-- @lang('buttons.delete') --></button>

                        @if ( ! $expedition->downloads->isEmpty())
                            <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs" type="button"
                                    onClick="location.href='{{ route('web.downloads.index', [$project->id, $expedition->id]) }}'">
                                <span class="fa fa-download fa-lrg"></span> <!-- @lang('buttons.download') --></button>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@stop
