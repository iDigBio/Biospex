@component('mail::message')
# @lang('pages.contact')

**@lang('pages.contact_name'):** {{ $contact['name'] }}
**@lang('pages.contact_email'):** {{ $contact['email'] }}
**@lang('pages.contact_message'):**
{{ $contact['message'] }}

Thank you,<br>
{{ config('app.name') }}
@endcomponent