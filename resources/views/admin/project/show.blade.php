@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
    @include('admin.project.partials.project-panel')
    <div class="row">
        <div class="col-sm-8 offset-md-2">
            <h1 class="text-center content-header text-uppercase" id="expeditions">{{ __('Expeditions') }}</h1>
            <hr class="header mx-auto" style="width:300px;">
            <div class="text-center mt-4">
                <button class="toggle-view-btn btn btn-primary pl-4 pr-4 text-uppercase"
                        data-toggle="collapse"
                        data-target="#active-expeditions-main,#completed-expeditions-main"
                        data-value="{{ t('view active expeditions') }}"
                >{{ t('view completed expeditions') }}</button>
            </div>
            <div class="d-flex justify-content-between mt-4 mb-3">
                <span>{{ $project->expeditions_count }} {{ __('Expeditions') }}</span>
                <span>{{ $transcriptionsCount }} {{ __('Digitizations') }}</span>
                <span>{{ $transcribersCount }} {{ __('Participants') }}</span>
            </div>
        </div>
        <div id="active-expeditions-main" class="col-sm-12 show">
            @include('common.expedition-sort', ['type' => 'active', 'route' => route('admin.expeditions.sort'), 'id' => $project->id])
            <div id="active-expeditions" class="row col-sm-12 mx-auto justify-content-center">
                @include('admin.expedition.partials.expedition', ['expeditions' => $expeditions])
            </div>
        </div>
        <div id="completed-expeditions-main" class="col-sm-12 collapse">
            @include('common.expedition-sort', ['type' => 'completed', 'route' => route('admin.expeditions.sort'), 'id' => $project->id])
            <div id="completed-expeditions" class="row col-sm-12 mx-auto justify-content-center">
                @include('admin.expedition.partials.expedition', ['expeditions' => $expeditionsCompleted])
            </div>
        </div>
    </div>
@endsection
