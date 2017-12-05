@component('mail::message')
# @lang('emails.error')

{{ $exception['message'] }}
{{ $exception['file'] }} {{ $exception['line'] }}
{{ $exception['trace'] }}

Thanks,
{{ config('app.name') }}
@endcomponent