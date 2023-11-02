@component('mail::message')
# {{ t('GeoLocateExport Csv Export') }}

{{ t('Your GeoLocateExport csv export is completed. You may click the download button to download the file or visit the Expedition and use the download section.') }}

@isset($url)
@component('mail::button', ['url' => $url])
{{ t('Download GeoLocateExport CSV') }}
@endcomponent
{{ t('If clicking button does not work, right click and open in new window.') }}
@endisset

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent