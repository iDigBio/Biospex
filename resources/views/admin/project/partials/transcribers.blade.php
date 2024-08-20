<tr>
    <td>{{ $transcriber['user_name'] }}</td>
    <td>{{ $transcriber['expeditionCount'] }}</td>
    <td>{{ $transcriber['transcriptionCount'] }}</td>
    <td>{{ format_mongo_date($transcriber['last_date'], 'Y-m-d', auth()->user()->profile->timezone) }}</td>
</tr>