@if($events->isNotEmpty())
    @each('admin.event.partials.event-loop', $events, 'event')
@else
    <h2 class="mx-auto pt-4">{{ __('No Events exist.') }}</h2>
@endif