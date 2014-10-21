<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
{{{ $missingImageMessage }}}
<p>{{{ $expeditionTitle }}}</p>
<p>
    </blockquote>
    {{{ $missingImages }}}<br />
    {{{ $missingList }}}
    </blockquote>
</p>
<p>{{ trans('emails.thank_you') }}, <br />
    ~{{ trans('emails.signature') }}</p>
</body>
</html>
