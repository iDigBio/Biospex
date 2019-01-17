@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
    <div class="row">
        <div class="col-sm-10 mx-auto">
            <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                <h1 class="text-center project-wide text-uppercase">{{ $project->title }}</h1>
                <div class="col-12">
                    <div class="d-flex justify-content-between mt-4 mb-3">
                        {!! $project->present()->project_page_icon_lrg !!}
                        {!! $project->present()->project_import_icon_lrg !!}
                        {!! $project->present()->project_explore_icon_lrg !!}
                        {!! $project->present()->project_advertise_icon_lrg !!}
                        {!! $project->present()->project_statistics_icon_lrg !!}
                        {!! $project->present()->project_edit_icon_lrg !!}
                        {!! $project->present()->project_clone_icon_lrg !!}
                        @can('isOwner', $project->group)
                            {!! $project->present()->project_delete_icon_lrg !!}
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8 offset-md-2">
            <h1 class="text-center project-headers" id="expeditions">{{ __('Expeditions') }}</h1>
            <div class="text-center mt-4">
                <button class="toggle-view-btn btn btn-primary pl-4 pr-4"
                        data-toggle="collapse"
                        data-target="#active-expeditions-main,#completed-expeditions-main"
                        data-value="{{ __('View Active Expeditions') }}"
                >{{ __('View Completed Expeditions') }}</button>
            </div>
            <div class="d-flex justify-content-between mt-4 mb-3">
                <span>{{ $project->expeditions->count() }} Expeditions</span>
                <span>{{ $project->transcriptions_count }} Transcriptions</span>
                <span>{{ $project->unique_transcribers_count }} Transcribers</span>
            </div>
            <hr class="header mx-auto">
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
    @include('admin.partials.import-modal')
    @include('admin.partials.expedition-download-modal')
@endsection

