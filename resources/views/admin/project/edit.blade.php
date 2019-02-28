@extends('admin.layout.default')

{{-- Web site Title --}}
@section('title')
    {{ __('Edit') }} {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
    @include('admin.project.partials.project-panel')
    <div class="row">
        <div class="col-sm-10 mx-auto">
            <div class="card white box-shadow pt-2 pb-5 my-5 p-sm-5">
                <div class="col-12">
                    <h2 class="text-center content-header mb-4">{{ __('EDIT PROJECT') }}</h2>
                    <form method="post" action="{{ route('admin.projects.update', $project->id) }}" role="form"
                          enctype="multipart/form-data">
                        {!! method_field('put') !!}
                        {!! csrf_field() !!}
                        <input type="hidden" name="entries" value="{{ old('entries', $resourceCount) }}">
                        <input type="hidden" name="id" value="{{ $project->id }}">
                        <div class="form-row">
                            <div class="form-group col-sm-6">
                                <label for="group_id"
                                       class="col-form-label required">{{ __('Group') }}
                                    :</label>
                                <select name="group_id" id="group_id"
                                        class="form-control custom-select {{ ($errors->has('group_id')) ? 'is-invalid' : '' }}"
                                        required>
                                    @foreach($groupOptions as $key => $name)
                                        <option {{ $key === old('group_id', $project->group_id) ?
                                        ' selected=selected' : '' }} value="{{ $key }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback">{{ $errors->first('group_id') }}</span>
                            </div>

                            <div class="form-group col-sm-6">
                                <label for="status" class="col-form-label required">{{ __('Status') }}:</label>
                                <select name="status" id="status"
                                        class="form-control custom-select {{ ($errors->has('status')) ? 'is-invalid' : '' }}"
                                        required>
                                    @foreach($statusOptions as $key => $name)
                                        <option value="{{ $key }}"{{ $key === old('status', $project->status) ?
                                        ' selected=selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback">{{ $errors->first('status') }}</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="title" class="col-form-label required">{{ __('Title') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('title')) ? 'is-invalid' : '' }}"
                                   id="title" name="title"
                                   value="{{ old('title', $project->title) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('title') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="contact" class="col-form-label required">{{ __('Contact') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('contact')) ? 'is-invalid' : '' }}"
                                   id="contact" name="contact"
                                   value="{{ old('contact', $project->contact) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('contact') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="contact_email" class="col-form-label required">{{ __('Contact Email') }}
                                :</label>
                            <input type="email"
                                   class="form-control {{ ($errors->has('contact_email')) ? 'is-invalid' : '' }}"
                                   id="contact_email" name="contact_email"
                                   value="{{ old('contact_email', $project->contact_email) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('contact_email') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="contact_title"
                                   class="col-form-label {{ ($errors->has('contact_title')) ? 'is-invalid' : '' }} required">
                                {{ __('Contact Title') }}
                                :</label>
                            <input type="text" class="form-control" id="contact_title" name="contact_title"
                                   value="{{ old('contact_title', $project->contact_title) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('contact_title') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="organization" class="col-form-label">{{ __('Organization') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('organization')) ? 'is-invalid' : '' }}"
                                   id="organization" name="organization"
                                   value="{{ old('organization', $project->organization) }}">
                            <span class="invalid-feedback">{{ $errors->first('organization') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="organization_website"
                                   class="col-form-label pl-0 {{ ($errors->has('organization_website')) ? 'is-invalid' : '' }}">
                                {{ __('Organization Website') }}
                                :</label>
                            <input type="url" class="form-control" id="organization_website" name="organization_website"
                                   value="{{ old('organization_website', $project->organization_website) }}">
                            <span class="invalid-feedback">{{ $errors->first('organization_website') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="project_partners" class="col-form-label">{{ __('Project Partners') }}:</label>
                            <textarea id="project_partners" name="project_partners"
                                      class="form-control {{ ($errors->has('project_partners')) ? 'is-invalid' : '' }}">
                                {{ old('project_partners', $project->project_partners) }}</textarea>
                            <span class="invalid-feedback">{{ $errors->first('project_partners') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="funding_source" class="col-form-label">{{ __('Funding Source') }}:</label>
                            <textarea id="funding_source" name="funding_source"
                                      class="form-control {{ ($errors->has('funding_source')) ? 'is-invalid' : '' }}">
                                {{ old('funding_source', $project->funding_source) }}</textarea>
                            <span class="invalid-feedback">{{ $errors->first('funding_source') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="description_short" class="col-form-label required">{{ __('Short Description') }}
                                :</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('description_short')) ? 'is-invalid' : '' }}"
                                   id="description_short" name="description_short"
                                   value="{{ old('description_short', $project->description_short) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('description_short') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="description_long" class="col-form-label required">{{ __('Long Description') }}
                                :</label>
                            <textarea id="description_long" name="description_long"
                                      class="form-control textarea {{ ($errors->has('description_long')) ? 'is-invalid' : '' }}"
                                      required>{{ old('description_long', $project->description_long) }}</textarea>
                            <span class="invalid-feedback">{{ $errors->first('description_long') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="incentives" class="col-form-label">{{ __('Incentives') }}:</label>
                            <textarea id="incentives" name="incentives"
                                      class="form-control {{ ($errors->has('incentives')) ? 'is-invalid' : '' }}">
                                {{ old('incentives', $project->incentives) }}</textarea>
                            <span class="invalid-feedback">{{ $errors->first('incentives') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="geographic_scope" class="col-form-label">{{ __('Geographic Scope') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('geographic_scope')) ? 'is-invalid' : '' }}"
                                   id="geographic_scope" name="geographic_scope"
                                   value="{{ old('geographic_scope', $project->geographic_scope) }}">
                            <span class="invalid-feedback">{{ $errors->first('geographic_scope') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="taxonomic_scope" class="col-form-label">{{ __('Taxonomic Scope') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('taxonomic_scope')) ? 'is-invalid' : '' }}"
                                   id="taxonomic_scope" name="taxonomic_scope"
                                   value="{{ old('taxonomic_scope', $project->taxonomic_scope) }}">
                            <span class="invalid-feedback">{{ $errors->first('taxonomic_scope') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="temporal_scope" class="col-form-label">{{ __('Temporal Scope') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('temporal_scope')) ? 'is-invalid' : '' }}"
                                   id="temporal_scope" name="temporal_scope"
                                   value="{{ old('temporal_scope', $project->temporal_scope) }}">
                            <span class="invalid-feedback">{{ $errors->first('temporal_scope') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="keywords" class="col-form-label required">{{ __('Keywords') }}:</label>
                            <input type="text" class="form-control {{ ($errors->has('keywords')) ? 'is-invalid' : '' }}"
                                   id="keywords" name="keywords"
                                   value="{{ old('keywords', $project->keywords) }}" required>
                            <span class="invalid-feedback">{{ $errors->first('keywords') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="blog_url" class="col-form-label">{{ __('Blog URL') }}:</label>
                            <input type="url" class="form-control {{ ($errors->has('blog_url')) ? 'is-invalid' : '' }}"
                                   id="blog_url" name="blog_url"
                                   value="{{ old('blog_url', $project->blog_url) }}">
                            <span class="invalid-feedback">{{ $errors->first('blog_url') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="facebook" class="col-form-label">{{ __('Facebook URL') }}:</label>
                            <input type="url" class="form-control {{ ($errors->has('facebook')) ? 'is-invalid' : '' }}"
                                   id="facebook" name="facebook"
                                   value="{{ old('facebook', $project->facebook) }}">
                            <span class="invalid-feedback">{{ $errors->first('facebook') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="twitter" class="col-form-label">{{ __('Twitter URL') }}:</label>
                            <input type="url" class="form-control {{ ($errors->has('twitter')) ? 'is-invalid' : '' }}"
                                   id="twitter" name="twitter"
                                   value="{{ old('twitter', $project->twitter) }}">
                            <span class="invalid-feedback">{{ $errors->first('twitter') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="activities" class="col-form-label">{{ __('Activities') }}:</label>
                            <input type="text" class="form-control"
                                   {{ ($errors->has('activities')) ? 'is-invalid' : '' }}
                                   id="activities" name="activities"
                                   value="{{ old('activities', $project->activities) }}">
                            <span class="invalid-feedback">{{ $errors->first('activities') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="language_skills" class="col-form-label">{{ __('Language Skills') }}:</label>
                            <input type="text"
                                   class="form-control {{ ($errors->has('language_skills')) ? 'is-invalid' : '' }}"
                                   id="language_skills" name="language_skills"
                                   value="{{ old('language_skills', $project->language_skills) }}">
                            <span class="invalid-feedback">{{ $errors->first('language_skills') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="workflow_id" class="col-form-label col-12 required">{{ __('Workflows') }}
                                :</label>
                            <select name="workflow_id" id="workflow_id"
                                    class="form-control custom-select col-sm-5 {{ ($errors->has('workflow_id')) ? 'is-invalid' : '' }}"
                                    {{ $disableWorkflow }}
                                    required>
                                @foreach($workflowOptions as $key => $name)
                                    <option value="{{ $key }}"{{ $key === old('workflow_id', $project->workflow_id) ? ' selected=selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @if( ! empty($disableWorkflow))
                                <input type="hidden" name="workflow_id" value="{{ old('workflow_id', $project->workflow_id) }}">
                            @endif
                            <span class="invalid-feedback">{{ $errors->first('workflow_id') }}</span>
                        </div>

                        <div class="form-row mt-4">
                            <div class="form-group col-sm-6 mt-4">
                                <div class="custom-file">
                                    <label for="logo" class="custom-file-label">{{ __('Logo: Max 300wx300h') }}:</label>
                                    <input type="file"
                                           class="form-control custom-file-input {{ ($errors->has('logo')) ? 'is-invalid' : '' }}"
                                           name="logo" id="logo"
                                           accept="image/svg+xml, image/png, image/jpg">
                                    <span class="invalid-feedback">{{ $errors->first('logo') }}</span>
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <img class="img-fluid" style="display: inline; width: 100px; height: 100px;"
                                     src="{{ $project->present()->logo_url }}"/>
                            </div>
                        </div>

                        <div class="form-row mt-4">
                            <div class="form-group col-sm-6">
                                <label for="banner" class="col-form-label">{{ __('Banner') }}:</label>
                                <input type="text" class="form-control" id="banner" name="banner"
                                       value="{{ $project->present()->banner_file_name ?? 'banner-trees.jpg' }}"
                                       readonly>
                            </div>
                            <div class="form-group col-sm-4 pt-3">
                                <a href="#" data-toggle="modal" data-target="#project-banner-modal"
                                   data-hover="tooltip" title="{{ __('Click to change banner') }}">
                                    Click to change banner
                                    <img class="img-fluid" id="banner-img"
                                         src="{{ $project->present()->banner_file_url }}"/>
                                </a>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="resources" class="col-form-label">{{ __('Resources') }}:</label>
                            <div class="controls col-sm-12">
                                @include('admin.project.partials.resources')
                            </div>
                        </div>
                        @include('common.cancel-submit-buttons')
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('admin.partials.project-banner-modal')
@endsection