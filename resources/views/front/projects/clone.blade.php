@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{ trans('pages.clone') }} {{ trans('projects.project') }}
@stop

{{-- Content --}}
@section('content')
    {!! Breadcrumbs::render('projects.get.show.title', $project, trans('pages.clone') . ' ' . trans('projects.project')) !!}
    <div class="col-xs-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.clone') }}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                    'route' => ['projects.post.store'],
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                ]) !!}

                <div class="form-group required {{ ($errors->has('group_id')) ? 'has-error' : '' }}" for="group">
                    {!! Form::label('group_id', trans('forms.group'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::select('group_id', $selectGroups, $project->group_id, ['class' => 'selectpicker']) !!}
                    </div>
                    {{ ($errors->has('group_id') ? $errors->first('group_id') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('status')) ? 'has-error' : '' }}" for="group">
                    {!! Form::label('status', trans('forms.status'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::select('status', $statusSelect, $project->status, ['class' => 'selectpicker']) !!}
                    </div>
                    {{ ($errors->has('status') ? $errors->first('status') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
                    {!! Form::label('title', trans('forms.title'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('title', $project->title, array('class' => 'form-control', 'placeholder' => trans('forms.title'))) !!}
                    </div>
                    {{ ($errors->has('title') ? $errors->first('title') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('contact')) ? 'has-error' : '' }}">
                    {!! Form::label('contact', trans('forms.contact'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('contact', $project->contact, array('class' => 'form-control', 'placeholder' => trans('forms.contact'))) !!}
                    </div>
                    {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
                    {!! Form::label('contact_email', trans('forms.contact_email'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('contact_email', $project->contact_email, array('class' => 'form-control', 'placeholder' => trans('forms.contact_email'))) !!}
                    </div>
                    {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('contact_title')) ? 'has-error' : '' }}">
                    {!! Form::label('contact_title', trans('forms.contact_title'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('contact_title', $project->contact_email, array('class' => 'form-control', 'placeholder' => trans('forms.contact_title'))) !!}
                    </div>
                    {{ ($errors->has('contact_title') ? $errors->first('contact_title') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('organization')) ? 'has-error' : '' }}">
                    {!! Form::label('organization', trans('forms.organization'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('organization', $project->organization, array('class' => 'form-control', 'placeholder' => trans('forms.organization_format'))) !!}
                    </div>
                    {{ ($errors->has('organization') ? $errors->first('organization') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('organization_website')) ? 'has-error' : '' }}">
                    {!! Form::label('organization_website', trans('forms.organization_website'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('organization_website', $project->organization_website, array('class' => 'form-control', 'placeholder' => trans('forms.organization_website_format'))) !!}
                    </div>
                    {{ ($errors->has('organization_website') ? $errors->first('organization_website') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('project_partners')) ? 'has-error' : '' }}">
                    {!! Form::label('project_partners', trans('forms.project_partners'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('project_partners', $project->project_partners, array('class' => 'form-control', 'placeholder' => trans('forms.project_partners'))) !!}
                    </div>
                    {{ ($errors->has('project_partners') ? $errors->first('project_partners') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('funding_source')) ? 'has-error' : '' }}">
                    {!! Form::label('funding_source', trans('forms.funding_source'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('funding_source', $project->funding_source, array('class' => 'form-control', 'placeholder' => trans('forms.funding_source'))) !!}
                    </div>
                    {{ ($errors->has('funding_source') ? $errors->first('funding_source') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('description_short')) ? 'has-error' : '' }}">
                    {!! Form::label('description_short', trans('forms.description_short'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('description_short', $project->description_short, array('class' => 'form-control', 'placeholder' => trans('forms.description_short_max'))) !!}
                    </div>
                    {{ ($errors->has('description_short') ? $errors->first('description_short') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('description_long')) ? 'has-error' : '' }}">
                    {!! Form::label('description_long', trans('forms.description_long'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::textarea('description_long', $project->description_long, array('class' => 'form-control', 'placeholder' => trans('forms.description_long'))) !!}
                    </div>
                    {{ ($errors->has('description_long') ? $errors->first('description_long') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('incentives')) ? 'has-error' : '' }}">
                    {!! Form::label('incentives', trans('forms.incentives'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::textarea('incentives', $project->incentives, array('size' => '30x3', 'class' => 'form-control', 'placeholder' => trans('forms.incentives'))) !!}
                    </div>
                    {{ ($errors->has('incentives') ? $errors->first('incentives') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('geographic_scope')) ? 'has-error' : '' }}">
                    {!! Form::label('geographic_scope', trans('forms.geographic_scope'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('geographic_scope', $project->geographic_scope, array('class' => 'form-control', 'placeholder' => trans('forms.geographic_scope'))) !!}
                    </div>
                    {{ ($errors->has('geographic_scope') ? $errors->first('geographic_scope') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('taxonomic_scope')) ? 'has-error' : '' }}">
                    {!! Form::label('taxonomic_scope', trans('forms.taxonomic_scope'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('taxonomic_scope', $project->taxonomic_scope, array('class' => 'form-control', 'placeholder' => trans('forms.taxonomic_scope'))) !!}
                    </div>
                    {{ ($errors->has('taxonomic_scope') ? $errors->first('taxonomic_scope') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('temporal_scope')) ? 'has-error' : '' }}">
                    {!! Form::label('temporal_scope', trans('forms.temporal_scope'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('temporal_scope', $project->temporal_scope, array('class' => 'form-control', 'placeholder' => trans('forms.temporal_scope'))) !!}
                    </div>
                    {{ ($errors->has('temporal_scope') ? $errors->first('temporal_scope') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
                    {!! Form::label('keywords', trans('forms.keywords'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('keywords', $project->keywords, array('class' => 'form-control', 'placeholder' => trans('forms.keywords'))) !!}
                    </div>
                    {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('blog_url')) ? 'has-error' : '' }}">
                    {!! Form::label('blog_url', trans('forms.blog_url'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('blog_url', $project->blog_url, array('class' => 'form-control', 'placeholder' => trans('forms.blog_url_format'))) !!}
                    </div>
                    {{ ($errors->has('blog_url') ? $errors->first('blog_url') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('facebook')) ? 'has-error' : '' }}">
                    {!! Form::label('facebook', trans('forms.facebook'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('facebook', $project->facebook, array('class' => 'form-control', 'placeholder' => trans('forms.facebook_format'))) !!}
                    </div>
                    {{ ($errors->has('facebook') ? $errors->first('facebook') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('twitter')) ? 'has-error' : '' }}">
                    {!! Form::label('twitter', trans('forms.twitter'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('twitter', $project->twitter, array('class' => 'form-control', 'placeholder' => trans('forms.twitter_format'))) !!}
                    </div>
                    {{ ($errors->has('twitter') ? $errors->first('twitter') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('activities')) ? 'has-error' : '' }}">
                    {!! Form::label('activities', trans('forms.activities'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('activities', $project->activities, array('class' => 'form-control', 'placeholder' => trans('forms.activities'))) !!}
                    </div>
                    {{ ($errors->has('activities') ? $errors->first('activities') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('language_skills')) ? 'has-error' : '' }}">
                    {!! Form::label('language_skills', trans('forms.language_skills'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {!! Form::text('language_skills', $project->language_skills, array('class' => 'form-control', 'placeholder' => trans('forms.language_skills'))) !!}
                    </div>
                    {{ ($errors->has('language_skills') ? $errors->first('language_skills') : '') }}
                </div>

                <div class="form-group required {{ ($errors->has('workflow_id')) ? 'has-error' : '' }}">
                    {!! Form::label('workflow_id', trans('forms.workflows'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-4">
                        {!! Form::select('workflow_id', $workflows, $project->workflow_id, ['class' => 'selectpicker']) !!}
                    </div>
                    {{ ($errors->has('workflow_id') ? $errors->first('workflow_id') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('logo')) ? 'has-error' : '' }}">
                    {!! Form::label('logo', trans('forms.logo'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-5">
                        {!! Form::file('logo') !!} {{ trans('forms.logo_max') }}
                    </div>
                    <div class="col-sm-5">
                        <img src="{{ $project->logo->url('thumb') }}"/>
                    </div>
                    {{ ($errors->has('logo') ? $errors->first('logo') : '') }}
                </div>

                <div class="form-group {{ ($errors->has('banner')) ? 'has-error' : '' }}">
                    {!! Form::label('banner', trans('forms.banner'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-5">
                        {!! Form::file('banner') !!} {{ trans('forms.banner_min') }}
                    </div>
                    <div class="col-sm-5">
                        <img src="{{ $project->banner->url('thumb') }}"/>
                    </div>
                    {{ ($errors->has('banner') ? $errors->first('banner') : '') }}
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        {!! Form::submit(trans('buttons.create'), ['class' => 'btn btn-primary']) !!}
                        {!! link_to(URL::previous(), trans('buttons.cancel'), ['class' => 'btn btn-large btn-primary btn-danger']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop