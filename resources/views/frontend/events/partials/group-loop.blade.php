@foreach ($event->groups as $group)
    @foreach ($group->users as $user)
        <tr>
            <td>{{ $group->title }}</td>
            <td>{{ $user->nfn_user }}</td>
            <td>{{ $user->transcriptions_count }}</td>
        </tr>
    @endforeach
@endforeach