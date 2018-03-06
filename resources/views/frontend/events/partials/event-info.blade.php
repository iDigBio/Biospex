<div class="jumbotron">
    <div class="row">
        <div class="col-md-6">
            <h3>{{ $event->title }}</h3>
            <p>{{ $event->description }}</p>
            <h4>@lang('pages.project'): {{ $event->project->title }}</h4>
        </div>
        <div class="col-md-6">
            <h3>@lang('pages.transcriptions'): {{ $event->transcriptionCount }}</h3>
            <h3>Group Invite Links</h3>
            @foreach($event->groups as $group)
                <button type="button" class="btn btn-default btn-copy js-tooltip js-copy"
                        data-toggle="tooltip"
                        data-placement="bottom"
                        data-copy="{{ route('web.events.join', [$group->uuid]) }}" title="Copy to clipboard">
                    <span class="fa fa-clipboard fa-lg"></span> {{ $group->title }}
                </button>
            @endforeach
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <button title="@lang('pages.downloadTitleTranscriptions')" class="btn btn-success btn-sm"
                        type="button"
                        onClick="location.href='{{ route('webauth.events.exportUsers', [$event->id]) }}'">
                    <span class="fa fa-download fa-lrg"></span> @lang('pages.users')
                </button>
                <button title="@lang('pages.downloadTitleUsers')" class="btn btn-success btn-sm"
                        type="button"
                        onClick="location.href='{{ route('webauth.events.exportTranscriptions', [$event->id]) }}'">
                    <span class="fa fa-download fa-lrg"></span> @lang('pages.transcriptions')
                </button>
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
</div>
