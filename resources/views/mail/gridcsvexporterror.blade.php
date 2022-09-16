@component('mail::message')
# {{ t('Grid Export CSV') }}

{{ t('There was an error while exporting the grid csv. The Administration has been contacted and will be working on the issue.') }}

{{ t('Error') }}: {!! $message !!}

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
