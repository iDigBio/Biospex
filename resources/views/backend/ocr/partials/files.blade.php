<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <h3 class="box-title">Current OCR Files</h3>
            <div class="box-body table-responsive no-padding">
                {!!  Form::open([
                        'route' => ['admin.ocr.delete'],
                        'method' => 'delete',
                        'class' => 'form-horizontal',
                        'role' => 'form'
                        ]) !!}
                <table class="table table-hover" id="resources">
                    <thead>
                    <colgroup>
                        <col class="col-md-2">
                        <col class="col-md-9">
                        <col class="col-md-1">
                    </colgroup>
                    <th>
                        {!! Form::checkbox('selectall', null, null, ['id'=>'ocrCheckAll']) !!}
                        {!! Form::label('Select All', 'Select All') !!}
                    </th>
                    <th>OCR File</th>
                    <th></th>
                    </thead>
                    @include('backend.ocr.partials.files-loop')
                </table>
                {!! Form::submit(trans('buttons.delete'), array('class' => 'btn btn-danger')) !!}
                {!! Form::close() !!}
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>