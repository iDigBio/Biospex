<table class="table-sort th-center table-responsive">
    <thead>
    <th>@lang('pages.name')</th>
    <th class="nowrap sorter-false">@lang('pages.options')</th>
    </thead>
    <tbody>
    @include('frontend.groups.partials.group-trashed-loop')
    </tbody>
</table>