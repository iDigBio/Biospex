@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Groups') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ __('BIOSPEX GROUPS') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-sm-3 mx-auto text-center my-4">
        <a href="{{ route('admin.groups.create') }}" type="submit"
           class="btn btn-primary"><i class="fas fa-plus-circle"></i> {{ __('NEW GROUP') }}</a>
    </div>
    <div class="row">
        <div id="active-expeditions" class="row col-sm-12 mx-auto justify-content-center">
            @include('admin.group.partials.group', ['groups' => $groups])
        </div>
    </div>
@stop

