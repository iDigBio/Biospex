@component('mail::message')
# {{ $title }}

{{ t('The OCR processing of your data is complete.') }}

@isset($url)
{{ t('Errors during OCR processing were detected. Click button to download error file.') }}
@component('mail::button', ['url' => $url])
{{ t('Download OCR Errors') }}
@endcomponent
@endisset

{{ t('If clicking button does not work, right click and open in new window.') }}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent