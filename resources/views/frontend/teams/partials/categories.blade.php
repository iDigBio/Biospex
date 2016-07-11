<h3 class="page-header">{{ $category->name }}</h3>
@foreach($category->teams as $team)
    @include('frontend.teams.partials.teams')
@endforeach
<div class="clearfix"></div>