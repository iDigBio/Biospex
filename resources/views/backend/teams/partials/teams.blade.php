<div class="col-md-4">
    <div class="box box-widget widget-user">
        <!-- Add the bg color to the header using any of the bg-* classes -->
        <div class="widget-user-header bg-aqua-active">
            <!-- /.widget-user-image -->
            <h3 class="widget-user-username">{{ $team->full_name }}</h3>
            <h5 class="widget-user-email">{{ Html::mailto($team->email, $team->email) }}</h5>
        </div>
        <div class="box-footer">
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-stacked">
                        <li>
                            <strong>Institution: </strong>{{ $team->institution }}
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="btn-toolbar col-md-8 col-lg-offset-4">
                    <button title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs" type="button"
                            onClick="location.href='{{ route('admin.teams.edit', [$category->id, $team->id]) }}'">
                        <span class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') --></button>

                    <button title="@lang('buttons.deleteTitle')" class="btn btn-danger delete-form btn-xs"
                            data-toggle="confirmation" data-placement="left"
                            data-href="{{ route('admin.teams.delete', [$category->id, $team->id]) }}"
                            data-method="delete">
                        <span class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') --></button>
                </div>
            </div>
        </div>
    </div>
</div>