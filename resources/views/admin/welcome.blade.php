@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Welcome') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ __('Biospex') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="text-center">
        {{ __('This is the starting point where Biospex Projects and Events can be created.') }}
    </div>
    <div class="row">
        <div class="col-md-4 mt-5 p-1 mx-auto">
            <div class="card white mb-4 px-4 box-shadow h-100">
                <h2 class="text-center pt-4">{{ __('Projects') }}</h2>
                <div class="row card-body">
                    <div class="text-justify">
                        {{ __('Projects begin with the initiation of a new Group. Groups manage Projects which launch Expeditions. You can manage a Project by yourself or invite collaborators to the Group. A Group can manage multiple Projects, and you can belong to multiple Groups.') }}
                    </div>
                    <div class="mx-auto mt-5">
                        <a href="{{ route('admin.groups.create') }}" type="submit"
                           class="btn btn-primary pl-4 pr-4">{{ __('New Group') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mt-5 p-1 mx-auto">
            <div class="card white mb-4 px-4 box-shadow h-100">
                <h2 class="text-center pt-4">{{ __('Events') }}</h2>
                <div class="row card-body">
                    <div class="text-justify">
                        {{ __('Events focus on creating data for a particular Project. Event participants are organized into one or more Teams. If the focus is collaboration, create one Team. If it is competitive, then multiple Teams. You will share a Team-specific sign-up link with team members, at which they will provide just their Zooniverse userid. This enables BIOSPEX to keep score during activity at Zooniverse.') }}
                    </div>
                    <div class="mx-auto mt-5">
                        <a href="{{ route('admin.events.create') }}" type="submit"
                           class="btn btn-primary pl-4 pr-4">{{ __('New Event') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="text-center mb-4 mt-5 mx-auto">
            <h2 class="col-6 pt-4 mx-auto">{{ __('Have questions?') }}</h2>
            <p>Please read the FAQ section and if you still have questions, contact us.</p>
            <a href="{{ route('front.contact.index') }}"
               class="btn btn-primary mx-auto text-uppercase">{{ __('Contact_us') }}</a>
        </div>
    </div>
@endsection