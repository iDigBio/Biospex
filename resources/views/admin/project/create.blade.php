@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Create New Project') }}
@stop

{{-- Content --}}
@section('content')

    <div class="row">
        <div class="col-sm-10 mx-auto">
            <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                <div class="col-12">
                    <form method="post" action="{{ route('admin.projects.store') }}" role="form"
                          enctype="multipart/form-data">
                        {!! csrf_field() !!}
                        <div class="form-row">
                            <div class="form-group col-sm-6 {{ ($errors->has('group_id')) ? 'has-error' : '' }}">
                                <label for="group_id" class="col-form-label required">{{ __('Group') }}:</label>
                                <select name="group_id" id="group_id" class="form-control custom-select" required>
                                    @foreach($groupOptions as $key => $name)
                                        <option {{ $key === old('group_id') ? ' selected=selected' : '' }} value="{{ $key }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                {{ ($errors->has('group_id') ? $errors->first('group_id') : '') }}
                            </div>

                            <div class="form-group col-sm-6 {{ ($errors->has('status')) ? 'has-error' : '' }}">
                                <label for="status" class="col-form-label required">{{ __('Status') }}:</label>
                                <select name="status" id="status" class="form-control custom-select" required>
                                    @foreach($statusOptions as $key => $name)
                                        <option value="{{ $key }}"{{ $key === old('status') ? ' selected=selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                {{ ($errors->has('status') ? $errors->first('status') : '') }}
                            </div>
                        </div>

                        <div class="form-group {{ ($errors->has('title')) ? 'has-error' : '' }}">
                            <label for="title" class="col-form-label required">{{ __('Title') }}:</label>
                            <input type="text" class="form-control" id="title" name="title"
                                   value="{{ old('title') }}" required>
                            {{ ($errors->has('title') ? $errors->first('title') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('contact')) ? 'has-error' : '' }}">
                            <label for="contact" class="col-form-label required">{{ __('Contact') }}:</label>
                            <input type="text" class="form-control" id="contact" name="contact"
                                   value="{{ old('contact') }}" required>
                            {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
                            <label for="contact_email" class="col-form-label required">{{ __('Contact Email') }}
                                :</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email"
                                   value="{{ old('contact_email') }}" required>
                            {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('contact_title')) ? 'has-error' : '' }}">
                            <label for="contact_title" class="col-form-label required">{{ __('Contact Title') }}
                                :</label>
                            <input type="text" class="form-control" id="contact_title" name="contact_title"
                                   value="{{ old('contact_title') }}" required>
                            {{ ($errors->has('contact_title') ? $errors->first('contact_title') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('organization')) ? 'has-error' : '' }}">
                            <label for="organization" class="col-form-label">{{ __('Organization') }}:</label>
                            <input type="text" class="form-control" id="organization" name="organization"
                                   value="{{ old('organization') }}">
                            {{ ($errors->has('organization') ? $errors->first('organization') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('organization_website')) ? 'has-error' : '' }}">
                            <label for="organization_website"
                                   class="col-form-label pl-0">{{ __('Organization Website') }}:</label>
                            <input type="url" class="form-control" id="organization_website" name="organization_website"
                                   value="{{ old('organization_website') }}">
                            {{ ($errors->has('organization_website') ? $errors->first('organization_website') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('project_partners')) ? 'has-error' : '' }}">
                            <label for="project_partners" class="col-form-label">{{ __('Project Partners') }}:</label>
                            <textarea id="project_partners" name="project_partners"
                                      class="form-control">{{ old('project_partners') }}</textarea>
                            {{ ($errors->has('project_partners') ? $errors->first('project_partners') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('funding_source')) ? 'has-error' : '' }}">
                            <label for="funding_source" class="col-form-label">{{ __('Funding Source') }}:</label>
                            <textarea id="funding_source" name="funding_source"
                                      class="form-control">{{ old('funding_source') }}</textarea>
                            {{ ($errors->has('funding_source') ? $errors->first('funding_source') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('description_short')) ? 'has-error' : '' }}">
                            <label for="description_short" class="col-form-label required">{{ __('Short Description') }}
                                :</label>
                            <input type="text" class="form-control" id="description_short" name="description_short"
                                   value="{{ old('description_short') }}" required>
                            {{ ($errors->has('description_short') ? $errors->first('description_short') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('description_long')) ? 'has-error' : '' }}">
                            <label for="description_long" class="col-form-label required">{{ __('Long Description') }}
                                :</label>
                            <textarea id="description_long" name="description_long" class="form-control textarea"
                                      required>{{ old('description_long') }}</textarea>
                            {{ ($errors->has('description_long') ? $errors->first('description_long') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('incentives')) ? 'has-error' : '' }}">
                            <label for="incentives" class="col-form-label">{{ __('Incentives') }}:</label>
                            <textarea id="incentives" name="incentives"
                                      class="form-control">{{ old('incentives') }}</textarea>
                            {{ ($errors->has('incentives') ? $errors->first('incentives') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('geographic_scope')) ? 'has-error' : '' }}">
                            <label for="geographic_scope" class="col-form-label">{{ __('Geographic Scope') }}:</label>
                            <input type="text" class="form-control" id="geographic_scope" name="geographic_scope"
                                   value="{{ old('geographic_scope') }}">
                            {{ ($errors->has('geographic_scope') ? $errors->first('geographic_scope') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('taxonomic_scope')) ? 'has-error' : '' }}">
                            <label for="taxonomic_scope" class="col-form-label">{{ __('Taxonomic Scope') }}:</label>
                            <input type="text" class="form-control" id="taxonomic_scope" name="taxonomic_scope"
                                   value="{{ old('taxonomic_scope') }}">
                            {{ ($errors->has('taxonomic_scope') ? $errors->first('taxonomic_scope') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('temporal_scope')) ? 'has-error' : '' }}">
                            <label for="temporal_scope" class="col-form-label">{{ __('Temporal Scope') }}:</label>
                            <input type="text" class="form-control" id="temporal_scope" name="temporal_scope"
                                   value="{{ old('temporal_scope') }}">
                            {{ ($errors->has('temporal_scope') ? $errors->first('temporal_scope') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
                            <label for="keywords" class="col-form-label required">{{ __('Keywords') }}:</label>
                            <input type="text" class="form-control" id="keywords" name="keywords"
                                   value="{{ old('keywords') }}" required>
                            {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('blog_url')) ? 'has-error' : '' }}">
                            <label for="blog_url" class="col-form-label">{{ __('Blog URL') }}:</label>
                            <input type="url" class="form-control" id="blog_url" name="blog_url"
                                   value="{{ old('blog_url') }}">
                            {{ ($errors->has('blog_url') ? $errors->first('blog_url') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('facebook')) ? 'has-error' : '' }}">
                            <label for="facebook" class="col-form-label">{{ __('Facebook URL') }}:</label>
                            <input type="url" class="form-control" id="facebook" name="facebook"
                                   value="{{ old('facebook') }}">
                            {{ ($errors->has('facebook') ? $errors->first('facebook') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('twitter')) ? 'has-error' : '' }}">
                            <label for="twitter" class="col-form-label">{{ __('Twitter URL') }}:</label>
                            <input type="url" class="form-control" id="twitter" name="twitter"
                                   value="{{ old('twitter') }}">
                            {{ ($errors->has('twitter') ? $errors->first('twitter') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('activities')) ? 'has-error' : '' }}">
                            <label for="activities" class="col-form-label">{{ __('Activities') }}:</label>
                            <input type="text" class="form-control" id="activities" name="activities"
                                   value="{{ old('activities') }}">
                            {{ ($errors->has('activities') ? $errors->first('activities') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('language_skills')) ? 'has-error' : '' }}">
                            <label for="language_skills" class="col-form-label">{{ __('Language Skills') }}:</label>
                            <input type="text" class="form-control" id="language_skills" name="language_skills"
                                   value="{{ old('language_skills') }}">
                            {{ ($errors->has('language_skills') ? $errors->first('language_skills') : '') }}
                        </div>

                        <div class="form-group {{ ($errors->has('workflow_id')) ? 'has-error' : '' }}">
                            <label for="workflow_id" class="col-form-label col-12 required">{{ __('Workflows') }}
                                :</label>
                            <select name="workflow_id" id="workflow_id"
                                    class="form-control custom-select col-sm-5" required>
                                @foreach($workflowOptions as $key => $name)
                                    <option value="{{ $key }}"{{ $key === old('workflow_id') ? ' selected=selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            {{ ($errors->has('workflow_id') ? $errors->first('workflow_id') : '') }}
                        </div>

                        <div class="form-row mt-4">
                            <div class="form-group col-sm-6 mt-4 {{ ($errors->has('logo')) ? 'has-error' : '' }}">
                                <div class="custom-file">
                                    <label for="logo" class="custom-file-label">{{ __('Logo: Max 300wx300h') }}:</label>
                                    <input type="file" class="form-control custom-file-input" name="logo" id="logo">
                                    {{ ($errors->has('logo') ? $errors->first('logo') : '') }}
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <img style="display: inline" src=""/>
                            </div>
                        </div>

                        <div class="form-row mt-4">
                            <div class="form-group col-sm-6">
                                <label for="banner" class="col-form-label">{{ __('Banner') }}:</label>
                                <input type="text" class="form-control" id="banner" name="banner"
                                       value="{{ GeneralHelper::projectBannerFileName(old('banner_file', 'banner-trees.jpg')) }}"
                                       readonly>
                            </div>
                            <div class="form-group col-sm-4 pt-3">
                                <a href="#" data-toggle="modal" data-target="#project-banner-modal"
                                   data-hover="tooltip" title="{{ __('Click to change banner') }}">
                                    Click to change banner
                                    <img class="img-fluid" id="banner-img"
                                         src="{{ GeneralHelper::projectBannerFileUrl(old('banner')) }}"/>
                                </a>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="resources" class="col-form-label">{{ __('Resources') }}:</label>
                            <div class="controls col-sm-12">
                                @if($errors->has('resources.*'))
                                    <span class="has-error">{{ $errors->first('resources.*') }}</span>
                                @endif
                                @include('admin.project.partials.resources')
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <input type="hidden" name="entries" value="{{ old('entries', 1) }}">
                            <button type="submit" class="btn btn-primary">{{ __('SUBMIT') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('admin.partials.project-banner-modal')
@endsection

