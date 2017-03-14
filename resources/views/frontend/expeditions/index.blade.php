@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    @lang('expeditions.expeditions')
@stop

{{-- Content --}}
@section('content')
    <div class="jumbotron">
        <h3>Expeditions</h3>
    </div>

    <div class="table-responsive">
        <table class="table table-sort dataTable th-center">
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
            @foreach ($expeditions as $expedition)
                <tr>
                    <td>{{ link_to_route('web.expeditions.show', $expedition->title, ['projects' => $expedition->project_id, 'expeditions' => $expedition->expedition_id]) }}</td>
                    <td>{{ $expedition->description }}</td>
                    <td>{{ convert_time_zone($expedition->created_at, 'Y-m-d', $user->profile->timezone) }}</td>
                    <td>{{ link_to_route('web.groups.show', $expedition->project->group->title, ['groups' => $expedition->project->group->id]) }}</td>
                    <td>{{ link_to_route('web.projects.show', $expedition->project->title, ['projects' => $expedition->project->id]) }}</td>
                    <td>{{ $expedition->stat->subject_count }}</td>
                    @if(null !== $expedition->actors)
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
                                onClick="location.href='{{ route('web.expeditions.show', [$expedition->project->id, $expedition->id]) }}'">
                            <span class="fa fa-eye fa-lrg"></span> <!-- @lang('buttons.view') --></button>
                        <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-xs" type="button"
                                onClick="location.href='{{ route('web.expeditions.duplicate', [$expedition->project->id, $expedition->id]) }}'">
                            <span class="fa fa-copy fa-lrg"></span> <!-- @lang('buttons.duplicate') --></button>
                        <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button"
                                onClick="location.href='{{ route('web.expeditions.edit', [$expedition->project->id, $expedition->id]) }}'">
                            <span class="fa fa-cog fa-lrg"></span> <!-- @lang('buttons.edit') --></button>
                        <button title="@lang('buttons.deleteTitle')"
                                class="btn btn-danger btn-xs delete-form" type="button"
                                data-method="delete"
                                data-toggle="confirmation" data-placement="left"
                                data-href="{{ route('web.expeditions.delete', [$expedition->project->id, $expedition->id]) }}"><span
                                    class="fa fa-remove fa-lrg"></span> <!-- @lang('buttons.delete') --></button>

                        @if (null !== $expedition->downloads)
                            <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs" type="button"
                                    onClick="location.href='{{ route('web.downloads.index', [$expedition->project->id, $expedition->id]) }}'">
                                <span class="fa fa-download fa-lrg"></span> <!-- @lang('buttons.download') --></button>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@stop