@if($events->isNotEmpty())
    @each('front.event.partials.event-loop', $events, 'event')
@else
    <h2 class="mx-auto pt-4">{{ t('No Events exist.') }}</h2>
@endif