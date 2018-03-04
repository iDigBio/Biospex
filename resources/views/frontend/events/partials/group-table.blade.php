<button title="@lang('pages.downloadTitle')" class="btn btn-success btn-sm"
        type="button"
        onClick="location.href='{{ route('webauth.events.exportUsers', [$event->id]) }}'">
    <span class="fa fa-download fa-lrg"></span> @lang('pages.download')
</button>
<table class="table-sort th-center top5">
    <thead>
    <tr>
        <th>@lang('pages.groups')</th>
        <th>@lang('pages.users')</th>
        <th>@lang('pages.transcriptions')</th>
    </tr>
    </thead>
    <tbody>
    @include('frontend.events.partials.group-loop')
    </tbody>
</table>