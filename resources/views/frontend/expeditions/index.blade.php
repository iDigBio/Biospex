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

    <div class="row">
        <div class="table-responsive">
            <table class="table table-sort dataTable th-center">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Created</th>
                    <th>Group</th>
                    <th>Project</th>
                    <th><span data-toggle="tooltip"
                              title="@lang('pages.biospex_subjects_header')" data-placement="bottom">Biospex Subjects</span></th>
                    <th><span data-toggle="tooltip" title="@lang('pages.nfn_subjects_header')" data-placement="bottom">NfN Subjects</span></th>
                    <th>Transcriptions Goal</th>
                    <th>Transcriptions Completed</th>
                    <th>Percent Complete</th>
                    <th class="fit sorter-false">Options</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($expeditions as $expedition)
                    <tr>
                        <td>{{ link_to_route('webauth.expeditions.show', $expedition->title, ['projects' => $expedition->project_id, 'expeditions' => $expedition->id]) }}</td>
                        <td>{{ $expedition->description }}</td>
                        <td>{{ DateHelper::convertTimeZone($expedition->created_at, 'Y-m-d', $user->profile->timezone) }}</td>
                        <td>{{ link_to_route('webauth.groups.show', $expedition->project->group->title, ['groups' => $expedition->project->group->id]) }}</td>
                        <td>{{ link_to_route('webauth.projects.show', $expedition->project->title, ['projects' => $expedition->project->id]) }}</td>
                        <td>{{ $expedition->stat->local_subject_count }}</td>
                        <td>{{ $expedition->stat->subject_count }}</td>
                        @if(null !== $expedition->actors)
                            <td>{{ $expedition->stat->transcriptions_total }}</td>
                            <td>{{ $expedition->stat->transcriptions_completed }}</td>
                            <td class="nowrap">
                            <span class="complete">
                                <span class="complete{{ GeneralHelper::roundUpToAnyFive($expedition->stat->percent_completed) }}">&nbsp;</span>
                            </span> {{ $expedition->stat->percent_completed }}%
                            </td>
                        @else
                            <td class="nowrap" colspan="3">{{ trans('expeditions.processing_not_started') }}</td>
                        @endif
                        <td class="fit">
                            <button title="@lang('buttons.viewTitle')" class="btn btn-primary btn-xs" type="button"
                                    onClick="location.href='{{ route('webauth.expeditions.show', [$expedition->project->id, $expedition->id]) }}'">
                                <span class="fa fa-eye fa-lrg"></span> <!-- @lang('buttons.view') --></button>
                            <button title="@lang('buttons.duplicateTitle')" class="btn btn-success btn-xs" type="button"
                                    onClick="location.href='{{ route('webauth.expeditions.duplicate', [$expedition->project->id, $expedition->id]) }}'">
                                <span class="fa fa-copy fa-lrg"></span> <!-- @lang('buttons.duplicate') --></button>
                            <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button"
                                    onClick="location.href='{{ route('webauth.expeditions.edit', [$expedition->project->id, $expedition->id]) }}'">
                                <span class="fa fa-cog fa-lrg"></span> <!-- @lang('buttons.edit') --></button>
                            <button class="btn btn-xs btn-danger" title="@lang('buttons.deleteTitle')"
                                    data-href="{{ route('webauth.expeditions.delete', [$expedition->project->id, $expedition->id]) }}"
                                    data-method="delete"
                                    data-toggle="confirmation"
                                    data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                                    data-btn-ok-class="btn-success"
                                    data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                                    data-btn-cancel-class="btn-danger"
                                    data-title="Continue action?" data-content="This will delete the item">
                                <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') -->
                            </button>

                            @if (null !== $expedition->downloads)
                                <button title="@lang('buttons.downloadTitle')" class="btn btn-success btn-xs"
                                        type="button"
                                        onClick="location.href='{{ route('webauth.downloads.index', [$expedition->project->id, $expedition->id]) }}'">
                                    <span class="fa fa-download fa-lrg"></span> <!-- @lang('buttons.download') -->
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop