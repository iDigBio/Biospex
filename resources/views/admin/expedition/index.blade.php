@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.expeditions') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4 text-uppercase">{{ __('pages.biospex') }} {{ __('pages.expeditions') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="text-center mx-auto my-4">
            <button class="toggle-view-btn btn btn-primary pl-4 pr-4 text-uppercase"
                    data-toggle="collapse"
                    data-target="#active-expeditions-main,#completed-expeditions-main"
                    data-value="{{ __('pages.view') }} {{ __('pages.active') }} {{ __('pages.expeditions') }}"
            >{{ __('pages.view') }} {{ __('pages.completed') }} {{ __('pages.expeditions') }}</button>
        </div>
    </div>
    <div class="row">
        <div id="active-expeditions-main" class="col-sm-12 show">
            @include('common.expedition-sort', ['type' => 'active', 'route' => route('admin.expeditions.sort')])
            <div id="active-expeditions" class="row col-sm-12 mx-auto justify-content-center">
                @include('admin.expedition.partials.expedition', ['expeditions' => $expeditions])
            </div>
        </div>
        <div id="completed-expeditions-main" class="col-sm-12 collapse">
            @include('common.expedition-sort', ['type' => 'completed', 'route' => route('admin.expeditions.sort')])
            <div id="completed-expeditions" class="row col-sm-12 mx-auto justify-content-center">
                @include('admin.expedition.partials.expedition', ['expeditions' => $expeditionsCompleted])
            </div>
        </div>
    </div>
@endsection