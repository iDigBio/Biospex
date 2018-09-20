<h3 class="ml-3 pt-4">{{ $category->name }}</h3>
<div class="card-deck">
@foreach($category->teams as $team)
    @include('front.team.partials.teams')
@endforeach
</div>