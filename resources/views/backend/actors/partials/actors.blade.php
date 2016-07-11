<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <h3 class="box-title">Current Actors</h3>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Url</th>
                        <th>Class</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    @include('backend.actors.partials.actors-loop')
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>