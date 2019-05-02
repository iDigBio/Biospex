@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.group') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4 text-uppercase">{{ __('pages.biospex') }} {{ __('pages.groups') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-sm-3 mx-auto text-center my-4">
        <a href="{{ route('admin.groups.create') }}" type="submit"
           class="btn btn-primary text-uppercase"><i class="fas fa-plus-circle"></i> {{ __('pages.new') }} {{ __('pages.group') }}</a>
    </div>
    <div class="row">
        <div id="active-expeditions" class="row col-sm-12 mx-auto justify-content-center">
            @include('admin.group.partials.group', ['groups' => $groups])
        </div>
    </div>
    @include('admin.partials.invite-modal')
@stop

