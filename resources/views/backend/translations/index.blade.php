@extends('backend.layouts.app')

@section('htmlheader_title')
    Translations
@endsection

@section('contentheader_title', 'Translations')


@section('main-content')
<div class="row">
    <div class="col-xs-12">
        @include('vendor.translation-manager.index')
    </div>
</div>
@endsection