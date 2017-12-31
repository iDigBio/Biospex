<tr>
    <td>{{ $transcriber['user_name'] }}</td>
    <td>{{ $transcriber['expeditionCount'] }}</td>
    <td>{{ $transcriber['transcriptionCount'] }}</td>
    <td>{{ DateHelper::formatMongoDbDate($transcriber['last_date']) }}</td>
</tr>