@extends('backend.layouts.app')

@section('htmlheader_title')
    Translations
@endsection

@section('contentheader_title', 'Translations')


@section('main-content')
@section('styles')
    <!-- Needed for Translations Package Editable -->
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css"
          rel="stylesheet"/>
@endsection
<div class="row">
    <div class="col-xs-12">
        @include('vendor.translation-manager.index')
    </div>
</div>
@section('scripts')
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ujs/1.2.1/rails.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script>
        $('.edit-in-place').editable({
            error: function (response, newValue) {
                if (response.status === 500) {
                    return 'Service unavailable. Please try later.';
                } else {
                    var obj = $.parseJSON(response.responseText); //  response.responseText;
                    return obj.name;
                }
            }
        });

        $('.editable').editable().on('hidden', function (e, reason) {
            var locale = $(this).data('locale');
            if (reason === 'save') {
                $(this).removeClass('status-0').addClass('status-1');
            }
            if (reason === 'save' || reason === 'nochange') {
                var $next = $(this).closest('tr').next().find('.editable.locale-' + locale);
                setTimeout(function () {
                    $next.editable('show');
                }, 300);
            }
        });

        $('.group-select').on('change', function () {
            var group = $(this).val();
            var url = $(this).find(':selected').data('route')
            if (group) {
                window.location.href = url + '/' + $(this).val();
            } else {
                window.location.href = url;
            }
        });

        $("a.delete-key").click(function (event) {
            event.preventDefault();
            var row = $(this).closest('tr');
            var url = $(this).attr('href');
            var id = row.attr('id');
            $.post(url, {id: id}, function () {
                row.remove();
            });
        });

        $('.form-import').on('ajax:success', function (e, data) {
            $('div.success-import strong.counter').text(data.counter);
            $('div.success-import').slideDown();
        });

        $('.form-find').on('ajax:success', function (e, data) {
            $('div.success-find strong.counter').text(data.counter);
            $('div.success-find').slideDown();
        });

        $('.form-publish').on('ajax:success', function (e, data) {
            $('div.success-publish').slideDown();
        });
    </script>
@endsection
@endsection