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
    @foreach ($expeditions as $expedition)
    <tr>
        <td>{{ $expedition->title }}</td>
        <td>{{ $expedition->description }}</td>
        <td>{{ $expedition->created_at }}</td>
        <td>{{ $expedition->total_subjects }}</td>
        <td>500</td>
        <td>300</td>
        <td>37.5%</td>
    </tr>
    @endforeach
    </tbody>
</table>
