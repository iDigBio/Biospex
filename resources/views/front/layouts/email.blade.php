<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<!-- Content -->
@yield('content')
<!-- ./ content -->
<br />
<br />
<p>
    {{ trans('emails.thank_you') }}, <br /><br />
    {{ trans('emails.signature') }}<br />
    {{ $emailAddress  }}
</p>
</body>
</html>