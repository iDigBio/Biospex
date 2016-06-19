$(function () {

    $('.sidebar-menu li a[href="' + location.href + '"]')
        .addClass('active')
        .closest('li').addClass('active')
        .closest("ul")
        .css('display', 'block')
        .closest('li')
        .addClass('active');

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

    $('.ckeditor').ckeditor();

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

});