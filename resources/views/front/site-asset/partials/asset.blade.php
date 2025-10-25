@if($assets->isNotEmpty())
    @each('front.site-asset.partials.asset-loop', $assets, 'asset')
@else
    <h2 class="mx-auto pt-4">{{ t('No Resources exist.') }}</h2>
@endif
