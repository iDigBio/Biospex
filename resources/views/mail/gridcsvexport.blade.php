@component('mail::message')
    # {{ t('Grid Export CSV') }}

    {{ t('Your grid export is completed. Click the button provided to download:') }}
    @component('mail::button', ['url' => $url])
        {{ t('Download') }}
    @endcomponent


    {{ t('Thank you') }},
    {{ config('app.name') }}
@endcomponent
