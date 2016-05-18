@extends('frontend.layouts.default')

{{-- Web site Title --}}
@section('title')
    @parent
    {{ $project->title }}
@stop

{{-- Content --}}
@section('project')
    <div id="banner" style="background: url({{ $project->banner->url() }});">
            <img src="{{ $project->logo->url() }}" alt="{{ $project->title }}" />
    </div>
    <div class="container">
    <!-- Notifications -->
    <!-- ./ notifications -->
    <!-- Content -->
    <h1 class="banner">{{ $project->title }}</h1>

    <div class="col-md-7">
        <p class="description">{{ $project->description_short }}</p>
        {{ $project->description_long }}
        <h2 style="color: #8dc63f; font-size: 18px; font-weight: bold; margin: 45px 0 10px 0;">How to
            Participate</h2>
        <p>This project has the following active expeditions:</p>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Expedition</th>
                    <th class="nowrap">% Complete <span class="red">*</span></th>
                    <th>Join In</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($project->expeditions as $expedition)
                    <tr>
                        <td>{{ $expedition->title }}</td>
                        @if( ! $expedition->actors->isEmpty())
                            <td class="nowrap">
                                        <span class="complete">
                                        <span class="complete{{ round_up_to_any_five($expedition->stat->percent_completed) }}">&nbsp;</span>
                                        </span> {{ $expedition->stat->percent_completed }}%
                            </td>
                        @else
                            <td class="nowrap" colspan="3">{{ trans('expeditions.processing_not_started') }}</td>
                        @endif
                        <td>
                            @foreach($expedition->actors as $actor)
                                <a href="{{ $actor->url }}">{{ $actor->title }}</a>&nbsp;&nbsp;
                            @endforeach
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <span class="red">*</span> <span class="small-font">Functionality currently under construction.</span>
        </div>
    </div>
    <div class="col-md-5">
        <dl>
            <dt class="firstdl">Organization</dt>
            <dd class="firstdl">{{ $project->organization }}&nbsp;</dd>
            <dt>Contact</dt>
            <dd><a href="mailto:{{ $project->contact_email }}">{{ $project->contact }}</a>&nbsp;</dd>
            <dt>Contact Title</dt>
            <dd>{{ $project->contact_title }}&nbsp;</dd>
            <dt>Organization Website</dt>
            <dd><a href="{{ $project->organization_website }}">{{ $project->organization_website }}</a>&nbsp;</dd>
            <dt>Project Partners</dt>
            <dd>{{ $project->project_partners }}&nbsp;</dd>
            <dt>Funding Source</dt>
            <dd>{{ $project->funding_source }}&nbsp;</dd>
            <dt>Incentives</dt>
            <dd>{{ $project->incentives }}&nbsp;</dd>
            <dt>Geographic Scope</dt>
            <dd>{{ $project->geographic_scope }}&nbsp;</dd>
            <dt>Taxonomic Scope</dt>
            <dd>{{ $project->taxonomic_scope }}&nbsp;</dd>
            <dt>Temporal Scope</dt>
            <dd>{{ $project->temporal_scope }}&nbsp;</dd>
            <dt>Language Skills Required</dt>
            <dd>{{ $project->language_skills }}&nbsp;</dd>
            <dt>Activities</dt>
            <dd>{{ $project->activities }}&nbsp;</dd>
            <dt>Keywords</dt>
            <dd>{{ $project->keywords }}&nbsp;</dd>
            <dt>Facebook</dt>
            <dd><a href="{{ $project->facebook }}">{{ $project->facebook }}</a>&nbsp;</dd>
            <dt>Twitter</dt>
            <dd><a href="http://twitter.com/{{ $project->twitter }}" target="_blank">{{ $project->twitter }}</a>&nbsp;</dd>
        </dl>
    </div>
    <!-- ./ content -->
    </div>
@stop