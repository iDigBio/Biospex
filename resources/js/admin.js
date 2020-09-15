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

    $('[data-confirm=confirmation]').on('click', function () {
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
        let $footer = $('#footer');
        let docHeight = $(window).height();
        let footerHeight = $footer.height();
        let footerTop = $footer.position().top + footerHeight;
        let marginTop = (docHeight - footerTop);
        // When not want the scrollbar if content would fit to screen just change the value of 10 to 0
        // The scrollbar will show up if content not fits to screen.

        if (footerTop < docHeight)
            $footer.css('margin-top', marginTop + 'px'); // padding of 30 on footer
        else
            $footer.css('margin-top', '0px');
    });
    $(window).resize();

    let $exportResults = $('#exportResults');
    $('#geolocateSelect').on('change', function () {
        $('#geolocate').collapse('hide');
        $exportResults.html('<div class="mt-5 loader mx-auto"></div>');
        $.post( $(this).data('url'), { frm: $(this).val() })
            .done(function( data ) {
                $exportResults.html(data).find('div.entry select').selectpicker();
                sortable();
            });
    });

    $exportResults.on('click', '.btn-add', function () {
        //e.preventDefault();
        $('.default').clone()
            .appendTo($('#controls')).removeClass('default')
            .addClass('entry').show().find('select').selectpicker();

        renumber_prefix();
        sortable();

    }).on('click', '.btn-remove', function () {
        if ($('#controls').children('div.entry').length === 1) {
            return;
        }
        $('#controls div.entry:last').remove();
        renumber_prefix();
    });

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

function sortable() {
    $(".entry").each(function () {
        $(this).sortable({
            items: '> div.sort',
            placeholder: "sort-highlight",
            tolerance: 'pointer',
            stop: function (event, ui) {
                let idsInOrder = $(this).sortable('toArray', {
                    attribute: 'data-id'
                });

                console.log(idsInOrder);

                let $input = $('input#order'+ui.item.attr('data-count'));
                $input.val(idsInOrder);
            }
        }).disableSelection();
    });
}

function renumber_prefix() {
    let controls = $('#controls');
    controls.children('div.entry').each(function (index) {
        $(this).find('.hidden').each(function () {
            $(this).attr('id', 'order'+index);
            $(this).attr('name', $(this).attr('name').replace(/\[[0-9]+\]/g, '[' + index + ']'));
        });

        $(this).find('.sort').each(function () {
            $(this).attr('data-count', index);
        });

        $(this).find('select').each(function () {
            //$(this).attr('id', $(this).attr('id').replace(/\[[0-9]+\]/g, '[' + index + ']'));
            $(this).attr('name', $(this).attr('name').replace(/\[[0-9]+\]/g, '[' + index + ']'));
        });
    });
    $('[name="entries"]').val(controls.children('div.entry').length);
}