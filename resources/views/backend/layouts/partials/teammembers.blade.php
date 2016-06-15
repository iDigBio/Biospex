<div class="col-md-4">
    <div class="box box-widget widget-user">
        <!-- Add the bg color to the header using any of the bg-* classes -->
        <div class="widget-user-header bg-aqua-active">
            <!-- /.widget-user-image -->
            <h3 class="widget-user-username">{{ $member->full_name }}</h3>
            <h5 class="widget-user-email">{{ Html::mailto($member->email, $member->email) }}</h5>
        </div>
        <div class="box-footer">
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-stacked">
                        <li>
                            <strong>Institution: </strong>{{ $member->institution }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="btn-toolbar col-md-8 col-lg-offset-4">
                    <a href="{{ route('admin.teams.edit', ['category' => $category->id, 'team' => $member->id]) }}"
                       title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs"
                       role="button"><span
                                class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') -->
                    </a>
                    <a href="{{ route('admin.teams.delete', ['category' => $category->id, 'team' => $member->id]) }}"
                       title="@lang('buttons.deleteTitle')"
                       class="btn btn-danger action_confirm btn-xs" role="button"
                       data-token="{{ Session::getToken() }}" data-method="delete"><span
                                class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') -->
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>