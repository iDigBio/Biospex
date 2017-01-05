<div class="table-responsive">
    <table class="table th-center">
        <colgroup>
            <col class="">
            <col class="">
            <col class="">
        </colgroup>
        <thead>
        <tr>
            <th>@lang('pages.title')</th>
            <th>@lang('pages.message')</th>
            <th class="sorter-false">@lang('pages.options')</th>
        </tr>
        </thead>
        <tbody>
        @include('frontend.notifications.partials.notification-loop')
        </tbody>
    </table>
</div>