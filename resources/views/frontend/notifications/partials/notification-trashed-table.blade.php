<div class="table-responsive">
    <table class="table th-center">
        <thead>
        <tr>
            <th>@lang('pages.title')</th>
            <th>@lang('pages.message')</th>
            <th class="nowrap sorter-false">@lang('pages.options')</th>
        </tr>
        </thead>
        <tbody>
        @include('frontend.notifications.partials.notification-trashed-loop')
        </tbody>
    </table>
</div>