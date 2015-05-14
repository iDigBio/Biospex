<div class="form-group">
    {{ Form::label('edit_permissions', trans('pages.permissions'), array('class' => 'col-sm-2 control-label'))}}
    <div class="col-sm-10">
        @foreach ($permissions as $key => $permission)
        <h5>@lang('pages.' . $key)</h5>
        @foreach ($permission as $perm)
        <?php
        if ( ! array_key_exists($perm['name'], $userPermissions)) $userPermissions[$perm['name']] = 0;
        ?>
        <div class="radio-inline">
            <label class="radio-inline">
                {{ trans($perm['description']) }}
            </label>
            <label class="radio-inline">
                {{ Form::radio($perm['name'], -1, $userPermissions[$perm['name']] == -1 ? true : '') }} Deny
            </label>
            <label class="radio-inline">
                {{ Form::radio($perm['name'], 0, $userPermissions[$perm['name']] == 0 ? true : '') }} Inherit
            </label>
            <label class="radio-inline">
                {{ Form::radio($perm['name'], 1, $userPermissions[$perm['name']] == 1 ? true : '') }} Allow
            </label>
        </div>
        <div class="clearfix"></div>
        @endforeach
        @endforeach
    </div>
</div>