@component('mail::message')
    # {{ $title }}

    {{ t('The OCR processing of your data is complete. If there were any errors in processing images, an attached file will be present.') }}

    {{ t('Thank you') }},
    {{ config('app.name') }}
@endcomponent