@component('mail::message')
    # {{ t('Welcome') }}

    {{ t('You have been invited to join the BIOSPEX %s group. Click the button to register using this email address with the provided group code.', $title) }}

    @component('mail::button', ['url' => $url])
        {{ t('Join Now') }}
    @endcomponent

    {{ t('Thank you') }},
    {{ config('app.name') }}
@endcomponent
