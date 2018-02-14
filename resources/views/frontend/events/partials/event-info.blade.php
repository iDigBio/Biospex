<div class="jumbotron">
    <h3>{{ $event->title }}</h3>
    <p>{{ $event->description }}</p>
    <div class="row">
        <div class="col-md-4">
            <button title="@lang('pages.editTitle')" class="btn btn-warning btn-sm" type="button"
                    onClick="location.href='{{ route('webauth.events.edit', [$event->id]) }}'"><span
                        class="fa fa-cog fa-lrg"></span> @lang('pages.edit')</button>
            @can('delete', $event)
                <button class="btn btn-sm btn-danger" title="@lang('pages.deleteTitle')"
                        data-href="{{ route('webauth.events.delete', [$event->id]) }}"
                        data-method="delete"
                        data-toggle="confirmation"
                        data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                        data-btn-ok-class="btn-success"
                        data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                        data-btn-cancel-class="btn-danger"
                        data-title="Continue action?" data-content="This will delete the item">
                    <span class="fa fa-remove fa-lrg"></span> @lang('pages.delete')
                </button>
            @endcan
        </div>
    </div>
</div>
