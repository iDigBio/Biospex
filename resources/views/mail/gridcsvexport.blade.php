@component('mail::message')
# {{ t('Grid Export CSV') }}

@if(isset($url))
{{ t('Your grid export is completed. Click the button provided to download:') }}
@component('mail::button', ['url' => $url])
{{ t('Download') }}
@endcomponent
@else
{{ t('Grid export could not be completed. Please contact the Admin.') }}
@endif


{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
