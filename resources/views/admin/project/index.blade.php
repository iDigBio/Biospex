@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Projects') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ __('BIOSPEX Projects') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="row">
        <div class="col-md-6 mx-auto mb-4 text-center">
            <span data-sort="title" data-order="asc" data-url="{{ route('projects.post.sort') }}" data-target="public-expeditions"
                  class="sortPage mr-2" style="color: #e83f29; cursor: pointer;"><i
                        class="fas fa-sort"></i> {{ __('TITLE') }}</span>
            <span data-sort="group" data-order="asc" data-url="{{ route('projects.post.sort') }}" data-target="public-expeditions"
                  class="sortPage ml-2" style="color: #e83f29; cursor: pointer;"><i
                        class="fas fa-sort"></i> {{ __('GROUP') }}</span>
        </div>
    </div>
    <div class="row col-sm-12 mx-auto justify-content-center" id="public-expeditions">
        @include('admin.project.partials.project', ['projects' => $projects])
    </div>
@endsection
