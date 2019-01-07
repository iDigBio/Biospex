<table class="table-sort dataTable th-center">
    <thead>
    <tr>
        <th>Title</th>
        <th>Description</th>
        <th>Created</th>
        <th><span data-toggle="tooltip" title="@lang('pages.biospex_subjects_header')" data-placement="bottom">Biospex Subjects</span></th>
        <th><span data-toggle="tooltip" title="@lang('pages.nfn_subjects_header')" data-placement="bottom">NfN Subjects</span></th>
        <th>Transcriptions Goal</th>
        <th>Transcriptions Completed</th>
        <th>Percent Complete</th>
        <th class="fit sorter-false">Options</th>
    </tr>
    </thead>
    <tbody>
    @if(null === $expeditions)
        <td colspan="8">@lang('pages.expeditions_none')</td>
    @else
        @foreach($expeditions as $expedition)
            @include('front.projects.partials.expedition-loop')
        @endforeach
    @endif
    </tbody>
</table>