<div class="form-group">
    {{ Form::label('edit_memberships', trans('groups.group_memberships'), array('class' => 'col-sm-2 control-label'))}}
    <div class="col-sm-10">
        @foreach ($allGroups as $group)
            <label class="checkbox-inline">
                <input type="checkbox" name="groups[{{ $group->id }}]" value='1'
                        {{ (in_array($group->name, $userGroups) ? 'checked="checked"' : '') }} > {{ $group->name }}
            </label>
        @endforeach
    </div>
</div>