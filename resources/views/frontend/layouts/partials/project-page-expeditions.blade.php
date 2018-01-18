<tr>
    <td>{{ $expedition->title }}</td>
    @if( ! $expedition->actors->isEmpty())
        <td class="nowrap">
            <span class="complete">
            <span class="complete{{ GeneralHelper::roundUpToAnyFive($expedition->stat->percent_completed) }}">&nbsp;</span>
            </span> {{ $expedition->stat->percent_completed }}%
        </td>
    @else
        <td class="nowrap" colspan="3">{{ trans('expeditions.processing_not_started') }}</td>
    @endif
    <td>
        @foreach($expedition->actors as $actor)
            <a href="{{ $actor->url }}">{{ $actor->title }}</a>&nbsp;&nbsp;
        @endforeach
    </td>
</tr>