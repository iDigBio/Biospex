<div class="table-responsive">
    <table class="table-sort th-center">
        <thead>
        <tr>
            <colgroup>
                <col class="col-md-4">
                <col class="col-md-6">
                <col class="col-md-2">
            </colgroup>
            <th>@lang('pages.title')</th>
            <th>@lang('pages.group')</th>
            <th class="nowrap sorter-false">@lang('projects.project_options')</th>
        </tr>
        </thead>
        <tbody>
        @include('frontend.projects.partials.project-trashed-loop')
        </tbody>
    </table>
</div>