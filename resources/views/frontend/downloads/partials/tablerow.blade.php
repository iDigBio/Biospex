<tr>
    <td>{{ $download->type }}</td>
    <td>{{ $download->file }}</td>
    <td>
        @if (File::exists($paths[$download->type] . '/' . $download->file))
            {{ human_file_size(File::size($paths[$download->type] . '/' . $download->file)) }}
        @else
            {{ human_file_size(mb_strlen($download->data, '8bit')) }}
        @endif
    </td>
    <td>{{ format_date($download->created_at, 'Y-m-d', $user->profile->timezone) }}</td>
    <td>{{ format_date($download->updated_at, 'Y-m-d', $user->profile->timezone) }}</td>
    <td>
        @if ($download->type != 'export')
            @include('frontend.downloads.partials.ownerbuttons')
        @else
            @include('frontend.downloads.partials.userbuttons')
        @endif
    </td>
</tr>