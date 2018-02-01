<div class="entry">
    <div class="col-sm-6 top10">
        <div class="input-group">
        <span class="input-group-btn">
            {!! Form::button('<i class="fa fa-plus fa-lrg"></i> ', ['type' => 'button', 'class' => 'btn btn-success btn-add']) !!}
        </span>
        {!! Form::text('groups[0][title]', null, ['class' => 'form-control', 'placeholder' => trans('forms.event_groups_title')]) !!}
        </div>
    </div>
</div>