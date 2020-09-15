@component('mail::message')
# {{ t('Export Review Job Complete') }}

{{ t('The Expert Review job for %s is complete and you may start reviewing the reconciled records.', $title) }}
{{ t('You may access the page by going to the Expedition Download modal and clicking the green button or click the button below and be taken to the page directly.') }}

@component('mail::button', ['url' => $url])
{{ t('Expert Review Start') }}
@endcomponent

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
