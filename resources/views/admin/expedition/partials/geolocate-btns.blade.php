<hr class="header mx-auto" style="width:300px;">
<h4>{{ $actor->title }}</h4>

<button
        class="btn btn-primary rounded-0 mb-1"
        data-dismiss="modal"
        data-toggle="modal"
        data-target="#global-modal"
        data-size="modal-lg"
        data-url="{{ route('admin.geolocates.show', [$expedition->project_id, $expedition->id]) }}"
        data-title="{{ t('GeoLocate Export Form') }}">{{ t('GeoLocate Export Form') }}</button>

@if($expedition->project->group->geoLocateForms->isNotEmpty())
<a href="{{ route('admin.groups.show', [$expedition->project->group->id]) }}#geolocate-forms"
   class="btn btn-primary rounded-0 mb-1">{{ t('Manage GeoLocate Forms') }}</a>
@endif

@if($actor->pivot->state > 0)
    <a href="" class="prevent-default btn btn-primary rounded-0 mb-1"
       data-dismiss="modal"
       data-toggle="modal"
       data-target="#global-modal"
       data-size="modal-lg"
       data-url="{{ route('admin.geolocates.communityForm', [$expedition->project_id, $expedition->id]) }}"
       data-title="{{ t('Edit GeoLocate Community & Data Source') }}"> {{ t('Edit GeoLocate Community & Data Source') }}</a>
@endif

@if($actor->pivot->state > 1)
<button
        class="btn btn-primary rounded-0 mb-1"
        data-dismiss="modal"
        data-toggle="modal"
        data-target="#global-modal"
        data-size="modal-lg"
        data-url="{{ route('admin.geolocates.stats', [$expedition->project_id, $expedition->id]) }}"
        data-title="{{ t('GeoLocate Stats') }}">{{ t('GeoLocate Stats') }}</button>
@endif