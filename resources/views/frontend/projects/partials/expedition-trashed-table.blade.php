<h3>{{ trans('pages.trash') }}</h3>
<table class="table table-sort dataTable th-center">
    <thead>
    <tr>
        <th>Title</th>
        <th>Description</th>
        <th>Created</th>
        <th class="fit sorter-false">Options</th>
    </tr>
    </thead>
    <tbody>
    @if(null === $trashed)
        <td colspan="4">@lang('pages.trashed_none')</td>
    @else
        @foreach($trashed as $expedition)
            @include('frontend.projects.partials.expedition-trashed-loop')
        @endforeach
    @endif
    </tbody>
</table>