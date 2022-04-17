@component('mail::message')
# {{ $file }}

{!! $message !!}

@isset($url)
    @component('mail::button', ['url' => $url])
        {{ t('Download Report CSV') }}
    @endcomponent
@endisset

{{ t('Thank you') }},
{{ config('app.name') }}
@endcomponent
