$(function () {

    $('.sidebar-menu li a[href="' + location.href + '"]')
        .addClass('active')
        .closest('li').addClass('active')
        .closest("ul")
        .css('display', 'block')
        .closest('li')
        .addClass('active');

    $('textarea').wysihtml5();

    $(".source li").draggable({
        addClasses: false,
        appendTo: "body",
        helper: "clone"
    });

    $(".target").droppable({
        addClasses: false,
        activeClass: "listActive",
        accept: ":not(.ui-sortable-helper)",
        drop: function(event, ui) {
            $(this).find(".placeholder").remove();
            var link = $("<a href='#' class='dismiss'>x</a>");
            var id = ui.draggable.attr('id');
            var list = $('<li id="'+ id +'"></li>').text(ui.draggable.text());
            $(list).append(link);
            $(list).appendTo(this);
        }
    }).sortable({
        items: "li:not(.placeholder)",
        sort: function() {
            $(this).removeClass("listActive");
        }
    }).on("click", ".dismiss", function(event) {
        event.preventDefault();
        $(this).parent().remove();
    });

    $('#workflow').submit(function(){
        if ($('ul.target').children().length < 1) {
            $('#workflow').append($('<input>').attr({'type':'hidden','name':'actors[0][id]'}).val(''));

        } else {
            $('ul.target').children().each(function (i) {
                var id = $(this).attr('id');
                $('#workflow').append($('<input>').attr({'type': 'hidden', 'name': 'actors[' + i + '][id]'}).val(id));
            });
        }
    });

    //iCheck for checkbox and radio inputs
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_minimal-blue'
    });

    $.fn.editable.defaults.mode = 'inline';
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
});
