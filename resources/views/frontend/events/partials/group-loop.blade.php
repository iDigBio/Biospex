@foreach ($event->groups as $group)
    @foreach ($group->users as $user)
        <tr>
            <td>{{ $group->title }}</td>
            <td>{{ $user->nfn_user }}</td>
            <td>{{ $user->transcriptionCount }}</td>
        </tr>
    @endforeach
@endforeach