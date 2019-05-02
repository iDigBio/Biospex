@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.welcome') }}
@stop

{{-- Content --}}
@section('content')
    <h2 class="text-center text-uppercase pt-4">{{ __('pages.biospex') }}</h2>
    <hr class="header mx-auto" style="width:300px;">
    <div class="text-center">
        {{ __('pages.welcome_msg') }}
    </div>
    <div class="row">
        <div class="col-md-4 mt-5 p-1 mx-auto">
            <div class="card white mb-4 px-4 box-shadow h-100">
                <h2 class="text-center pt-4">{{ __('pages.projects') }}</h2>
                <div class="row card-body">
                    <div class="text-justify">
                        {{ __('pages.welcome_project_txt') }}
                    </div>
                    <div class="mx-auto mt-5">
                        <a href="{{ route('admin.groups.create') }}" type="submit"
                           class="btn btn-primary pl-4 pr-4">{{ __('pages.new') }} {{ __('pages.group') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mt-5 p-1 mx-auto">
            <div class="card white mb-4 px-4 box-shadow h-100">
                <h2 class="text-center pt-4">{{ __('pages.events') }}</h2>
                <div class="row card-body">
                    <div class="text-justify">
                        {{ __('pages.welcome_event_txt') }}
                    </div>
                    <div class="mx-auto mt-5">
                        <a href="{{ route('admin.events.create') }}" type="submit"
                           class="btn btn-primary pl-4 pr-4">{{ __('pages.new') }} {{ __('pages.event') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="text-center mb-4 mt-5 mx-auto">
            <h2 class="col-6 pt-4 mx-auto">{{ __('pages.faq_questions') }}</h2>
            <p>Please read the FAQ section and if you still have questions, contact us.</p>
            <a href="{{ route('front.contact.index') }}" class="btn btn-primary mx-auto text-uppercase">{{ __('pages.contact_us') }}</a>
        </div>
    </div>
@endsection