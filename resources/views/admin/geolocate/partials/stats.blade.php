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
            @if(isset($geoLocateDataSource->data))
                @foreach($geoLocateDataSource->data['stats'] as $label => $stat)
                    <tr>
                        <td class="text-left">{{ $label }}</td>
                        <td>{{ $stat }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="2">{{ t('No current stats at this time') }}</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
