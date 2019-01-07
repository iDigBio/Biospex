@extends('front.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{ trans('pages.clone') }} {{ trans('pages.project') }}
@stop

{{-- Content --}}
@section('content')
    <div class="col-xs-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">{{ trans('pages.clone') }}</h3>
            </div>
            <div class="panel-body">
                {!! Form::open([
                    'route' => ['admin.projects.store'],
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                    'class' => 'form-horizontal',
                    'role' => 'form'
                ]) !!}

                <div class="form-group required {{ ($errors->has('group_id')) ? 'has-error' : '' }}" for="group">
                    {!! Form::label('group_id', trans('pages.group'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-3">
                        {{ ($errors->has('group_id') ? $errors->first('group_id') : '') }}
                        {!! Form::select('group_id', $selectGroups, $project->group_id, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('status')) ? 'has-error' : '' }}" for="group">
                    {!! Form::label('status', trans('pages.status'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-3">
                        {{ ($errors->has('status') ? $errors->first('status') : '') }}
                        {!! Form::select('status', $statusSelect, $project->status, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
                    {!! Form::label('title', trans('pages.title'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('title') ? $errors->first('title') : '') }}
                        {!! Form::text('title', $project->title, array('class' => 'form-control', 'placeholder' => trans('pages.title'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact')) ? 'has-error' : '' }}">
                    {!! Form::label('contact', trans('pages.contact'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact') ? $errors->first('contact') : '') }}
                        {!! Form::text('contact', $project->contact, array('class' => 'form-control', 'placeholder' => trans('pages.contact'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact_email')) ? 'has-error' : '' }}">
                    {!! Form::label('contact_email', trans('pages.contact').' '.trans('pages.email'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact_email') ? $errors->first('contact_email') : '') }}
                        {!! Form::text('contact_email', $project->contact_email, array('class' => 'form-control', 'placeholder' => trans('pages.contact').' '.trans('pages.email'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('contact_title')) ? 'has-error' : '' }}">
                    {!! Form::label('contact_title', trans('pages.contact').' '.trans('pages.title'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('contact_title') ? $errors->first('contact_title') : '') }}
                        {!! Form::text('contact_title', $project->contact_title, array('class' => 'form-control', 'placeholder' => trans('pages.contact').' '.trans('pages.title'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('organization')) ? 'has-error' : '' }}">
                    {!! Form::label('organization', trans('pages.organization'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('organization') ? $errors->first('organization') : '') }}
                        {!! Form::text('organization', $project->organization, array('class' => 'form-control', 'placeholder' => trans('pages.organization_format'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('organization_website')) ? 'has-error' : '' }}">
                    {!! Form::label('organization_website', trans('pages.organization_website'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('organization_website') ? $errors->first('organization_website') : '') }}
                        {!! Form::text('organization_website', $project->organization_website, array('class' => 'form-control', 'placeholder' => trans('pages.organization_website_format'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('project_partners')) ? 'has-error' : '' }}">
                    {!! Form::label('project_partners', trans('pages.project_partners'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('project_partners') ? $errors->first('project_partners') : '') }}
                        {!! Form::textarea('project_partners', $project->project_partners, array('class' => 'form-control', 'placeholder' => trans('pages.project_partners'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('funding_source')) ? 'has-error' : '' }}">
                    {!! Form::label('funding_source', trans('pages.funding_source'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('funding_source') ? $errors->first('funding_source') : '') }}
                        {!! Form::textarea('funding_source', $project->funding_source, array('class' => 'form-control', 'placeholder' => trans('pages.funding_source'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('description_short')) ? 'has-error' : '' }}">
                    {!! Form::label('description_short', trans('pages.description_short'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('description_short') ? $errors->first('description_short') : '') }}
                        {!! Form::text('description_short', $project->description_short, array('class' => 'form-control', 'placeholder' => trans('pages.description_short_max'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('description_long')) ? 'has-error' : '' }}">
                    {!! Form::label('description_long', trans('pages.description_long'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('description_long') ? $errors->first('description_long') : '') }}
                        {!! Form::textarea('description_long', $project->description_long, array('class' => 'form-control textarea', 'placeholder' => trans('pages.description_long'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('incentives')) ? 'has-error' : '' }}">
                    {!! Form::label('incentives', trans('pages.incentives'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('incentives') ? $errors->first('incentives') : '') }}
                        {!! Form::textarea('incentives', $project->incentives, array('size' => '30x3', 'class' => 'form-control', 'placeholder' => trans('pages.incentives'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('geographic_scope')) ? 'has-error' : '' }}">
                    {!! Form::label('geographic_scope', trans('pages.geographic_scope'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('geographic_scope') ? $errors->first('geographic_scope') : '') }}
                        {!! Form::text('geographic_scope', $project->geographic_scope, array('class' => 'form-control', 'placeholder' => trans('pages.geographic_scope'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('taxonomic_scope')) ? 'has-error' : '' }}">
                    {!! Form::label('taxonomic_scope', trans('pages.taxonomic_scope'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('taxonomic_scope') ? $errors->first('taxonomic_scope') : '') }}
                        {!! Form::text('taxonomic_scope', $project->taxonomic_scope, array('class' => 'form-control', 'placeholder' => trans('pages.taxonomic_scope'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('temporal_scope')) ? 'has-error' : '' }}">
                    {!! Form::label('temporal_scope', trans('pages.temporal_scope'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('temporal_scope') ? $errors->first('temporal_scope') : '') }}
                        {!! Form::text('temporal_scope', $project->temporal_scope, array('class' => 'form-control', 'placeholder' => trans('pages.temporal_scope'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('keywords')) ? 'has-error' : '' }}">
                    {!! Form::label('keywords', trans('pages.keywords'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('keywords') ? $errors->first('keywords') : '') }}
                        {!! Form::text('keywords', $project->keywords, array('class' => 'form-control', 'placeholder' => trans('pages.keywords'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('blog_url')) ? 'has-error' : '' }}">
                    {!! Form::label('blog_url', trans('pages.blog_url'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('blog_url') ? $errors->first('blog_url') : '') }}
                        {!! Form::text('blog_url', $project->blog_url, array('class' => 'form-control', 'placeholder' => trans('pages.blog_url_format'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('facebook')) ? 'has-error' : '' }}">
                    {!! Form::label('facebook', trans('pages.facebook'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('facebook') ? $errors->first('facebook') : '') }}
                        {!! Form::text('facebook', $project->facebook, array('class' => 'form-control', 'placeholder' => trans('pages.facebook_format'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('twitter')) ? 'has-error' : '' }}">
                    {!! Form::label('twitter', trans('pages.twitter'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('twitter') ? $errors->first('twitter') : '') }}
                        {!! Form::text('twitter', $project->twitter, array('class' => 'form-control', 'placeholder' => trans('pages.twitter_format'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('activities')) ? 'has-error' : '' }}">
                    {!! Form::label('activities', trans('pages.activities'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('activities') ? $errors->first('activities') : '') }}
                        {!! Form::text('activities', $project->activities, array('class' => 'form-control', 'placeholder' => trans('pages.activities'))) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('language_skills')) ? 'has-error' : '' }}">
                    {!! Form::label('language_skills', trans('pages.language_skills'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-10">
                        {{ ($errors->has('language_skills') ? $errors->first('language_skills') : '') }}
                        {!! Form::text('language_skills', $project->language_skills, array('class' => 'form-control', 'placeholder' => trans('pages.language_skills'))) !!}
                    </div>
                </div>

                <div class="form-group required {{ ($errors->has('workflow_id')) ? 'has-error' : '' }}">
                    {!! Form::label('workflow_id', trans('pages.workflows'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-4">
                        {{ ($errors->has('workflow_id') ? $errors->first('workflow_id') : '') }}
                        {!! Form::select('workflow_id', $workflows, $project->workflow_id, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('logo')) ? 'has-error' : '' }}">
                    {!! Form::label('logo', trans('pages.logo'), array('class' => 'col-sm-2 control-label')) !!}
                    <div class="col-sm-5">
                        {{ ($errors->has('logo') ? $errors->first('logo') : '') }}
                        {!! Form::file('logo') !!} {{ trans('pages.logo_max') }}
                    </div>
                    <div class="col-sm-5">
                        <img src="{{ $project->present()->logo_avatar_url }}"/>
                    </div>
                </div>

                <div class="form-group {{ ($errors->has('banner')) ? 'has-error' : '' }}">
                    {!! Form::label('', trans('pages.banner'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-5">
                        {{ ($errors->has('banner') ? $errors->first('banner') : '') }}
                        {!! Form::file('banner') !!} {{ trans('pages.banner_min') }}
                    </div>
                    <div class="col-sm-5">
                        <img src="{{ $project->present()->banner_thumb_url }}"/>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('', trans('pages.project_resources'), ['class' => 'col-sm-2 control-label']) !!}
                    <div class="controls col-sm-10">
                        @if($errors->has('resources.*'))
                            @for($i = 0; $i <= old('entries'); $i++)
                                @include('front.projects.partials.resource-error')
                            @endfor
                        @else
                            @include('front.projects.partials.resource-create')
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        {!! Form::hidden('entries', 1) !!}
                        {!! Form::submit(trans('pages.create'), ['class' => 'btn btn-primary']) !!}
                        {!! link_to(URL::previous(), trans('pages.cancel'), ['class' => 'btn btn-large btn-primary btn-danger']) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop