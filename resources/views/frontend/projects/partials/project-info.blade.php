<div class="jumbotron">
    <h3>{{ $project->title }}</h3>
    <p>{{ $project->description_short }}</p>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <button title="@lang('pages.dataTitle')" class="btn btn-inverse btn-sm" type="button"
                        onClick="location.href='{{ route('webauth.imports.import', [$project->id]) }}'"><span
                            class="fa fa-plus fa-lrg"></span> @lang('pages.data')</button>
                <button title="@lang('pages.dataViewTitle')" class="btn btn-info btn-sm" type="button"
                        onClick="location.href='{{ route('projects.get.explore', [$project->id]) }}'"><span
                            class="fa fa-search fa-lrg"></span> @lang('pages.dataView')</button>
                <button title="@lang('pages.duplicateTitle')" class="btn btn-success btn-sm" type="button"
                        onClick="location.href='{{ route('webauth.projects.duplicate', [$project->id]) }}'"><span
                            class="fa fa-copy fa-lrg"></span> @lang('pages.duplicate')</button>
                <button title="@lang('pages.editTitle')" class="btn btn-warning btn-sm" type="button"
                        onClick="location.href='{{ route('webauth.projects.edit', [$project->id]) }}'"><span
                            class="fa fa-cog fa-lrg"></span> @lang('pages.edit')</button>
                @can('isOwner', $project->group->getWrappedObject)
                    <button class="btn btn-sm btn-danger" title="@lang('pages.deleteTitle')"
                            data-href="{{ route('webauth.projects.delete', [$project->id]) }}"
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

            <div class="col-md-2">
                <button title="@lang('pages.ocrTitle')" class="btn btn-success btn-sm" type="button"
                        {{ count($project->ocrQueue) === 0 ? '' : 'disabled' }}
                        onClick="location.href='{{ route('webauth.projects.ocr', [$project->id]) }}'">
                    <span class="fa fa-repeat fa-lrg"></span>
                    {{ count($project->ocrQueue) === 0 ? trans('pages.ocr') : trans('pages.ocrDisabled') }}
                </button>
            </div>
            <div class="col-md-6">
                <p class="eyesright"><strong>@lang('pages.project_url')
                        :</strong> {!! link_to_route('home.get.project', $project->title, [$project->slug]) !!}
                </p>
                <button title="@lang('pages.advertiseTitle')" class="btn btn-success btn-sm" type="button"
                        onClick="location.href='{{ route('webauth.advertises.index', [$project->id]) }}'"><span
                            class="fa fa-globe fa-lrg"></span> @lang('pages.advertise')</button>
                @can('isOwner', $project->group->getWrappedObject())
                    <button title="@lang('pages.projectStatsTitle')" class="btn btn-success btn-sm"
                            type="button"
                            onClick="location.href='{{ route('webauth.statistics.index', [$project->id]) }}'">
                        <span class="fa fa-bar-chart fa-lrg"></span> @lang('pages.projectStats')
                    </button>
                @endcan
            </div>
        </div>
    </div>
</div>

<h3>{{ trans('pages.expeditions') }}
    <button class="btn btn-success" title="@lang('pages.createTitleE')"
            onClick="location.href='{{ URL::route('webauth.expeditions.create', [$project->id]) }}'"><span
                class="fa fa-plus fa-lrg"></span> @lang('pages.create')</button>
</h3>