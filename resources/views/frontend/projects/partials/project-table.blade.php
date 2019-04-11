<table class="table-sort th-center">
    <thead>
    <tr>
        <th>@lang('pages.title')</th>
        <th>@lang('pages.group')</th>
        <th class="fit sorter-false">@lang('pages.options')</th>
    </tr>
    </thead>
    <tbody>
    @each('frontend.projects.partials.project-loop', $groups, 'group')
    </tbody>
</table>
