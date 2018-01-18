<tr>
    <td>{{ $download->type }}</td>
    <td>{{ $download->file }}</td>
    <td>
        @if (File::exists($paths[$download->type] . '/' . $download->file))
            {{ GeneralHelper::humanFileSize(File::size($paths[$download->type] . '/' . $download->file)) }}
        @else
            {{ GeneralHelper::humanFileSize(mb_strlen($download->data, '8bit')) }}
        @endif
    </td>
    <td>{{ DateHelper::formatDate($download->created_at, 'Y-m-d', $user->profile->timezone) }}</td>
    <td>{{ DateHelper::formatDate($download->updated_at, 'Y-m-d', $user->profile->timezone) }}</td>
    <td>
        @if ($download->type != 'export')
            @include('frontend.downloads.partials.ownerbuttons')
        @else
            @include('frontend.downloads.partials.userbuttons')
        @endif
    </td>
</tr>