@component('mail::message')
    # Failed Job Errors

    {!! $message !!}

    Thank you,<br>
    {{ config('app.name') }}
@endcomponent
