
<table class="table-sort th-center">
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