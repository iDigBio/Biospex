<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <h3 class="box-title">Deleted Projects</h3>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover" id="resources">
                    <colgroup>
                        <col class="">
                        <col class="">
                    </colgroup>
                    <thead>
                    <th>ID</th>
                    <th>Title</th>
                    <th></th>
                    </thead>
                    @include('backend.projects.partials.trashed-loop')
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>