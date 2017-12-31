@component('mail::message')
# @lang('emails.contact')

**@lang('emails.contact_name'):** {{ $contact['first_name'] }} {{ $contact['last_name'] }}
**@lang('emails.contact_email'):** {{ $contact['email'] }}
**@lang('emails.contact_message'):**
{{ $contact['message'] }}

Thanks,
{{ config('app.name') }}
@endcomponent