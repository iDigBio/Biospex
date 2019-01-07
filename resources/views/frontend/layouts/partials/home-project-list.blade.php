<ul class="recent-projects">
    @each('front.layouts.partials.home-project-list-loop', $recentProjects, 'project')
</ul>
<div style="display: none" id="count" data-count="{!! $recentProjects->count() !!}"></div>