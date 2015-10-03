@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.clone') }} {{ trans('projects.project') }}
@stop

{{-- Content --}}
@section('content')
{!! Breadcrumbs::render('projects.inside', $project) !!}
<h3>{{ trans('pages.clone') }} {{ trans('projects.project') }}</h3>
<div class="well">
    {!! Form::open(array(
    'route' => array('projects.store'),
    'method' => 'post',
    'enctype' => 'multipart/form-data',
    'class' => 'form-horizontal',
    'role' => 'form'
    )) !!}

    <div class="form-group required {{ ($errors->has('group')) ? 'has-error' : '' }}" for="group">
        {!! Form::label('group', trans('forms.group'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::select('group_id', $selectGroups, $project->group_id, array('class' => 'form-control', 'placeholder' => trans('forms.title'))) !!}
            {{ ($errors->has('group_id') ? $errors->first('group_id') : '') }}
        </div>
    </div>

    <div class="form-group required {{ ($errors->has('status')) ? 'has-error' : '' }}" for="group">
        {!! Form::label('status', trans('forms.status'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::select('status', $statusSelect, $project->status, array('class' => 'form-control', 'placeholder' => trans('forms.status'))) !!}
            {{ ($errors->has('status') ? $errors->first('status') : '') }}
        </div>
    </div>

    <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
        {!! Form::label('title', trans('forms.title'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('title', $project->title, array('class' => 'form-control', 'placeholder' => trans('forms.title'))) !!}
            {{ ($errors->has('title') ? $errors->first('title') : '') }}
        </div>
    </div>

    <div class="form-group required {{ ($errors->has('contact')) ? 'has-error' : '' }}">
        {!! Form::label('contact', trans('forms.contact'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('contact', $project->contact, array('class' => 'form-control', 'placeholder' => trans('forms.contact'))) !!}
            {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
        </div>
    </div>

    <div class="form-group required {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
        {!! Form::label('contact_email', trans('forms.contact_email'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('contact_email', $project->contact_email, array('class' => 'form-control', 'placeholder' => trans('forms.contact_email'))) !!}
            {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('organization')) ? 'has-error' : '' }}">
        {!! Form::label('organization', trans('forms.organization'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('organization', $project->organization, array('class' => 'form-control', 'placeholder' => trans('forms.organization_format'))) !!}
            {{ ($errors->has('organization') ? $errors->first('organization') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('organization_website')) ? 'has-error' : '' }}">
        {!! Form::label('organization_website', trans('forms.organization_website'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('organization_website', $project->organization_website, array('class' => 'form-control', 'placeholder' => trans('forms.organization_website_format'))) !!}
            {{ ($errors->has('organization_website') ? $errors->first('organization_website') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('project_partners')) ? 'has-error' : '' }}">
        {!! Form::label('project_partners', trans('forms.project_partners'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('project_partners', $project->project_partners, array('class' => 'form-control', 'placeholder' => trans('forms.project_partners'))) !!}
            {{ ($errors->has('project_partners') ? $errors->first('project_partners') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('funding_source')) ? 'has-error' : '' }}">
        {!! Form::label('funding_source', trans('forms.funding_source'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('funding_source', $project->funding_source, array('class' => 'form-control', 'placeholder' => trans('forms.funding_source'))) !!}
            {{ ($errors->has('funding_source') ? $errors->first('funding_source') : '') }}
        </div>
    </div>

    <div class="form-group required {{ ($errors->has('description_short')) ? 'has-error' : '' }}">
        {!! Form::label('description_short', trans('forms.description_short'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('description_short', $project->description_short, array('class' => 'form-control', 'placeholder' => trans('forms.description_short_max'))) !!}
            {{ ($errors->has('description_short') ? $errors->first('description_short') : '') }}
        </div>
    </div>

    <div class="form-group required {{ ($errors->has('description_long')) ? 'has-error' : '' }}">
        {!! Form::label('description_long', trans('forms.description_long'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::textarea('description_long', $project->description_long, array('class' => 'form-control', 'placeholder' => trans('forms.description_long'))) !!}
            {{ ($errors->has('description_long') ? $errors->first('description_long') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('incentives')) ? 'has-error' : '' }}">
        {!! Form::label('incentives', trans('forms.incentives'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::textarea('incentives', $project->incentives, array('size' => '30x3', 'class' => 'form-control', 'placeholder' => trans('forms.incentives'))) !!}
            {{ ($errors->has('incentives') ? $errors->first('incentives') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('geographic_scope')) ? 'has-error' : '' }}">
        {!! Form::label('geographic_scope', trans('forms.geographic_scope'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('geographic_scope', $project->geographic_scope, array('class' => 'form-control', 'placeholder' => trans('forms.geographic_scope'))) !!}
            {{ ($errors->has('geographic_scope') ? $errors->first('geographic_scope') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('taxonomic_scope')) ? 'has-error' : '' }}">
        {!! Form::label('taxonomic_scope', trans('forms.taxonomic_scope'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('taxonomic_scope', $project->taxonomic_scope, array('class' => 'form-control', 'placeholder' => trans('forms.taxonomic_scope'))) !!}
            {{ ($errors->has('taxonomic_scope') ? $errors->first('taxonomic_scope') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('temporal_scope')) ? 'has-error' : '' }}">
        {!! Form::label('temporal_scope', trans('forms.temporal_scope'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('temporal_scope', $project->temporal_scope, array('class' => 'form-control', 'placeholder' => trans('forms.temporal_scope'))) !!}
            {{ ($errors->has('temporal_scope') ? $errors->first('temporal_scope') : '') }}
        </div>
    </div>

    <div class="form-group required {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
        {!! Form::label('keywords', trans('forms.keywords'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('keywords', $project->keywords, array('class' => 'form-control', 'placeholder' => trans('forms.keywords'))) !!}
            {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('blog_url')) ? 'has-error' : '' }}">
        {!! Form::label('blog_url', trans('forms.blog_url'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('blog_url', $project->blog_url, array('class' => 'form-control', 'placeholder' => trans('forms.blog_url_format'))) !!}
            {{ ($errors->has('blog_url') ? $errors->first('blog_url') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('facebook')) ? 'has-error' : '' }}">
        {!! Form::label('facebook', trans('forms.facebook'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('facebook', $project->facebook, array('class' => 'form-control', 'placeholder' => trans('forms.facebook_format'))) !!}
            {{ ($errors->has('facebook') ? $errors->first('facebook') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('twitter')) ? 'has-error' : '' }}">
        {!! Form::label('twitter', trans('forms.twitter'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('twitter', $project->twitter, array('class' => 'form-control', 'placeholder' => trans('forms.twitter_format'))) !!}
            {{ ($errors->has('twitter') ? $errors->first('twitter') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('activities')) ? 'has-error' : '' }}">
        {!! Form::label('activities', trans('forms.activities'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('activities', $project->activities, array('class' => 'form-control', 'placeholder' => trans('forms.activities'))) !!}
            {{ ($errors->has('activities') ? $errors->first('activities') : '') }}
        </div>
    </div>

    <div class="form-group {{ ($errors->has('language_skills')) ? 'has-error' : '' }}">
        {!! Form::label('language_skills', trans('forms.language_skills'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-10">
            {!! Form::text('language_skills', $project->language_skills, array('class' => 'form-control', 'placeholder' => trans('forms.language_skills'))) !!}
            {{ ($errors->has('language_skills') ? $errors->first('language_skills') : '') }}
        </div>
    </div>

    @for($i = 0; $i < count($actors); $i++)
        <?php
        $name = 'actor['.$i.']';
        $value = isset($project->actors[$i]) ? $project->actors[$i]->id : null;
        ?>
        <div class="form-group required {{ ($errors->has("actor.$i")) ? 'has-error' : '' }}">
            {!! Form::label($name, trans('forms.actor'), array('class' => 'col-sm-2 control-label')) !!}
            <div class="col-sm-4">
                {!! Form::select($name, ['' => '--Select--'] + $actors, $value, array('class' => 'selectpicker', $workflowCheck, 'placeholder' => trans('forms.actor'))) !!}
                @if ($errors->has("actor.$i")) <p> {{ $errors->first("actor.$i") }}</p> @endif
            </div>
        </div>
    @endfor

    <div class="form-group {{ ($errors->has('logo')) ? 'has-error' : '' }}">
        {!! Form::label('logo', trans('forms.logo'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-5">
            {!! Form::file('logo') !!} {{ trans('forms.logo_max') }}
        </div>
        <div class="col-sm-5">
            <img src="{{ $project->logo->url('thumb') }}" />
        </div>
        {{ ($errors->has('logo') ? $errors->first('logo') : '') }}
    </div>

    <div class="form-group {{ ($errors->has('banner')) ? 'has-error' : '' }}">
        {!! Form::label('banner', trans('forms.banner'), array('class' => 'col-sm-2 control-label')) !!}
        <div class="col-sm-5">
            {!! Form::file('banner') !!} {{ trans('forms.banner_min') }}
        </div>
        <div class="col-sm-5">
            <img src="{{ $project->banner->url('thumb') }}" />
        </div>
        {{ ($errors->has('banner') ? $errors->first('banner') : '') }}
    </div>


    <div class="form-group">
        <button title="@lang('buttons.addTargetTitle')" id="add_target" class="btn btn-default btn-sm" type="button">@lang('buttons.target_add')</button>
        <button title="@lang('buttons.removeTargetTitle')" id="remove_target" class="btn btn-default btn-sm" type="button">@lang('buttons.target_remove')</button>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {!! Form::hidden('id', $project->id) !!}
            {!! Form::hidden('targetCount', 0, array('id' => 'targetCount')) !!}
            {!! Form::submit(trans('buttons.create'), array('class' => 'btn btn-primary')) !!}
            {!! link_to(URL::previous(), trans('buttons.cancel'), ['class' => 'btn btn-large btn-primary btn-danger']) !!}
        </div>
    </div>
    {{ Form::close()}}
</div>
@stop