@component('mail::message')
# {{ t('Grid Export CSV') }}

@if($expeditionId !== 0)
{{ t('Your grid export for Expedition Id %s is complete. Click the button provided to download:', $expeditionId) }}
@else
{{ t('Your grid export for Project Id %s is complete. Click the button provided to download:', $projecctId) }}
@endif
@component('mail::button', ['url' => $url])
{{ t('Download') }}
@endcomponent

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
