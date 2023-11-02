<div class="col-md-10 mx-auto mt-3 mb-3">
    <div class="row">
        <table id="stat-tbl" class="table table-striped table-bordered dt-responsive nowrap"
               style="width:100%; font-size: .8rem">
            <thead>
            <tr>
                <th>{{ t('Label') }}</th>
                <th>{{ t('Count') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($expedition->geoLocateDataSource->data['stats'] as $label => $count)
                <tr>
                    <td class="text-left">{{ $label }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
