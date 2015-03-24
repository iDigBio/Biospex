<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>{{ trans('emails.contact') }}</h2>

<p><b>{{ trans('emails.contact_first') }}:</b> {{{ $first_name }}}</p>
<p><b>{{ trans('emails.contact_last') }}:</b> {{{ $last_name }}}</p>
<p><b>{{ trans('emails.contact_email') }}:</b> {{{ $email }}}</p>
<p><b>{{ trans('emails.contact_message') }}:</b><br />
    {{{ $email_message }}}
</p>
</body>
</html>
