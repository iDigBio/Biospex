@component('mail::message')
# {{ t('Grid Export CSV') }}

@if($expeditionId !== 0)
{{ t('Your grid export for Expedition Id %s is complete. Click the button provided to download:', $expeditionId) }}
@else
{{ t('Your grid export for Project Id %s is complete. Click the button provided to download:', $projectId) }}
@endif
@component('mail::button', ['url' => $url])
{{ t('Download') }}
@endcomponent

{{ t('If clicking button does not work, right click and open in new window.') }}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
