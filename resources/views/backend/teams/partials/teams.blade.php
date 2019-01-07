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
                    <button title="@lang('pages.editTitle')" class="btn btn-warning btn-xs" type="button"
                            onClick="location.href='{{ route('admin.teams.edit', [$category->id, $team->id]) }}'">
                        <span class="fa fa-wrench fa-sm"></span> <!-- @lang('pages.edit') --></button>

                    <button class="btn btn-xs btn-danger" title="@lang('pages.deleteTitle')"
                            data-href="{{ route('admin.teams.delete', [$category->id, $team->id]) }}"
                            data-method="delete"
                            data-toggle="confirmation"
                            data-btn-ok-label="Continue" data-btn-ok-icon="fa fa-share fa-lrg"
                            data-btn-ok-class="btn-success"
                            data-btn-cancel-label="Stop" data-btn-cancel-icon="fa fa-ban fa-lrg"
                            data-btn-cancel-class="btn-danger"
                            data-title="Continue action?" data-content="This will delete the item">
                        <span class="fa fa-remove fa-sm"></span> <!-- @lang('pages.delete') -->
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>