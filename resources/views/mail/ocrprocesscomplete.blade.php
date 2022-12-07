@component('mail::message')
# {{ $title }}

{{ t('The OCR processing of your data is complete.') }}

@isset($url)
{{ t('Errors during OCR processing were detected. Click button to download error file.') }}
@component('mail::button', ['url' => $url])
{{ t('Download OCR Errors') }}
@endcomponent
{{ t('If clicking button does not work, right click and open in new window.') }}
@endisset

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent