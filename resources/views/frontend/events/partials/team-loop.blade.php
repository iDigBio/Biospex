@foreach ($event->teams as $team)
    @foreach ($team->users as $user)
        <tr>
            <td>{{ $team->title }}</td>
            <td>{{ $user->nfn_user }}</td>
            <td>{{ $user->transcriptions_count }}</td>
        </tr>
    @endforeach
@endforeach