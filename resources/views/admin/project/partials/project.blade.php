@if($projects->isNotEmpty())
    @each('admin.project.partials.project-loop', $projects, 'project')
@else
    <h2 class="mx-auto pt-4">{{ __('pages.projects_none') }}</h2>
@endif
