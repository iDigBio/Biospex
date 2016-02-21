<div class="table-responsive">
    <table class="table table-hover">
        <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Created</th>
            <th>Subjects</th>
            <th>Transcriptions Goal</th>
            <th>Transcriptions Completed</th>
            <th>Percent Complete</th>
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
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
