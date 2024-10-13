<hr class="header mx-auto" style="width:300px;">
<h4>{{ $actor->title }}</h4>

<button
        class="btn btn-primary rounded-0 mb-1"
        data-dismiss="modal"
        data-toggle="modal"
        data-target="#global-modal"
        data-size="modal-xl"
        data-url="{{ route('admin.geolocate-form.index', [$expedition]) }}"
        data-title="{{ t('GeoLocate Export Form') }}">{{ t('GeoLocate Export Form') }}</button>

@if($expedition->project->group->geoLocateForms->isNotEmpty())
    <a href="{{ route('admin.groups.show', [$expedition->project->group]) }}#geolocate-forms"
       class="btn btn-primary rounded-0 mb-1">{{ t('Manage GeoLocate Forms') }}</a>
@endif

@if($actor->pivot->state > 0)
    <a href="" class="prevent-default btn btn-primary rounded-0 mb-1"
       data-dismiss="modal"
       data-toggle="modal"
       data-target="#global-modal"
       data-size="modal-lg"
       data-url="{{ route('admin.geolocate-community.edit', [$expedition]) }}"
       data-title="{{ t('Edit GeoLocate Community & Data Source') }}"> {{ t('Edit GeoLocate Community & Data Source') }}</a>
@endif

@if($actor->pivot->state > 1)
    <button
            class="btn btn-primary rounded-0 mb-1"
            data-dismiss="modal"
            data-toggle="modal"
            data-target="#global-modal"
            data-size="modal-lg"
            data-url="{{ route('admin.geolocate-stats.index', [$expedition->geoLocateDataSource]) }}"
            data-title="{{ t('GeoLocate Stats') }}">{{ t('GeoLocate Stats') }}</button>
@endif

@if($actor->pivot->state === 3)
    <a href="{{ route('admin.geolocate-stats.update', [$expedition->geoLocateDataSource]) }}"
       class="prevent-default btn btn-primary rounded-0 mb-1"
       data-dismiss="modal"
       data-title="{{ t('Refresh GeoLocate Stats') }}"
       data-method="post"
       data-confirm="confirmation"
       data-content="{{ t('This will refresh the stats and kml file if there has been changes on GeoLocate. Do you wish to Continue?') }}
            ">{{ t('Refresh GeoLocate Stats') }}</a>
@endif