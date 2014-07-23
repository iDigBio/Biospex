@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('projects.project')}}
@stop

{{-- Content --}}
@section('content')

<!-- Container -->
<div class="container">
    <!-- Notifications -->
    @include('layouts/notifications')
    <!-- ./ notifications -->

    <!-- Content -->
    <div class="row">
        <div class="col-md-6">
            <h2>Temp public page for project</h2>

            <p>Projects will have outward facing pages for the public showing various expeditions, statistics,
                and other relevant material rgarding the project.</p>

        </div>
    </div>
    <!-- ./ content -->
</div>

@stop