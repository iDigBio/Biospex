<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <h3 class="box-title">Current Page Text</h3>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tr>
                        <th>ID</th>
                        <th>KEY</th>
                        <th>HTML</th>
                        <th></th>
                    </tr>
                    @include('backend.pages.partials.pages-loop')
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>