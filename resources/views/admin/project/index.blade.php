@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ t('Projects') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4 text-uppercase">{{ t('Biospex Projects') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="col-sm-3 mx-auto text-center my-4">
        <a href="{{ route('admin.projects.create') }}" type="submit"
           class="btn btn-primary text-uppercase"><i class="fas fa-plus-circle"></i> {{ t('New Project') }}</a>
    </div>
    <div class="row">
        @include('common.project-sort', ['route' => route('admin.projects.sort')])
    </div>
    <div id="projects" class="row col-sm-12 mx-auto justify-content-center">
        @include('admin.project.partials.project', ['projects' => $projects])
    </div>
@endsection

