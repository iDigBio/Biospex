<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <h3 class="box-title">Deleted Workflows</h3>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <colgroup>
                        <col class="col-md-10">
                        <col class="col-md-1">
                        <col class="col-md-1">
                    </colgroup>
                    <tr>
                        <th>Workflow</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    @include('backend.workflows.partials.trashed-loop')
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>