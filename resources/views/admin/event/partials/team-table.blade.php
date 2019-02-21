<div class="col-md-10 offset-2 mx-auto">
<table class="table-sort th-center top5" style="width: 100%">
    <thead>
    <tr>
        <th>@lang('pages.teams')</th>
        <th>@lang('pages.users')</th>
        <th>@lang('pages.transcriptions')</th>
    </tr>
    </thead>
    <tbody>
    @include('admin.event.partials.team-loop')
    </tbody>
</table>
</div>