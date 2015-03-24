<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>{{ trans('emails.contact') }}</h2>

<p><b>{{ trans('emails.contact_first') }}:</b> {{{ $firstName }}}</p>
<p><b>{{ trans('emails.contact_last') }}:</b> {{{ $lastName }}}</p>
<p><b>{{ trans('emails.contact_email') }}:</b> {{{ $email }}}</p>
<p><b>{{ trans('emails.contact_message') }}:</b><br />
    {{{ $message }}}
</p>
</body>
</html>
