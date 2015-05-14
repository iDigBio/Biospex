<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Created</th>
            <th>Subjects</th>
            <th>Incomplete</th>
            <th>Complete</th>
            <th>Percent Complete</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($project->expeditions as $expedition)
                <tr>
                    <td>{{ $expedition->title }}</td>
                    <td>{{ $expedition->description }}</td>
                    <td>{{ $expedition->created_at }}</td>
                    <td>{{ $expedition->subjectsCount }}</td>
                    <td>0</td>
                    <td>0</td>
                    <td class="nowrap">
                        <span class="complete">
                            <span class="complete{{ Helper::roundUpToAnyFive($expedition->actorsCompleted) }}">&nbsp;</span>
                        </span> {{ Helper::roundUpToAnyFive($expedition->actorsCompleted) }}%
                    </td>
                </tr>
                @endforeach
        </tbody>
    </table>
</div>
