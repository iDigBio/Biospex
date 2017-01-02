<div class="table-responsive">
    <table class="table-sort th-center">
        <thead>
        <tr>
            <th class="sorter-false"></th>
            <th>@lang('pages.title')</th>
            <th>@lang('pages.group')</th>
            <th class="nowrap sorter-false">@lang('projects.project_options')</th>
        </tr>
        </thead>
        <tbody>
        @include('frontend.projects.partials.project-loop')
        </tbody>
    </table>
</div>