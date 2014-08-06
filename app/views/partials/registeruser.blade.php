<div class="form-group {{ ($errors->has('invite')) ? 'has-error' : '' }}">
    {{ Form::text('invite', null, array('class' => 'form-control', 'placeholder' => trans('groups.invite_code'))) }}
    {{ ($errors->has('invite') ?  $errors->first('invite') : '') }}
</div>
<div class="form-group">
    {{ Form::hidden('groups[]', $group->id, array('id' => 'groups')) }}
</div>