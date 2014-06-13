@extends('layouts.default')

@section('title', trans('smarterror::error.csrfTitle'))

@section('content')

	<p><?php echo trans('smarterror::error.csrfText'); ?></p>
	<p style="text-align:center;">
	<?php if ($referer): ?>
		<a href="<?php echo $referer; ?>"><?php echo trans('smarterror::error.backLinkTitle'); ?></a> - 
	<?php endif; ?>
		<a href="/"><?php echo trans('smarterror::error.frontpageLinkTitle'); ?></a>
	</p>

@stop
