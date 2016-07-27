<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <h3 class="box-title">Deleted Resources</h3>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <colgroup>
                        <col class="col-md-0">
                        <col class="col-md-2">
                        <col class="col-md-6">
                        <col class="col-md-2">
                        <col class="col-md-0">
                    </colgroup>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Document</th>
                        <th></th>
                    </tr>
                    @include('backend.resources.partials.trashed-loop')
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>