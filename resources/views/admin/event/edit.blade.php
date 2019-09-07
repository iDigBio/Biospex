@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('pages.edit') }} {{ $event->title }}
@stop

{{-- Content --}}
@section('content')
    @include('admin.event.partials.event-panel')
    <div class="row">
        <div class="col-sm-10 mx-auto">
            <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                <div class="col-12">
                    <h2 class="text-center content-header text-uppercase mb-4">{{ __('pages.edit') }} {{ __('pages.event') }}</h2>

                    <form id="gridForm" method="post"
                          action="{{ route('admin.events.update', [$event->id]) }}"
                          role="form" enctype="multipart/form-data">
                        {!! method_field('put') !!}
                        @csrf
                        <input type="hidden" name="entries" value="{{ old('entries', $teamsCount) }}">
                        <input type="hidden" name="owner_id" value="{{ Auth::id() }}">
                        <div class="form-group">
                            <div class="col-12 p-0">
                                <label for="project_id" class="col-form-label required">{{ __('pages.project') }}:</label>
                            </div>
                            <div class="col-6 p-0">
                                <select name="project_id" id="project_id"
                                        class="form-control custom-select {{ ($errors->has('project_id')) ? 'is-invalid' : '' }}"
                                        required>
                                    @foreach($projects as $key => $title)
                                        <option {{ $key == old('project_id', $event->project_id) ?
                                        ' selected=selected' : '' }} value="{{ $key }}">{{ $title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="title" class="col-form-label required">{{ __('pages.title') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}"
                                   id="title" name="title"
                                   value="{{ old('title', $event->title) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('title') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="description" class="col-form-label required">{{ __('pages.description') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('description')) ? 'is-invalid' : '' }}"
                                   id="description" name="description"
                                   value="{{ old('description', $event->description) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('description') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="contact" class="col-form-label required">{{ __('pages.contact') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('contact')) ? 'is-invalid' : '' }}"
                                   id="contact" name="contact"
                                   value="{{ old('contact', $event->contact) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('contact') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="contact_email" class="col-form-label required">{{ __('pages.contact') }} {{ __('pages.email') }}
                                :</label>
                            <input type="email"
                                   class="form-control {{ ($errors->has('contact_email')) ? 'is-invalid' : '' }}"
                                   id="contact_email" name="contact_email"
                                   value="{{ old('contact_email', $event->contact_email) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('contact_email') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="hashtag" class="col-form-label">{{ __('pages.hash_tags') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('hashtag')) ? 'is-invalid' : '' }}"
                                   id="hashtag" name="hashtag" placeholder="{{ __('pages.separated_by_commas') }}"
                                   value="{{ old('hashtag', $event->hashtag) }}">
                            <span class="invalid-feedback">{{ $errors->first('hashtag') }}</span>
                        </div>
                        <div class="form-row">
                        <div class="col-sm-4 form-group">
                            <label for="start_date"
                                   class="col-form-label required">{{ __('pages.start_date') }}
                                :</label>
                            <input type="text"
                                   class="form-control datetimepicker {{ ($errors->has('start_date')) ? 'is-invalid' : '' }}"
                                   id="start_date" name="start_date"
                                   value="{{ old('start_date', $event->start_date->setTimezone($event->timezone)->format('Y-m-d H:i')) }}"
                                   required>
                            <span class="invalid-feedback">{{ $errors->first('start_date') }}</span>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label for="end_date"
                                   class="col-form-label required">{{ __('pages.end_date') }}:</label>
                            <input type="text"
                                   class="form-control datetimepicker {{ ($errors->has('end_date')) ? 'is-invalid' : '' }}"
                                   id="end_date" name="end_date"
                                   value="{{ old('end_date', $event->end_date->setTimezone($event->timezone)->format('Y-m-d H:i')) }}"
                                   required>
                            <span class="invalid-feedback">{{ $errors->first('end_date') }}</span>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label for="timezone"
                                   class="col-form-label required">{{ __('pages.timezone') }}:</label>
                            <select name="timezone" id="timezone"
                                    class="form-control custom-select {{ ($errors->has('timezone')) ? 'is-invalid' : '' }}"
                                    required>
                                @foreach($timezones as $key => $value)
                                    <option {{ $key == old('timezone', $event->timezone) ?
                                        ' selected=selected' : '' }} value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        </div>

                        <div class="form-group">
                            <label for="teams" class="col-form-label">{{ __('pages.teams') }}:</label>
                            <div class="controls col-sm-12">
                                @include('admin.event.partials.teams', ['teams' => $event->teams, 'teamsCount' => $teamsCount])
                            </div>
                        </div>
                        @include('common.cancel-submit-buttons')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection