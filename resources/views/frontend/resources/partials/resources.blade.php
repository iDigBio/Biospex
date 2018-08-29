<div class="panel panel-primary">
    <div class="panel-heading">
        <h4 class="panel-title">
            {!! $resource->title !!}
        </h4>
    </div>
    <div class="panel-body">
        {!! $resource->description !!}<br /><br />
        {{ $resource->present()->document_url }}
    </div>
</div>