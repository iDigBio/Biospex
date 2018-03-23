<h2>{{ trans('pages.events') }}</h2>
@each('frontend.events.partials.board-loop', $events, 'event')
