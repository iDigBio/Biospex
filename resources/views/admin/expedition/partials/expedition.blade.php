@if($expeditions->isNotEmpty())
    @foreach($expeditions as $expedition)
        @include('admin.expedition.partials.expedition-loop')
    @endforeach
@else
    <h2 class="mx-auto pt-4">{{ t('No Expeditions exist.') }}</h2>
@endif