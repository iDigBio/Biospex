<div class="col-md-8">
<h2 class="project-page-header">{{ trans('pages.project_map_title') }}</h2>
<iframe width="700" height="500" scrolling="no" frameborder="no" src="https://fusiontables.google.com/embedviz?q=select+col2+from+{{ $project->fusion_table_id }}&amp;viz=MAP&amp;h=false&amp;lat=34.72404554786575&amp;lng=-93.08009375000002&amp;t=1&amp;z=3&amp;l=col2&amp;y=2&amp;tmplt=3&amp;hml=GEOCODABLE&key={{ Config::get('google.map_api_key') }}"></iframe>
</div>