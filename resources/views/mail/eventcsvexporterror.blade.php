@component('mail::message')
    # {{ t('Event Export CSV Error') }}

    {{ t('There was an error while exporting the csv file. The Administration has been copied on this error and will investigate.') }}

    {{ t('Error') }}: {{ $error }}

    {{ t('Thank you') }},
    {{ config('app.name') }}
@endcomponent
