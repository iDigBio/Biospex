@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Welcome') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center pt-4">{{ __('BIOSPEX Expeditions') }}</h2>
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
                        {{ __('Projects begin with the initiation of a new Group.
                        Groups manage Projects which launch Expeditions. You can
                        manage a Project yourself or invite collaborators to the group.
                        You can have as many groups as are necessary to manage.') }}
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
                        {{ __('Events are based on Projects. They contain single or multiple
                        Teams consisting of users. Create a single group for a class event or
                        create multiple Teams for competitions. After creating your Event and Teams,
                        you will be given invite links where users can sign up for a particular
                        Team using their Notes From Nature user ID.') }}
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
            <a href="{{ route('contact.get.index') }}" class="btn btn-primary mx-auto">{{ __('CONTACT US') }}</a>
        </div>
    </div>
@endsection