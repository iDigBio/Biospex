<div class="form-group {{ ($errors->has('groups')) ? 'has-error' : '' }}">
    {{ Form::select('groups[]', $groups, Input::old('groups'), array('multiple' => true)) }}
    {{ ($errors->has('groups') ?  $errors->first('groups') : '') }}
</div>