@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{ trans('pages.clone') }} {{ trans('projects.project') }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('web.projects.show.title', $project, trans('pages.clone') . ' ' . trans('projects.project')) !!}
    <div class="col-xs-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.clone') }}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                    'route' => ['web.projects.store'],
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                ]) !!}

                <div class="form-group required {{ ($errors->has('group_id')) ? 'has-error' : '' }}" for="group">
                    {!! Form::label('group_id', trans('forms.group'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-3">
                        {{ ($errors->has('group_id') ? $errors->first('group_id') : '') }}
                        {!! Form::select('group_id', $selectGroups, $project->group_id, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('status')) ? 'has-error' : '' }}" for="group">
                    {!! Form::label('status', trans('forms.status'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-3">
                        {{ ($errors->has('status') ? $errors->first('status') : '') }}
                        {!! Form::select('status', $statusSelect, $project->status, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
                    {!! Form::label('title', trans('forms.title'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('title') ? $errors->first('title') : '') }}
                        {!! Form::text('title', $project->title, array('class' => 'form-control', 'placeholder' => trans('forms.title'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact')) ? 'has-error' : '' }}">
                    {!! Form::label('contact', trans('forms.contact'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
                        {!! Form::text('contact', $project->contact, array('class' => 'form-control', 'placeholder' => trans('forms.contact'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
                    {!! Form::label('contact_email', trans('forms.contact_email'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
                        {!! Form::text('contact_email', $project->contact_email, array('class' => 'form-control', 'placeholder' => trans('forms.contact_email'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact_title')) ? 'has-error' : '' }}">
                    {!! Form::label('contact_title', trans('forms.contact_title'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact_title') ? $errors->first('contact_title') : '') }}
                        {!! Form::text('contact_title', $project->contact_title, array('class' => 'form-control', 'placeholder' => trans('forms.contact_title'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('organization')) ? 'has-error' : '' }}">
                    {!! Form::label('organization', trans('forms.organization'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('organization') ? $errors->first('organization') : '') }}
                        {!! Form::text('organization', $project->organization, array('class' => 'form-control', 'placeholder' => trans('forms.organization_format'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('organization_website')) ? 'has-error' : '' }}">
                    {!! Form::label('organization_website', trans('forms.organization_website'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('organization_website') ? $errors->first('organization_website') : '') }}
                        {!! Form::text('organization_website', $project->organization_website, array('class' => 'form-control', 'placeholder' => trans('forms.organization_website_format'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('project_partners')) ? 'has-error' : '' }}">
                    {!! Form::label('project_partners', trans('forms.project_partners'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('project_partners') ? $errors->first('project_partners') : '') }}
                        {!! Form::textarea('project_partners', $project->project_partners, array('class' => 'form-control', 'placeholder' => trans('forms.project_partners'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('funding_source')) ? 'has-error' : '' }}">
                    {!! Form::label('funding_source', trans('forms.funding_source'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('funding_source') ? $errors->first('funding_source') : '') }}
                        {!! Form::textarea('funding_source', $project->funding_source, array('class' => 'form-control', 'placeholder' => trans('forms.funding_source'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('description_short')) ? 'has-error' : '' }}">
                    {!! Form::label('description_short', trans('forms.description_short'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('description_short') ? $errors->first('description_short') : '') }}
                        {!! Form::text('description_short', $project->description_short, array('class' => 'form-control', 'placeholder' => trans('forms.description_short_max'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('description_long')) ? 'has-error' : '' }}">
                    {!! Form::label('description_long', trans('forms.description_long'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('description_long') ? $errors->first('description_long') : '') }}
                        {!! Form::textarea('description_long', $project->description_long, array('class' => 'form-control textarea', 'placeholder' => trans('forms.description_long'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('incentives')) ? 'has-error' : '' }}">
                    {!! Form::label('incentives', trans('forms.incentives'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('incentives') ? $errors->first('incentives') : '') }}
                        {!! Form::textarea('incentives', $project->incentives, array('size' => '30x3', 'class' => 'form-control', 'placeholder' => trans('forms.incentives'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('geographic_scope')) ? 'has-error' : '' }}">
                    {!! Form::label('geographic_scope', trans('forms.geographic_scope'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('geographic_scope') ? $errors->first('geographic_scope') : '') }}
                        {!! Form::text('geographic_scope', $project->geographic_scope, array('class' => 'form-control', 'placeholder' => trans('forms.geographic_scope'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('taxonomic_scope')) ? 'has-error' : '' }}">
                    {!! Form::label('taxonomic_scope', trans('forms.taxonomic_scope'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('taxonomic_scope') ? $errors->first('taxonomic_scope') : '') }}
                        {!! Form::text('taxonomic_scope', $project->taxonomic_scope, array('class' => 'form-control', 'placeholder' => trans('forms.taxonomic_scope'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('temporal_scope')) ? 'has-error' : '' }}">
                    {!! Form::label('temporal_scope', trans('forms.temporal_scope'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('temporal_scope') ? $errors->first('temporal_scope') : '') }}
                        {!! Form::text('temporal_scope', $project->temporal_scope, array('class' => 'form-control', 'placeholder' => trans('forms.temporal_scope'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
                    {!! Form::label('keywords', trans('forms.keywords'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
                        {!! Form::text('keywords', $project->keywords, array('class' => 'form-control', 'placeholder' => trans('forms.keywords'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('blog_url')) ? 'has-error' : '' }}">
                    {!! Form::label('blog_url', trans('forms.blog_url'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('blog_url') ? $errors->first('blog_url') : '') }}
                        {!! Form::text('blog_url', $project->blog_url, array('class' => 'form-control', 'placeholder' => trans('forms.blog_url_format'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('facebook')) ? 'has-error' : '' }}">
                    {!! Form::label('facebook', trans('forms.facebook'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('facebook') ? $errors->first('facebook') : '') }}
                        {!! Form::text('facebook', $project->facebook, array('class' => 'form-control', 'placeholder' => trans('forms.facebook_format'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('twitter')) ? 'has-error' : '' }}">
                    {!! Form::label('twitter', trans('forms.twitter'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('twitter') ? $errors->first('twitter') : '') }}
                        {!! Form::text('twitter', $project->twitter, array('class' => 'form-control', 'placeholder' => trans('forms.twitter_format'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('activities')) ? 'has-error' : '' }}">
                    {!! Form::label('activities', trans('forms.activities'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('activities') ? $errors->first('activities') : '') }}
                        {!! Form::text('activities', $project->activities, array('class' => 'form-control', 'placeholder' => trans('forms.activities'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('language_skills')) ? 'has-error' : '' }}">
                    {!! Form::label('language_skills', trans('forms.language_skills'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('language_skills') ? $errors->first('language_skills') : '') }}
                        {!! Form::text('language_skills', $project->language_skills, array('class' => 'form-control', 'placeholder' => trans('forms.language_skills'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('workflow_id')) ? 'has-error' : '' }}">
                    {!! Form::label('workflow_id', trans('forms.workflows'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-4">
                        {{ ($errors->has('workflow_id') ? $errors->first('workflow_id') : '') }}
                        {!! Form::select('workflow_id', $workflows, $project->workflow_id, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('logo')) ? 'has-error' : '' }}">
                    {!! Form::label('logo', trans('forms.logo'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-5">
                        {{ ($errors->has('logo') ? $errors->first('logo') : '') }}
                        {!! Form::file('logo') !!} {{ trans('forms.logo_max') }}
                    </div>
                    <div class="col-sm-5">
                        <img src="{{ $project->logo->url('thumb') }}"/>
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('banner')) ? 'has-error' : '' }}">
                    {!! Form::label('', trans('forms.banner'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-5">
                        {{ ($errors->has('banner') ? $errors->first('banner') : '') }}
                        {!! Form::file('banner') !!} {{ trans('forms.banner_min') }}
                    </div>
                    <div class="col-sm-5">
                        <img src="{{ $project->banner->url('thumb') }}"/>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('', trans('forms.project_resources'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="controls col-sm-10">
                        @if($errors->has('resources.*'))
                            @for($i = 0; $i < Input::old('resourceFields'); $i++)
                                @include('frontend.projects.partials.resource-error')
                            @endfor
                        @else
                            @include('frontend.projects.partials.resource-create')
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        {!! Form::hidden('resourceFields', 1) !!}
                        {!! Form::submit(trans('buttons.create'), ['class' => 'btn btn-primary']) !!}
                        {!! link_to(URL::previous(), trans('buttons.cancel'), ['class' => 'btn btn-large btn-primary btn-danger']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop