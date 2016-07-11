<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <h3 class="box-title">Current Messages</h3>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <colgroup>
                        <col class="col-md-10">
                        <col class="col-md-1">
                        <col class="col-md-1">
                    </colgroup>
                    <tr>
                        <th>Message</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    @include('backend.notices.partials.notices-loop')
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>