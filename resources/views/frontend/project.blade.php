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
                        @if(null !== $expeditions)
                            @foreach ($expeditions as $expedition)
                                <tr>
                                    <td>{{ $expedition->title }}</td>
                                    @if( ! $expedition->actors->isEmpty())
                                        <td class="nowrap">
                                        <span class="complete">
                                        <span class="complete{{ round_up_to_any_five($expedition->stat->percent_completed) }}">&nbsp;</span>
                                        </span> {{ $expedition->stat->percent_completed }}%
                                        </td>
                                    @else
                                        <td class="nowrap"
                                            colspan="3">{{ trans('expeditions.processing_not_started') }}</td>
                                    @endif
                                    <td>
                                        @foreach($expedition->actors as $actor)
                                            <a href="{{ $actor->url }}">{{ $actor->title }}</a>&nbsp;&nbsp;
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-5">
                <dl>
                    <dt class="firstdl">{{ trans('forms.organization') }}</dt>
                    <dd class="firstdl">{{ $project->organization }}&nbsp;</dd>
                    <dt>{{ trans('forms.contact') }}</dt>
                    <dd><a href="mailto:{{ $project->contact_email }}">{{ $project->contact }}</a>&nbsp;</dd>
                    <dt>{{ trans('forms.contact_title') }}</dt>
                    <dd>{{ $project->contact_title }}&nbsp;</dd>
                    <dt>{{ trans('forms.organization_website') }}</dt>
                    <dd><a href="{{ $project->organization_website }}">{{ $project->organization_website }}</a>&nbsp;
                    </dd>
                    <dt>{{ trans('forms.project_partners') }}</dt>
                    <dd>{{ $project->project_partners }}&nbsp;</dd>
                    <dt>{{ trans('forms.funding_source') }}</dt>
                    <dd>{{ $project->funding_source }}&nbsp;</dd>
                    <dt>{{ trans('forms.incentives') }}</dt>
                    <dd>{{ $project->incentives }}&nbsp;</dd>
                    <dt>{{ trans('forms.geographic_scope') }}</dt>
                    <dd>{{ $project->geographic_scope }}&nbsp;</dd>
                    <dt>{{ trans('forms.taxonomic_scope') }}</dt>
                    <dd>{{ $project->taxonomic_scope }}&nbsp;</dd>
                    <dt>{{ trans('forms.temporal_scope') }}</dt>
                    <dd>{{ $project->temporal_scope }}&nbsp;</dd>
                    <dt>{{ trans('forms.language_skills') }}</dt>
                    <dd>{{ $project->language_skills }}&nbsp;</dd>
                    <dt>{{ trans('forms.activities') }}</dt>
                    <dd>{{ $project->activities }}&nbsp;</dd>
                    <dt>{{ trans('forms.keywords') }}</dt>
                    <dd>{{ $project->keywords }}&nbsp;</dd>
                    <dt>{{ trans('forms.blog_url') }}</dt>
                    <dd><a href="{{ $project->blog_url }}">{{ $project->blog_url }}</a>&nbsp;</dd>
                    <dt>{{ trans('forms.facebook') }}</dt>
                    <dd><a href="{{ $project->facebook }}">{{ $project->facebook }}</a>&nbsp;</dd>
                    <dt>{{ trans('forms.twitter') }}</dt>
                    <dd><a href="{{ $project->twitter }}" target="_blank">{{ $project->twitter }}</a>&nbsp;
                    </dd>
                </dl>
            </div>
        </div>
        @if ($project->amChart !== null)
            <div class="row">
                <input type="hidden" id="projectId" value="{{ $project->id }}"/>
                <div id="chartdiv" class="col-md-12" style="width: 100%; height: 600px"></div>
            </div>
        @endif
        <div class="row">
            @if ($project->fusion_table_id !== null)
                @include('frontend.layouts.partials.projectmap')
            @endif
            <div class="col-md-4 organizers">
                <h2 class="project-page-header">{{ trans('pages.project_page_organizers') }}</h2>
                <dl>
                    @foreach($project->group->users as $user)
                        <dt>
                            <img src="{{ $user->profile->avatar->url('small') }}"/>
                        </dt>
                        <dd>{!! HTML::mailto($user->email, $user->profile->full_name) !!}</dd>
                    @endforeach
                </dl>
            </div>
        </div>
        <!-- ./ content -->
    </div>
@stop