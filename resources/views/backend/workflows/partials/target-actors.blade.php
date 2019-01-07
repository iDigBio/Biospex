@foreach($workflow->actors as $actor)
    <li id="{{ $actor->id }}">
        <span class="text">{{ $actor->title }}</span>
        <a href='#' class='dismiss'>x</a>
    </li>
@endforeach