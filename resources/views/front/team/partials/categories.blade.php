<div class="col-sm-12 my-4"><h1 class="ml-3 content-header text-center">{{ $category->name }}</h1></div>
@foreach($category->teams as $team)
    @include('front.team.partials.teams')
@endforeach

