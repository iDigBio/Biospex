<div class="col-md-8 col-lg-offset-2">
<h2 class="project-page-header">{{ trans('pages.project_map_title') }}</h2>
    <iframe width="700" height="500" scrolling="no" frameborder="no" src="https://fusiontables.google.com/embedviz?q=select+col2+from+{{ $project->fusion_table_id }}&amp;viz=MAP&amp;h=false&amp;lat=34.00&amp;lng=-93.00&amp;t=1&amp;z=3&amp;l=col2&amp;y={{ $project->fusion_style_id }}&amp;tmplt={{ $project->fusion_template_id }}&amp;hml=KML"></iframe>
</div>