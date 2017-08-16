<tr>
    <td>{{ $transcriber['user_name'] }}</td>
    <td>{{ $transcriber['expeditionCount'] }}</td>
    <td>{{ $transcriber['transcriptionCount'] }}</td>
    <td>{{ date('Y-m-d', $transcriber['last_date']->sec) }}</td>
</tr>