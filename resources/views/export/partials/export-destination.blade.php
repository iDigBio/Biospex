<h3 class="mb-5 mx-auto">{{ t('Select Export Destination') }}:</h3>
<div class="col-sm-10 mx-auto text-center">
    <button type="button" class="btn btn-primary pl-4 pr-4 text-uppercase"
            data-toggle="collapse"
            data-target="#geolocate"
            data-hover="tooltip" title="{{ t('Export to GeoLocate') }}"
            aria-expanded="false" aria-controls="collapseGeoLocate"
    >{{ t('geolocate') }}</button>
    <button type="button" class="btn btn-primary pl-4 pr-4 text-uppercase"
            data-toggle="collapse"
            data-target="#people"
            data-hover="tooltip" title="{{ t('Export to People Standardization') }}"
            aria-expanded="false" aria-controls="collapsePeople"
    >{{ t('People') }}</button>
    <button type="button" class="btn btn-primary pl-4 pr-4 text-uppercase"
            data-toggle="collapse"
            data-target="#taxonomic"
            data-hover="tooltip" title="{{ t('Export Taxonomic') }}"
            aria-expanded="false" aria-controls="collapseTaxonomic"
    >{{ t('Taxonomic') }}</button>
    <button type="button" class="btn btn-primary pl-4 pr-4 text-uppercase"
            data-toggle="collapse"
            data-target="#generic"
            data-hover="tooltip" title="{{ t('Export Generic') }}"
            aria-expanded="false" aria-controls="collapseGeneric"
    >{{ t('Generic') }}</button>
    <button type="button" class="btn btn-primary pl-4 pr-4 text-uppercase"
            data-toggle="collapse"
            data-target="#product"
            data-hover="tooltip" title="{{ t('Product Data') }}"
            aria-expanded="false" aria-controls="collapseProduct"
    >{{ t('Product Data') }}</button>
</div>