@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('projects.project')}}
@stop

{{-- Content --}}
@section('content')

</div>
<div id="banner" style="background: url({{ $project->banner->url() }}) top left no-repeat; height: 250px;">
	<div class="container">
		<div class="col-md-12">
			<img src="{{ $project->logo->url() }}" alt="{{ $project->title }}"
				 style="border: 5px solid #fff; margin-top: 100px; margin-left: -20px;"/>
		</div>
	</div>
</div>
<br clear="all"/>&nbsp;
<div>
	<!-- Container -->
	<div class="container">
		<!-- Notifications -->
		<!-- ./ notifications -->
		<!-- Content -->
		<div class="row">
			<h1 class="banner">{{ $project->title }}</h1>

			<div class="col-md-7">
				<p class="description">{{ $project->description_short }}</p>
				{{ $project->description_long }}
				<h2 style="color: #8dc63f; font-size: 18px; font-weight: bold; margin: 45px 0 10px 0;">How to Participate</h2>
				<p>This project has the following active expeditions:</p>
				<div class="table-responsive">
					<table class="table table-striped table-hover">
						<thead>
						<tr>
							<th>Expedition</th>
							<th class="nowrap">% Complete</th>
							<th>Join In</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>Apalachicola National Forest #1</td>
							<td class="nowrap">85% <span class="complete"><span class="complete85">&nbsp;</span></span>
							</td>
							<td><a href="">Notes from Nature</a></td>
						</tr>
						<tr>
							<td>Apalachicola National Forest #1</td>
							<td class="nowrap">35% <span class="complete"><span class="complete35">&nbsp;</span></span>
							</td>
							<td><a href="">GeoLocate</a></td>
						</tr>
						<tr>
							<td>Apalachicola National Forest #2</td>
							<td class="nowrap">15% <span class="complete"><span class="complete25">&nbsp;</span></span>
							</td>
							<td><a href="">Notes from Nature</a></td>
						</tr>
						<tr>
							<td>Apalachicola National Forest #3</td>
							<td class="nowrap">00% <span class="complete">&nbsp;</span></td>
							<td><a href="">Notes from Nature</a></td>
						</tr>
						<tr>
							<td colspan="3">
								<span title="3" id="1" class="collapse out"></span></td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="col-md-5">
				<dl>
					<dt class="firstdl">Managed by</dt>
					<dd class="firstdl">{{ $project->managed }}&nbsp;</dd>
					<dt>Contact</dt>
					<dd><a href="mailto:{{ $project->contact_email }}">{{ $project->contact }}</a>&nbsp;</dd>
					<dt>Website</dt>
					<dd><a href="{{ $project->website }}">{{ $project->website }}</a>&nbsp;</dd>
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
				</dl>
			</div>
		</div>
		<!-- ./ content -->
	</div>
</div>

@stop