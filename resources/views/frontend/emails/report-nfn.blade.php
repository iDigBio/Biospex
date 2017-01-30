@extends('frontend.layouts.email')

{{-- Content --}}
@section('content')
    <p>
    {{ $mainMessage }}
    <br /><br />
    <blockquote>
        <b>@lang('forms.contact'):</b> {{ $projectContact }}<br />
        <b>@lang('forms.contact_email'):</b> {{ $projectContactEmail }}<br />
        <b>@lang('forms.title'):</b> {{ $projectTitle }}<br />
        <b>@lang('forms.description_long'):</b> {!! $projectLongDescription !!}
        <br />
        <br />
    </blockquote>
    </p>
@stop