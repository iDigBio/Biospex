@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }}
@stop

{{-- Content --}}
@section('project')
    <div id="banner" style="background: url({{ $project->banner->url() }});">
        <img src="{{ $project->logo->url() }}" alt="{{ $project->title }}"/>
    </div>
    <div class="container">
        <!-- Notifications -->
        <!-- ./ notifications -->
        <!-- Content -->
        <h1 class="banner">{{ $project->title }}</h1>

        <div class="row">
            <div class="col-md-7">
                <p class="description">{{ $project->description_short }}</p>
                {!! $project->description_long !!}
                <h2 class="project-page-header">{{ trans('pages.project_page_header') }}</h2>
                <p>{{ trans('pages.project_page_expeditions') }}:</p>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>{{ trans('pages.expeditions') }}</th>
                            <th class="nowrap">{{ trans('pages.project_page_percent_complete') }}</th>
                            <th>{{ trans('pages.project_page_join') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @each('frontend.layouts.partials.project-page-expeditions', $project->expeditions, 'expedition')
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-5">
                <dl>
                    @isset($project->organization)
                        <dt class="firstdl">{{ trans('forms.organization') }}</dt>
                        <dd class="firstdl">{{ $project->organization }}&nbsp;</dd>
                    @endisset
                    @isset($project->contact_email)
                        <dt>{{ trans('forms.contact') }}</dt>
                        <dd><a href="mailto:{{ $project->contact_email }}">{{ $project->contact }}</a>&nbsp;</dd>
                    @endisset
                    @isset($project->contact_title)
                        <dt>{{ trans('forms.contact_title') }}</dt>
                        <dd>{{ $project->contact_title }}&nbsp;</dd>
                    @endisset
                    @isset($project->organization_website)
                        <dt>{{ trans('forms.organization_website') }}</dt>
                        <dd><a href="{{ $project->organization_website }}">{{ $project->organization_website }}</a></dd>
                    @endisset
                    @isset($project->project_partners)
                        <dt>{{ trans('forms.project_partners') }}</dt>
                        <dd>{{ $project->project_partners }}&nbsp;</dd>
                    @endisset
                    @isset($project->funding_source)
                        <dt>{{ trans('forms.funding_source') }}</dt>
                        <dd>{{ $project->funding_source }}&nbsp;</dd>
                    @endisset
                    @isset($project->incentives)
                        <dt>{{ trans('forms.incentives') }}</dt>
                        <dd>{{ $project->incentives }}&nbsp;</dd>
                    @endisset
                    @isset($project->geographic_scope)
                        <dt>{{ trans('forms.geographic_scope') }}</dt>
                        <dd>{{ $project->geographic_scope }}&nbsp;</dd>
                    @endisset
                    @isset($project->taxonomic_scope)
                        <dt>{{ trans('forms.taxonomic_scope') }}</dt>
                        <dd>{{ $project->taxonomic_scope }}&nbsp;</dd>
                    @endisset
                    @isset($project->temporal_scope)
                        <dt>{{ trans('forms.temporal_scope') }}</dt>
                        <dd>{{ $project->temporal_scope }}&nbsp;</dd>
                    @endisset
                    @isset($project->language_skills)
                        <dt>{{ trans('forms.language_skills') }}</dt>
                        <dd>{{ $project->language_skills }}&nbsp;</dd>
                    @endisset
                    @isset($project->activities)
                        <dt>{{ trans('forms.activities') }}</dt>
                        <dd>{{ $project->activities }}&nbsp;</dd>
                    @endisset
                    @isset($project->keywords)
                        <dt>{{ trans('forms.keywords') }}</dt>
                        <dd>{{ $project->keywords }}&nbsp;</dd>
                    @endisset
                    @isset($project->blog_url)
                        <dt>{{ trans('forms.blog_url') }}</dt>
                        <dd><a href="{{ $project->blog_url }}">{{ $project->blog_url }}</a></dd>
                    @endisset
                    @isset($project->facebook)
                        <dt>{{ trans('forms.facebook') }}</dt>
                        <dd><a href="{{ $project->facebook }}">{{ $project->facebook }}</a></dd>
                    @endisset
                    @isset($project->twitter)
                        <dt>{{ trans('forms.twitter') }}</dt>
                        <dd><a href="{{ $project->twitter }}" target="_blank">{{ $project->twitter }}</a></dd>
                    @endisset
                    @if($project->resources->isNotEmpty())
                        <dt>{{ trans('forms.project_resources') }}</dt>
                        <dd>
                            @foreach($project->resources as $resource)
                                @if($resource->type === 'File Download')
                                    <a href="{{ $resource->download->url() }}" target="_blank" data-toggle="tooltip"
                                       title="{{ $resource->description }}">{{ $resource->name }}</a><br />
                                @else
                                    <a href="{{ $resource->name }}" target="_blank" data-toggle="tooltip"
                                       title="{{ $resource->description }}">{{ $resource->name }}</a><br />
                                @endif
                            @endforeach
                        </dd>
                    @endif
                </dl>
            </div>
        </div>
        @if ($project->amChart !== null)
            <div class="row">
                <input type="hidden" id="projectId" value="{{ $project->id }}"/>
                <div id="chartdiv" class="amchart col-md-12" style="width: 100%; height: 600px"></div>
            </div>
        @endif
        <div class="row">
        @if ($project->fusion_table_id !== null)
            @include('frontend.layouts.partials.projectmap')
        @endif
        </div>
        <!-- ./ content -->
    </div>
@stop