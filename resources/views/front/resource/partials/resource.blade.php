@if($resources->isNotEmpty())
    @each('front.resource.partials.resource-loop', $resources, 'resource')
@else
    <h2 class="mx-auto pt-4">{{ t('No Resources exist.') }}</h2>
@endif
