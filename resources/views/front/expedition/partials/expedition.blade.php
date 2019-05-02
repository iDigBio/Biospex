@if($expeditions->isNotEmpty())
    @each('front.expedition.partials.expedition-loop', $expeditions, 'expedition')
@else
    <h2 class="mx-auto pt-4">{{ __('pages.expeditions_none') }}</h2>
@endif