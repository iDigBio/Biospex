@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{ trans('pages.edit') }} {{ $project->title }}
@stop

{{-- Content --}}
@section('content')
{!! Breadcrumbs::render('projects.inside', $project) !!}
<div class="col-xs-12">
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">{{ trans('pages.edit') }} {{ $project->title }}</h3>
        </div>
        <div class="panel-body">
            {!! Form::open([
                'route' => ['projects.update', $project->id],
                'method' => 'put',
                'enctype' => 'multipart/form-data',
                'class' => 'form-horizontal',
                'role' => 'form'
            ]) !!}

            <div class="form-group required {{ ($errors->has('group')) ? 'has-error' : '' }}" for="group">
                {!! Form::label('group', trans('forms.group'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::select('group_id', $selectGroups, $project->group_id, ['class' => 'selectpicker']) !!}
                </div>
                {{ ($errors->has('group_id') ? $errors->first('group_id') : '') }}
            </div>

            <div class="form-group required {{ ($errors->has('status')) ? 'has-error' : '' }}" for="group">
                {!! Form::label('status', trans('forms.status'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::select('status', $statusSelect, $project->status, ['class' => 'selectpicker']) !!}
                </div>
                {{ ($errors->has('status') ? $errors->first('status') : '') }}
            </div>

            <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
                {!! Form::label('title', trans('forms.title'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('title', $project->title, ['class' => 'form-control', 'placeholder' => trans('forms.title')]) !!}
                </div>
                {{ ($errors->has('title') ? $errors->first('title') : '') }}
            </div>

            <div class="form-group required {{ ($errors->has('contact')) ? 'has-error' : '' }}">
                {!! Form::label('contact', trans('forms.contact'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('contact', $project->contact, ['class' => 'form-control', 'placeholder' => trans('forms.contact')]) !!}
                </div>
                {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
            </div>

            <div class="form-group required {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
                {!! Form::label('contact_email', trans('forms.contact_email'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('contact_email', $project->contact_email, ['class' => 'form-control', 'placeholder' => trans('forms.contact_email')]) !!}
                </div>
                {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('organization')) ? 'has-error' : '' }}">
                {!! Form::label('organization', trans('forms.organization'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('organization', $project->organization, ['class' => 'form-control', 'placeholder' => trans('forms.organization_format')]) !!}
                </div>
                {{ ($errors->has('organization') ? $errors->first('organization') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('organization_website')) ? 'has-error' : '' }}">
                {!! Form::label('organization_website', trans('forms.organization_website'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('organization_website', $project->organization_website, ['class' => 'form-control', 'placeholder' => trans('forms.organization_website_format')]) !!}
                </div>
                {{ ($errors->has('organization_website') ? $errors->first('organization_website') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('project_partners')) ? 'has-error' : '' }}">
                {!! Form::label('project_partners', trans('forms.project_partners'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('project_partners', $project->project_partners, ['class' => 'form-control', 'placeholder' => trans('forms.project_partners')]) !!}
                </div>
                {{ ($errors->has('project_partners') ? $errors->first('project_partners') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('funding_source')) ? 'has-error' : '' }}">
                {!! Form::label('funding_source', trans('forms.funding_source'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('funding_source', $project->funding_source, ['class' => 'form-control', 'placeholder' => trans('forms.funding_source')]) !!}
                </div>
                {{ ($errors->has('funding_source') ? $errors->first('funding_source') : '') }}
            </div>

            <div class="form-group required {{ ($errors->has('description_short')) ? 'has-error' : '' }}">
                {!! Form::label('description_short', trans('forms.description_short'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('description_short', $project->description_short, ['class' => 'form-control', 'placeholder' => trans('forms.description_short_max')]) !!}
                </div>
                {{ ($errors->has('description_short') ? $errors->first('description_short') : '') }}
            </div>

            <div class="form-group required {{ ($errors->has('description_long')) ? 'has-error' : '' }}">
                {!! Form::label('description_long', trans('forms.description_long'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::textarea('description_long', $project->description_long, ['class' => 'form-control', 'placeholder' => trans('forms.description_long')]) !!}
                </div>
                {{ ($errors->has('description_long') ? $errors->first('description_long') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('incentives')) ? 'has-error' : '' }}">
                {!! Form::label('incentives', trans('forms.incentives'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::textarea('incentives', $project->incentives, ['size' => '30x3', 'class' => 'form-control', 'placeholder' => trans('forms.incentives')]) !!}
                </div>
                {{ ($errors->has('incentives') ? $errors->first('incentives') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('geographic_scope')) ? 'has-error' : '' }}">
                {!! Form::label('geographic_scope', trans('forms.geographic_scope'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('geographic_scope', $project->geographic_scope, ['class' => 'form-control', 'placeholder' => trans('forms.geographic_scope')]) !!}
                </div>
                {{ ($errors->has('geographic_scope') ? $errors->first('geographic_scope') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('taxonomic_scope')) ? 'has-error' : '' }}">
                {!! Form::label('taxonomic_scope', trans('forms.taxonomic_scope'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('taxonomic_scope', $project->taxonomic_scope, ['class' => 'form-control', 'placeholder' => trans('forms.taxonomic_scope')]) !!}
                </div>
                {{ ($errors->has('taxonomic_scope') ? $errors->first('taxonomic_scope') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('temporal_scope')) ? 'has-error' : '' }}">
                {!! Form::label('temporal_scope', trans('forms.temporal_scope'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('temporal_scope', $project->temporal_scope, ['class' => 'form-control', 'placeholder' => trans('forms.temporal_scope')]) !!}
                </div>
                {{ ($errors->has('temporal_scope') ? $errors->first('temporal_scope') : '') }}
            </div>

            <div class="form-group required {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
                {!! Form::label('keywords', trans('forms.keywords'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('keywords', $project->keywords, ['class' => 'form-control', 'placeholder' => trans('forms.keywords')]) !!}
                </div>
                {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('blog_url')) ? 'has-error' : '' }}">
                {!! Form::label('blog_url', trans('forms.blog_url'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('blog_url', $project->blog_url, ['class' => 'form-control', 'placeholder' => trans('forms.blog_url_format')]) !!}
                </div>
                {{ ($errors->has('blog_url') ? $errors->first('blog_url') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('facebook')) ? 'has-error' : '' }}">
                {!! Form::label('facebook', trans('forms.facebook'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('facebook', $project->facebook, ['class' => 'form-control', 'placeholder' => trans('forms.facebook_format')]) !!}
                </div>
                {{ ($errors->has('facebook') ? $errors->first('facebook') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('twitter')) ? 'has-error' : '' }}">
                {!! Form::label('twitter', trans('forms.twitter'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('twitter', $project->twitter, ['class' => 'form-control', 'placeholder' => trans('forms.twitter_format')]) !!}
                </div>
                {{ ($errors->has('twitter') ? $errors->first('twitter') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('activities')) ? 'has-error' : '' }}">
                {!! Form::label('activities', trans('forms.activities'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('activities', $project->activities, ['class' => 'form-control', 'placeholder' => trans('forms.activities')]) !!}
                </div>
                {{ ($errors->has('activities') ? $errors->first('activities') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('language_skills')) ? 'has-error' : '' }}">
                {!! Form::label('language_skills', trans('forms.language_skills'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-10">
                    {!! Form::text('language_skills', $project->language_skills, ['class' => 'form-control', 'placeholder' => trans('forms.language_skills')]) !!}
                </div>
                {{ ($errors->has('language_skills') ? $errors->first('language_skills') : '') }}
            </div>

            @for($i = 0; $i < count($actors); $i++)
                <?php
                $name = 'actor['.$i.']';
                $value = isset($project->actors[$i]) ? $project->actors[$i]->id : null;
                ?>
                <div class="form-group required {{ ($errors->has($name)) ? 'has-error' : '' }}">
                    {!! Form::label($name, trans('forms.actor'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-4">
                        {!! Form::select($name, ['' => '--Select--'] + $actors, $value, ['class' => 'selectpicker', $workflowCheck]) !!}
                    </div>
                    {{ ($errors->has($name) ? $errors->first($name) : '') }}
                </div>
            @endfor

            <div class="form-group {{ ($errors->has('logo')) ? 'has-error' : '' }}">
                {!! Form::label('logo', trans('forms.logo'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-5">
                    {!! Form::file('logo') !!} {{ trans('forms.logo_max') }}
                </div>
                <div class="col-sm-5">
                    <img src="{{ $project->logo->url('thumb') }}" />
                </div>
                {{ ($errors->has('logo') ? $errors->first('logo') : '') }}
            </div>

            <div class="form-group {{ ($errors->has('banner')) ? 'has-error' : '' }}">
                {!! Form::label('banner', trans('forms.banner'), ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-5">
                    {!! Form::file('banner') !!} {{ trans('forms.banner_min') }}
                </div>
                <div class="col-sm-5">
                    <img src="{{ $project->banner->url('thumb') }}" />
                </div>
                {{ ($errors->has('banner') ? $errors->first('banner') : '') }}
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    {!! Form::hidden('id', $project->id) !!}
                    {!! Form::hidden('targetCount', $count, ['id' => 'targetCount']) !!}
                    {!! Form::submit(trans('buttons.update'), ['class' => 'btn btn-primary']) !!}
                    {!! Form::button(trans('buttons.cancel'), ['class' => 'btn btn-large btn-primary btn-danger', 'onClick' => "location.href='$cancel'"]) !!}
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@stop