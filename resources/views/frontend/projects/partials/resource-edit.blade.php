<div class="entry">
    <div class="col-sm-3">
        {!! Form::label('', trans('Type'), ['class' => 'control-label']) !!}
        <div class="input-group">
            <span class="input-group-btn">
                {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
            </span>
            {!! Form::select('resources['. $key .'][type]', $resourcesSelect, $resource->type, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-sm-3">
        {!! Form::label('', trans('forms.name_url'), ['class' => 'control-label']) !!}
        {!! Form::text('resources['. $key .'][name]', $resource->name, ['class' => 'form-control', 'placeholder' => trans('pages.project_resources_name')]) !!}
    </div>
    <div class="col-sm-4">
        {!! Form::label('', trans('Description'), ['class' => 'control-label']) !!}
        {!! Form::text('resources['. $key .'][description]', $resource->description, ['class' => 'form-control', 'placeholder' => trans('pages.project_resources_description')]) !!}
    </div>
    <div class="col-sm-2">
        {!! Form::label('', trans('File'), ['class' => 'control-label']) !!}
        {!! Form::file('resources['. $key .'][download]') !!}
        @if ( ! empty($resource->download_file_name))
            <div class="col-sm-5 fileName">
                {{ $resource->download_file_name }}
            </div>
            {!! Form::hidden('resources['. $key .'][download_file_name]', $resource->download_file_name) !!}
        @endif
    </div>
    {!! Form::hidden('resources['. $key .'][id]', $resource->id) !!}
</div>