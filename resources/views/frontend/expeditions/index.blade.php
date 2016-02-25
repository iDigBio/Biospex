@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('expeditions.expeditions')
@stop

{{-- Content --}}
@section('content')
    <div class="jumbotron">
        <h2>Expeditions</h2>
    </div>

    <div class="table-responsive">
        <table class="table table-sort dataTable">
            <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Created</th>
                <th>Group</th>
                <th>Project</th>
                <th>Subjects</th>
                <th>Transcriptions Goal</th>
                <th>Transcriptions Completed</th>
                <th>Percent Complete</th>
                <th class="sorter-false">Options</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($results as $expedition)
                <tr>
                    <td>{{ link_to_route('projects.expeditions.get.show', $expedition->expedition_title, ['projects' => $expedition->project_id, 'expeditions' => $expedition->expedition_id]) }}</td>
                    <td>{{ $expedition->expedition_description }}</td>
                    <td>{{ convert_time_zone($expedition->expedition_created_at, 'Y-m-d', $user->timezone) }}</td>
                    <td>{{ link_to_route('groups.get.show', $expedition->group_label, ['groups' => $expedition->group_id]) }}</td>
                    <td>{{ link_to_route('projects.get.show', $expedition->project_title, ['projects' => $expedition->project_id]) }}</td>
                    <td>{{ $expedition->subjectsCount }}</td>
                    @if( ! is_null($expedition->actor_expedition_id))
                        <td>{{ $expedition->transcriptions_total }}</td>
                        <td>{{ $expedition->transcriptions_completed }}</td>
                        <td class="nowrap">
                <span class="complete">
                    <span class="complete{{ round_up_to_any_five($expedition->percent_completed) }}">&nbsp;</span>
                </span> {{ $expedition->percent_completed }}%
                        </td>
                    @else
                        <td class="nowrap" colspan="3">{{ trans('expeditions.processing_not_started') }}</td>
                    @endif
                    <td class="buttons-xs">
                        <button title="@lang('buttons.viewTitle')" class="btn btn-info btn-xs" type="button" onClick="location.href='{{ route('projects.expeditions.get.show', [$expedition->project_id, $expedition->expedition_id]) }}'"><span class="fa fa-search fa-lrg"></span> <!-- @lang('buttons.view') --></button>
                        <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ route('projects.expeditions.get.duplicate', [$expedition->project_id, $expedition->expedition_id]) }}'"><span class="fa fa-copy fa-lrg"></span> <!-- @lang('buttons.duplicate') --></button>
                        <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button" onClick="location.href='{{ route('projects.expeditions.get.edit', [$expedition->project_id, $expedition->expedition_id]) }}'"><span class="fa fa-cog fa-lrg"></span> <!-- @lang('buttons.edit') --></button>
                        <button title="@lang('buttons.deleteTitle')" class="btn btn-default btn-danger action_confirm btn-xs" href="{{ route('projects.expeditions.delete.delete', [$expedition->project_id, $expedition->expedition_id]) }}" data-token="{{ Session::getToken() }}" data-method="delete"><span class="fa fa-remove fa-lrg"></span> <!-- @lang('buttons.delete') --></button>
                        @if ( ! is_null($expedition->downloads_id))
                            <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs" type="button" onClick="location.href='{{ route('projects.expeditions.downloads.get.index', [$expedition->project_id, $expedition->expedition_id]) }}'"><span class="fa fa-download fa-lrg"></span> <!-- @lang('buttons.download') --></button>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@stop