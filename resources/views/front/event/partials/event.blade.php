@if($events->isNotEmpty())
    @each('front.event.partials.event-loop', $events, 'event')
@else
    <h2 class="mx-auto pt-4">{{ __('No current Events exist. Click "Completed" button to view completed Events.') }}</h2>
@endif