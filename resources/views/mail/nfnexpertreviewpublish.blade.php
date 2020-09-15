@component('mail::message')
# {{ t('Expert Review Reconciled Published') }}

{{ t('The Expert Reviewed Reconciled CSV file has been published for %s', $title) }}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
