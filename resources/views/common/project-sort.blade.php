<div class="col-md-6 mx-auto mb-4 text-center">
    <span data-sort="title" data-order="asc" data-url="{{ $route }}"
          data-target="projects"
          class="sort-page mr-2 text-uppercase" style="color: #e83f29; cursor: pointer;">
        <i class="fas fa-sort"></i> {{ __('pages.title') }}</span>
    <span data-sort="group" data-order="asc" data-url="{{ $route }}"
          data-target="projects"
          class="sort-page ml-2 text-uppercase" style="color: #e83f29; cursor: pointer;">
        <i class="fas fa-sort"></i> {{ __('pages.group') }}</span>
    <span data-sort="date" data-order="asc" data-url="{{ $route }}"
          data-target="projects"
          class="sort-page ml-2 text-uppercase" style="color: #e83f29; cursor: pointer;">
        <i class="fas fa-sort"></i> {{ __('pages.date') }}</span>
</div>