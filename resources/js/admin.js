$(function () {

    $('#add_target').on('click', function () {
        let first = $('div.target:first');
        let last = $('div.target:last');

        if (first.is(':hidden')) {
            first.show();
        } else {
            last.after(last.clone()
                .find(':input')
                .each(function () {
                    this.name = this.name.replace(/\[(\d+)\]/, function (str, p1) {
                        return '[' + (parseInt(p1, 10) + 1) + ']';
                    });
                })
                .end());
        }
        $('#target-count').val($('div.target:visible').length);
    });

    $('#remove_target').click(function () {
        let target = $('div.target');
        if (target.length === 1) {
            target.hide();
        } else {
            $('div.target:last').remove();
        }
        $('#target-count').val($('div.target:visible').length);
    });

    let userGroup = $('#user-group');
    let groupInput = $('#group-input');
    userGroup.change(function () {
        this.value === 'new' ? groupInput.show() : groupInput.hide();
    });
    if (userGroup.length) {
        userGroup.val() === 'new' ? groupInput.show() : groupInput.hide();
    }

    $('#select-all').click(function () {  //on click
        let checkboxAll = $('.checkbox-all');
        if (this.checked) { // check select status
            checkboxAll.each(function () { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class 'checkbox1'
            });
        } else {
            checkboxAll.each(function () { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class 'checkbox1'
            });
        }
    });

    let homeProjectList = $('a.home-project-list');
    homeProjectList.click(function (e) {
        let count = $(this).data('count');
        $.get($(this).attr('href') + '/' + count, function (data) {
            $('.recent-projects-pane').html(data);
            homeProjectList.data('count', count + 5);
        });
        e.preventDefault();
    });

    let textarea = $('.textarea');
    if (textarea.length) {
        textarea.summernote({
            height: 200,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ]
        });
    }

    $('[data-name="js-copy"]').on('click', function () {
        copyToClipboard($(this));
    });

    $.datetimepicker.setLocale('en');
    $('.datetimepicker').datetimepicker({
        format: 'Y-m-d H:i',
        allowTimes: [
            '00:00', '00:30', '01:00', '01:30', '02:00', '02:30', '03:00', '03:30',
            '04:00', '04:30', '05:00', '05:30', '06:00', '06:30', '07:00', '07:30',
            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
            '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
            '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30',
            '20:00', '20:30', '21:00', '21:30', '22:00', '22:30'
        ]
    });

    $(document).on('click', '.btn-add', function (e) {
        e.preventDefault();

        let controls = $('.controls'),
            currentEntry = $(this).parents('.entry:first'),
            newEntry = $(currentEntry.clone()).appendTo(controls);

        newEntry.find(':input').each(function () {
            $(this).val('');
        });
        newEntry.find('.fileName').html('');
        controls.find('.entry:last span.btn-add')
            .removeClass('btn-add').addClass('btn-remove')
            .html('<i class="fas fa-minus"></i>');
        renumber_resource()
    }).on('click', '.btn-remove', function (e) {
        $(this).parents('.entry:first').remove();
        renumber_resource();
        e.preventDefault();
        return false;
    }).on('submit', '.projectFrm', function (e) {
        $("#entries").val($('.controls').children().length);
    });

    $(document).on('click', '[data-confirm=confirmation]', function (e) {
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

    $('#expedition-download-modal').on('show.bs.modal', function (e) {
        let $button = $(e.relatedTarget);
        let $modal = $(this).find('.modal-body');
        $modal.html('<div class="loader mx-auto"></div>');
        $modal.load($button.data("remote"));

    }).on('hidden.bs.modal', function () {
        $(this).find('.modal-body').html('');
    });

    $('#import-modal').on('show.bs.modal', function (e) {
        let $button = $(e.relatedTarget);
        let $modal = $(this).find('.modal-body');
        $modal.html('<div class="loader mx-auto"></div>');
        $modal.load($button.data("remote"));

    }).on('hidden.bs.modal', function () {
        $(this).find('.modal-body').html('');
    });

    $('#invite-modal').on('show.bs.modal', function (e) {
        let $button = $(e.relatedTarget);
        let $modal = $(this).find('.modal-body');
        $modal.html('<div class="loader mx-auto"></div>');
        $modal.load($button.data("remote"));

    }).on('hidden.bs.modal', function () {
        $(this).find('.modal-body').html('');
    });

    $('.project-banner').on('click', function (e) {
        let img = $(this).data('name');
        $('#banner_file').val(img);
        $('#banner_file').attr('value', img);
        $('#banner-img').attr('src', '/images/habitat-banners/' + img);
        $("#project-banner-modal .close").click();
    });

    $('#jqgrid-modal').on('show.bs.modal', function (e) {
        let $button = $(e.relatedTarget);
        let $modal = $(this).find('.modal-body');
        $modal.html('<div class="loader mx-auto"></div><div>Retrieving Image</div>');
        if ($button.attr('class') === 'url-view') {
            $modal.html($button.data("remote"));
        } else {
            $modal.load($button.data("remote"));
        }

    }).on('hidden.bs.modal', function () {
        $(this).find('.modal-body').html('');
    });

    $(document).on('change', '.custom-file-input', function () {
        let fileName = $(this).val().split('\\').pop();
        $(this).prev('.custom-file-label').addClass("selected").html(fileName);
    });

    setInterval(function () {
        let $footer = $('#footer');
        let docHeight = $(window).height();
        let footerHeight = $footer.height();
        let footerTop = $footer.position().top + footerHeight;
        let marginTop = (docHeight - footerTop + 10);

        if (footerTop < docHeight)
            $footer.css('margin-top', marginTop + 'px'); // padding of 30 on footer
        else
            $footer.css('margin-top', '0px');
    }, 250);

    // this is the id of the form
    $("#workflowIdFrm").submit(function (e) {

        e.preventDefault(); // avoid to execute the actual submit of the form.

        let form = $(this);
        let actionUrl = form.attr('action');

        $.ajax({
            type: "POST",
            url: actionUrl,
            data: form.serialize(), // serializes the form's elements.
            success: function (data) {
                let text = data['code'] === 200 ? 'text-success' : 'text-danger';
                $('.feedback').addClass(text).html(data['message']); // show response from the php script.
            }
        });

    });

    let $exportResults = $('#exportResults');
    let $sourceType = $('.sourceType');
    if ($sourceType.length > 0) {
        let $input = $("input:radio.sourceType:checked");
        let value = $input.val();
        if (value !== '') {
            form($input, $exportResults)
        }
        $sourceType.on('change', function () {
            if($("#frmDataExists").val()) {
                notify("exclamation-circle", "Cannot change source after form data is saved. Delete form to change source.", "warning");
                $input.prop('checked', true);
                return;
            }
            form($(this), $exportResults)
        });
    }

    $exportResults.on('click', '.btn-add', function () {
        let $entry = $('.default').clone();
        $entry.appendTo($('#controls')).removeClass('default').addClass('entry').show();
        $entry.find('.geolocate-field-default').removeClass('geolocate-field-default').addClass('geolocate-field').prop('required',true);
        $entry.find('.header-select-default').removeClass('header-select-default').addClass('header-select').prop('required',true);

        makeSelect($entry);
        renumber_geolocate();
    }).on('click', '.btn-remove', function () {
        if ($('#controls').children('div.entry').length === 1) {
            return;
        }
        $('#controls div.entry:last').remove();
        renumber_geolocate();
    }).on('click', '#process', function (){
        $('form#geolocateFrm').attr('action', $(this).data('url')).trigger('submit');
    });

    $('form#geolocateFrm').on('submit', function (e) {
        if (checkDuplicates()) {
            notify("exclamation-circle", "GeoLocate field cannot contain duplicate values.", "warning");

            return false;
        }

        let fields = checkRequiredValues();
        if (fields.length > 0) {
            notify("exclamation-circle", fields.toString() + ' GeoLocate fields are required.', "warning");

            return false;
        }

        $("#entries").val($('#controls').children().length);
    });

});

form = function($input, $exportResults) {
    $exportResults.html('<div class="mt-5 loader mx-auto"></div>');
    $.post($input.data('url'), {frm: $input.val()})
        .done(function (data) {
            $exportResults.html(data).find("div.entry").each(function () {
                makeSelect($(this));
            });
        });
}

function renumber_resource() {
    $('.controls').children('.entry').each(function (index) {
        $(this).find('legend').html('Resource ' + (index + 1));
        $(this).find(':input').each(function () {
            $(this).attr('id', $(this).attr('id').replace(/\[[0-9]+\]/g, '[' + index + ']'));
            $(this).attr('name', $(this).attr('name').replace(/\[[0-9]+\]/g, '[' + index + ']'));
        });
    });
}

function copyToClipboard(el) {
    let copyTest = document.queryCommandSupported('copy');
    let copyText = el.attr('data-value');
    let titleText = el.attr('data-original-title');

    if (copyTest === true) {
        let copyTextArea = document.createElement('textarea');
        copyTextArea.value = copyText;
        document.body.appendChild(copyTextArea);
        copyTextArea.select();
        try {
            let successful = document.execCommand('copy');
            let msg = successful ? 'Copied!' : 'Whoops, not copied!';
            el.attr('data-original-title', msg).tooltip('show');
        } catch (err) {
            alert('Oops, unable to copy');
        }
        document.body.removeChild(copyTextArea);
        el.attr('data-original-title', titleText);
    } else {
        // Fallback if browser doesn't support .execCommand('copy')
        window.prompt('Copy to clipboard: Ctrl+C or Command+C, Enter', text);
    }
}

// Make select box rows sortable and bootstrap-select
makeSelect = function($entry, options) {
    $entry.find('select').each(function () {
        $(this).selectpicker();
    }).disableSelection();
}

// Renumber prefixes when rows add and removed.
renumber_geolocate = function() {
    $('#controls').children('div.entry').each(function (index) {
        $(this).children(":first-child")
            .attr('id', 'order' + index)
            .attr('name', 'exportFields[' + index + '][order]');
        $(this).find('select').each(function () {
            $(this).attr('name', $(this).attr('name').replace(/\[[0-9]+\]/g, '[' + index + ']'));
        });
    });
}

// Check duplicate export field selection before submitting form.
checkDuplicates = function() {
    let dup = false;
    let fieldOptions = [];
    $('select.geolocate-field').each(function () {
        if ($.inArray($(this).val(), fieldOptions) > -1) {
            dup = true;
        }

        fieldOptions.push($(this).val());
    });

    return dup;
}

checkRequiredValues = function() {
    let list = ["County", "Country", "Locality", "ScientificName", "StateProvince"];
    $('select.geolocate-field').each(function () {
        if ($.inArray($(this).val(), list) > -1) {
            list.splice($.inArray($(this).val(), list), 1);
        }
    });

    return list;
}
