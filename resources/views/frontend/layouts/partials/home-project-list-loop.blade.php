<li>
    <div class="col-md-1">
        <img src="{{ $project->logo_avatar_url }}" alt="{{ $project->title }}"/>
    </div>
    <div class="col-md-11 project-text">
        {!! link_to_route('home.get.project', $project->title, [$project->slug]) !!}
        <p>{!! $project->description_short !!}</p>
    </div>
</li>