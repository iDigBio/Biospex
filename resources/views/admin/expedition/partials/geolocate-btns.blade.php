<hr class="header mx-auto" style="width:300px;">
<h4>{{ $actor->title }}</h4>
<a href="{{ route('admin.geolocate.index', [$expedition->project_id, $expedition->id]) }}" class="btn btn-primary rounded-0 mb-1">{{ t('GeoLocate Index') }}</a>