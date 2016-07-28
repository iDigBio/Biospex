<div class="panel panel-info">
    <div class="panel-heading">
        <h4 class="panel-title">
            {!! $resource->title !!}
        </h4>
    </div>
    <div class="panel-body">
        {!! $resource->description !!}<br /><br />
        {{ link_to_route('web.resources.download', $resource->document, [$resource->id]) }}
    </div>
</div>