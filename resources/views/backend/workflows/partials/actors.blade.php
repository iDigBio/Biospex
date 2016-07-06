@foreach($actors as $actor)
    <li id="{{ $actor->id }}">
        <span class="handle"><i class="fa fa-ellipsis-v"></i><i class="fa fa-ellipsis-v"></i></span>
        <span class="text">{{ $actor->title }}</span>
    </li>
@endforeach