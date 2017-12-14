<table class="table-sort th-center">
    <thead>
    <tr>
        <th>@lang('pages.title')</th>
        <th>@lang('pages.group')</th>
        <th class="nowrap sorter-false">@lang('projects.project_options')</th>
    </tr>
    </thead>
    <tbody>
    @include('frontend.projects.partials.project-loop')
    </tbody>
</table>
