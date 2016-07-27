<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <h3 class="box-title">Current Resources</h3>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover" id="resources">
                    <colgroup>
                        <col class="col-md-0">
                        <col class="col-md-2">
                        <col class="col-md-6">
                        <col class="col-md-2">
                        <col class="col-md-0">
                    </colgroup>
                    <thead>
                        <th>Order</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Document</th>
                        <th></th>
                    </thead>
                    @include('backend.resources.partials.resources-loop')
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>