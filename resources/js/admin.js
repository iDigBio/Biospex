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
        $.post($(this).data('url'), {frm: $(this).val()})
            .done(function (data) {
                $exportResults.html(data).find("div.entry").each(function () {
                    makeSortable($(this));
                });
            });
    });

    $exportResults.on('click', '.btn-add', function () {
        let $entry = $('.default').clone();
        $entry.appendTo($('#controls')).removeClass('default')
            .addClass('entry').show()
            .find('.export-field-default').removeClass('export-field-default').addClass('export-field');

        makeSortable($entry);
        renumber_prefix();

    }).on('click', '.btn-remove', function () {
        if ($('#controls').children('div.entry').length === 1) {
            return;
        }
        $('#controls div.entry:last').remove();
        renumber_prefix();
    }).on('click', '#geolocateSubmit', function(e){
        if (checkDuplicates()) {
            $('#duplicateWarning').show();
            return;
        }
        $('#exportGeoLocateFrm').submit();
    }).on('change', 'select.export-field', function (){
        $('#duplicateWarning').hide();
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

// Make select box rows sortable
function makeSortable($entry, options) {
    $entry.sortable({
        items: '> div.sort',
        placeholder: "sort-highlight",
        tolerance: 'pointer',
        stop: function (event, ui) {
            let idsInOrder = $(this).sortable('toArray', {
                attribute: 'data-id'
            });

            let $input = $('input#order' + ui.item.attr('data-count'));
            $input.val(idsInOrder);
        }
    }).find('select').each(function () {
        $(this).selectpicker();
    }).disableSelection();
}

// Renumber prefixes when rows add and removed.
function renumber_prefix() {
    let controls = $('#controls');
    controls.children('div.entry').each(function (index) {
        $(this).find('.hidden').each(function () {
            $(this).attr('id', 'order' + index);
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

// Check duplicate export field selection before submitting form.
function checkDuplicates() {
    let dup = false;
    let fieldOptions = [];
    $('select.export-field').each(function (){
        if ($.inArray($(this).val(), fieldOptions) > -1) {
            dup = true;
        }

        fieldOptions.push($(this).val());
    });

    return dup;
}
