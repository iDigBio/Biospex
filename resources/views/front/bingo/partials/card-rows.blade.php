@foreach($rows as $row)
    <div class="row">
        @foreach($row as $key => $word)
            @if ($key === 'c3')
                <div class="square logo d-inline float-left d-flex text-center justify-content-center align-items-center">
                    <img src="{{ $project->present()->show_logo }}" class="img-fluid"
                         alt="{{ $project->title }} logo"></div>
                @continue
            @endif
        <!-- justify-content-center align-items-center -->
            <div class="square d-inline float-left d-flex text-center justify-content-center align-items-center"
                 id="{{ $key }}">{{ $word }}</div>
        @endforeach
    </div>
@endforeach
