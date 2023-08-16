@if($events->isNotEmpty())
    @foreach($events as $event)
        @include('front.event.partials.event-loop')
    @endforeach
@else
    <h2 class="mx-auto pt-4">{{ t('No Events exist.') }}</h2>
@endif