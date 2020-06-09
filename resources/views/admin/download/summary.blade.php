@extends('admin.layout.popup')

@section('title')
{{ __('pages.summary') }}
@endsection

@section('custom-style')
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .frame {
            display: block;
            width: 100vw;
            height: 100vh;
            max-width: 100%;
            margin: 0;
            padding: 0;
            border: 0 none;
            box-sizing: border-box;
        }
    </style>
@endsection

@section('content')
    <iframe class="frame" id="iframe"
            src="{{ route('admin.downloads.summaryHtml', [$expedition->project->id, $expedition->id]) }}"
            style="border:none;"></iframe>
@endsection

@section('custom-script')
<script>
    $('#iframe').on("load", function () {
        let $iframe = $('#iframe');
        let csrfVar = $('meta[name="csrf-token"]').attr('content');

        $iframe.contents().find('select').change(function () {
            let form = '<form method="post" target="_blank" id="editReconcile" style="margin-top: 5px" ' +
                'action="https://biospex.test/admin/projects/13/expeditions/260/reconciles" role="form">' +
                '<input name="_token" value="' + csrfVar + '" type="hidden">' +
                '<input type="hidden" id="frmData" name="data" value=""> ' +
                '<button type="submit" class="mt-5">Expert Review Reconciliation Ambiguities</button>' +
                '</form>';

            $iframe.contents().find('#editReconcile').remove();

            if ($(this).val() === '__all__') {
                $(this).after(form);
                let frmValues = [];
                let selector = 'tr[data-row-type="B"][data-problems!=""]';

                $iframe.contents().find(selector).each(function () {
                    let data = {};
                    data.id = $(this).data('subjectId');
                    data.columns = $(this).data('problems');
                    frmValues.push(data);
                });
                $iframe.contents().find('#frmData').val(JSON.stringify(frmValues));
            }
        });
    });

    function getContent() {
        return $('#iframe').contents();
    }
</script>
@endsection