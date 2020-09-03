@component('mail::message')
    # {{ t('Job Error') }}

    {{ t('Message') }}: {{ $message }}
    {{ t('File') }}: {{ $file }}
    {{ t('Line') }}: {{ $line }}
    {{ t('Trace') }}: {{ $trace }}

    {{ t('Thank you') }},
    {{ config('app.name') }}
@endcomponent
