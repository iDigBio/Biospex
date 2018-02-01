<div class="entry">
    <div class="col-sm-6 top10">
        <div class="input-group">
        <span class="input-group-btn">
            {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
        </span>
            {!! Form::text('groups[' . $key . '][title]', $group->title, ['class' => 'form-control', 'placeholder' => trans('forms.event_groups_title')]) !!}
        </div>
    </div>
    {!! Form::hidden('groups['. $key .'][id]', $resource->id) !!}
</div>