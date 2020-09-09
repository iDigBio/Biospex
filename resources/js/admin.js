$(function () {

    // Add token to any ajax requests.
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Set prevent default for links
    $('.prevent-default').click(function (e) {
        e.preventDefault();
    });

    // Tooltips
    $('[data-hover="tooltip"]').tooltip();
    $(document).ajaxComplete(function () {
        $('[data-hover="tooltip"]').tooltip();
    });

    $(".hamburger").click(function () {
        $(this).toggleClass("is-active");
    });

    $(document).on('change', '.custom-file-input', function () {
        let fileName = $(this).val().split('\\').pop();
        $(this).prev('.custom-file-label').addClass("selected").html(fileName);
    });

    if (Laravel.flashMessage.length) {
        notify(Laravel.flashIcon, Laravel.flashMessage, Laravel.flashType);
    }

    $('[data-confirm=confirmation]').on('click', function (e) {
        let url = $(this).is("[data-href]") ? $(this).data("href") : $(this).attr('href');
        let method = $(this).data('method');
        bootbox.confirm({
            title: $(this).data('title'),
            message: $(this).data('content'),
            buttons: {
                cancel: {
                    label: '<i class="fas fa-times-circle"></i> Cancel',
                    className: 'btn btn-primary'
                },
                confirm: {
                    label: '<i class="fas fa-check-circle"></i> Confirm',
                    className: 'btn btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    $(this).append(function () {
                        let methodForm = "\n";
                        methodForm += "<form action='" + url + "' method='POST' style='display:none'>\n";
                        methodForm += "<input type='hidden' name='_method' value='" + method + "'>\n";
                        methodForm += "<input type='hidden' name='_token' value='" + $('meta[name=csrf-token]').attr('content') + "'>\n";
                        methodForm += "</form>\n";
                        return methodForm;
                    }).find('form').submit();
                }
            }
        });
    });

    $(window).resize(function () {
        let docHeight = $(window).height();
        let footerHeight = $('#footer').height();
        let footerTop = $('#footer').position().top + footerHeight;
        let marginTop = (docHeight - footerTop + 0);
        // When not want the scrollbar if content would fit to screen just change the value of 10 to 0
        // The scrollbar will show up if content not fits to screen.

        if (footerTop < docHeight)
            $('#footer').css('margin-top', marginTop + 'px'); // padding of 30 on footer
        else
            $('#footer').css('margin-top', '0px');
    });
    $(window).resize();

    $('#exportSelect input:radio').on('click', function () {
        $('#ajaxResult').html('<div class="loader mx-auto"></div>');
        $.get($(this).data('url'), function (data) {
            $('#ajaxResult').html(data).find('div.entry select').selectpicker();
        });
    });

    $('#ajaxResult').on('click', '.btn-add', function (e) {
        //e.preventDefault();
        $('#controls .default').clone()
            .appendTo($('#controls')).removeClass('default')
            .addClass('entry').show().find('select').selectpicker();

        renumber_prefix();
    }).on('click', '.btn-remove', function (e) {
        $('#controls div.entry:last').remove();
        renumber_prefix();
    })
});

function notify(icon, msg, type) {
    $.notify({
        icon: 'fas fa-' + icon + ' fa-2x',
        message: msg
    }, {
        type: type,
        placement: {
            from: "top",
            align: "center"
        },
        offset: 50,
        spacing: 10,
        animate: {
            enter: 'animated fadeInDown',
            exit: 'animated fadeOutUp'
        }
    });
}

function renumber_prefix() {
    let controls = $('#controls');
    controls.children('div.entry').each(function (index) {
        $(this).find('select').each(function () {
            //$(this).attr('id', $(this).attr('id').replace(/\[[0-9]+\]/g, '[' + index + ']'));
            $(this).attr('name', $(this).attr('name').replace(/\[[0-9]+\]/g, '[' + index + ']'));
        });
    });
    $('[name="entries"]').val(controls.children('div.entry').length);
}