@extends('layouts.default')

@section('title', trans('smarterror::error.genericErrorTitle'))

@section('content')

	<h2>{{{  trans('smarterror::error.genericErrorParagraph1') }}}</h2>
	<h2>{{{  trans('smarterror::error.genericErrorParagraph2') }}}</h2>

@stop
