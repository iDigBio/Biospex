@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.events') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ __('pages.biospex') }} {{ __('pages.events') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-sm-8 offset-md-2 text-center">
                <button class="toggle-view-btn btn btn-primary my-4 mr-2 text-uppercase"
                        data-toggle="collapse"
                        data-target="#active-events-main,#completed-events-main"
                        data-value="{{ __('pages.view') }} {{ __('pages.active') }} {{ __('pages.events') }}"
                >{{ __('pages.view') }} {{ __('pages.completed') }} {{ __('pages.events') }}</button>
                <a href="{{ route('admin.events.create') }}" type="submit"
                   class="btn btn-primary my-4 ml-2 text-uppercase"><i class="fas fa-plus-circle"></i> {{ __('pages.new') }} {{ __('pages.event') }}</a>
        </div>
    </div>

    <div class="row">
        <div id="active-events-main" class="col-sm-12 show">
            @include('common.event-sort', ['type' => 'active', 'route' => route('admin.events.sort')])
            <div id="active-events" class="row col-sm-12 mx-auto justify-content-center">
                @include('admin.event.partials.event', ['events' => $events])
            </div>
        </div>
        <div id="completed-events-main" class="col-sm-12 collapse">
            @include('common.event-sort', ['type' => 'completed', 'route' => route('admin.events.sort')])
            <div id="completed-events" class="row col-sm-12 mx-auto justify-content-center">
                @include('admin.event.partials.event', ['events' => $eventsCompleted])
            </div>
        </div>
    </div>
    @include('common.scoreboard')
@endsection

