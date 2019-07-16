<div class="mx-auto mb-2">
    <div class="card px-4 box-shadow h-100" style="max-width: 25rem;">
        <h2 class="text-center pt-4">{{ $resource->title }}</h2>
        <hr>
        <div class="row card-body">
            {!! $resource->description !!}
        </div>
        <div class="card-footer py-4">
            {!! $resource->present()->document_url !!}
        </div>
    </div>
</div>