<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <h3 class="box-title">Current Users</h3>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover" id="resources">
                    <colgroup>
                        <col class="">
                        <col class="">
                        <col class="">
                        <col class="">
                        <col class="">
                        <col class="">
                        <col class="">
                    </colgroup>
                    <thead>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Activated</th>
                    <th>Timezone</th>
                    <th>Created</th>
                    <th></th>
                    </thead>
                    @include('backend.users.partials.users-loop')
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>