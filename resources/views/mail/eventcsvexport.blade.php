@component('mail::message')
    # {{ t('Event Export CSV') }}

    {{ t('Your export is attached. If an attachment is not included, it is due to no records being located for the action. Some records require overnight processing before they are available.') }}
    {{ t('If you believe this is an error, please contact the Administration.') }}

    {{ t('Thank you') }},
    {{ config('app.name') }}
@endcomponent
