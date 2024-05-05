<tr>
    <td>{{ $transcriber['user_name'] }}</td>
    <td>{{ $transcriber['expeditionCount'] }}</td>
    <td>{{ $transcriber['transcriptionCount'] }}</td>
    <td>{{ \Carbon\Carbon::createFromTimestampMs($transcriber['last_date'])->toDateString() }}</td>
</tr>