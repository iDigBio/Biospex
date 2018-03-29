$(document).ready(function () {

    // If process modal, prevent click and return without active class
    // get current URL path and assign 'active' class
    let href = window.location.href;
    $('.nav > li > a[href="' + href + '"]').parent().addClass('active');

    $(".preventDefault").click(function (e) {
        e.preventDefault();
    });

    $('a.polling').click(function (event) {
        $('#ocrHtml').html('Retrieving data');
        $('#exportHtml').html('Retrieving data');
        $.get("/poll");
    });

    let tableSort = $(".table-sort");
    tableSort.tablesorter({
        // this will apply the bootstrap theme if "uitheme" widget is included
        // the widgetOptions.uitheme is no longer required to be set
        theme: "bootstrap",
        widgets: ['uitheme', 'zebra'],
        headerTemplate: '{content}{icon}'
    });

    tableSort.bind("sortStart", function () {
        $('.ajax-rows').remove();
        $('.toggle').removeClass("fa-folder-open").addClass("fa-folder");
    });

    $('#add_target').on('click', function () {
        let first = $('div.target:first');
        let last = $('div.target:last');


        if (first.is(":hidden")) {
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
        $('#targetCount').val($("div.target:visible").length);
    });
    $('#remove_target').click(function () {
        let target = $('div.target');
        if (target.length === 1) {
            target.hide();
        } else {
            $('div.target:last').remove();
        }
        $('#targetCount').val($("div.target:visible").length);
    });

    $("#form-data").validate({
        rules: {
            dwc: {
                required: true,
                extension: "zip"
            }
        }
    });
    $("#form-recordset").validate({
        rules: {
            recordset: {
                required: true
            }
        }
    });
    $("#form-data-url").validate({
        rules: {
            "data-url": {
                required: true
            }
        }
    });
    $("#form-trans").validate({
        rules: {
            transcription: {
                required: true,
                extension: "csv"
            }
        }
    });

    let userGroup = $("#userGroup");
    let groupInput = $("#groupInput");
    userGroup.change(function () {
        this.value === 'new' ? groupInput.show() : groupInput.hide();
    });
    if (userGroup.length) {
        userGroup.val() === 'new' ? groupInput.show() : groupInput.hide();
    }

    $('#selectall').click(function () {  //on click
        let checkboxAll = $('.checkbox-all');
        if (this.checked) { // check select status
            checkboxAll.each(function () { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"
            });
        } else {
            checkboxAll.each(function () { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"
            });
        }
    });

    let homeProjectList = $('a.home-project-list');
    homeProjectList.click(function (event) {
        let count = $(this).data("count");
        $.get($(this).attr("href") + '/' + count, function (data) {
            $(".recent-projects-pane").html(data);
            homeProjectList.data("count", count + 5);
        });
        event.preventDefault();
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

    $('div.panel-heading i').click(function () {
        let div = '#transcribers';
        if ($(div).height() !== 370) {
            $(div).css('height', '370px');
            $(div).css('overflow', 'scroll');
        } else {
            $(div).css('height', 'auto');
            $(div).css('overflow', '');
        }
    });

    if ($('#processModal').length) {
        Echo.channel(Laravel.ocrChannel)
            .listen('PollOcrEvent', (e) => {
                let groupIds = $.parseJSON(Laravel.groupIds);
                let ocrHtml = '';
                if ($.isArray(e.data)) {
                    $.each(e.data, function (index) {
                        if ($.inArray(e.data[index].groupId, groupIds) === -1) {
                            return true;
                        }
                        ocrHtml += e.data[index].notice;
                    });
                } else {
                    ocrHtml = e.data;
                }

                $('#ocrHtml').html(ocrHtml === "" ? "No processes running at this time." : ocrHtml);
            });

        Echo.channel(Laravel.exportChannel)
            .listen('PollExportEvent', (e) => {
                let groupIds = $.parseJSON(Laravel.groupIds);
                let exportHtml = '';

                if ($.isArray(e.data)) {
                    $.each(e.data, function (index) {
                        if ($.inArray(e.data[index].groupId, groupIds) === -1) {
                            return true;
                        }
                        exportHtml += e.data[index].notice;
                    });
                } else {
                    exportHtml = e.data;
                }

                $('#exportHtml').html(exportHtml === "" ? "No processes running at this time." : exportHtml);
            });
    }

    if ($('#event-boards').length) {
        let projectId = $("#projectId").data('value');
        //Echo.private(Laravel.boardChannel + '.' + projectId)
        Echo.channel(`boardloc.53`)
            .listen('PollBoardEvent', (e) => {
                if (e.data['id'] === projectId) {
                    $('#event-boards').html(e.data['html']);
                }
            });
    }

    $('[data-toggle="tooltip"]').tooltip();

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

    $('.js-tooltip').tooltip();
    $('.js-copy').click(function () {
        let text = $(this).attr('data-copy');
        let el = $(this);
        copyToClipboard(text, el);
    });

});

$(function () {
    $(document).on('click', '.btn-add', function (e) {
        e.preventDefault();

        let controls = $('.controls'),
            currentEntry = $(this).parents('.entry:first'),
            newEntry = $(currentEntry.clone()).appendTo(controls);

        newEntry.find(":input").each(function () {
            $(this).val('');
        });
        newEntry.find('.fileName').html('');
        controls.find('.entry:last .btn-add')
            .removeClass('btn-add').addClass('btn-remove')
            .removeClass('btn-success').addClass('btn-danger')
            .html('<span class="fa fa-minus fa-lrg"></span>');
        renumber_prefix()
    }).on('click', '.btn-remove', function (e) {
        $(this).parents('.entry:first').remove();
        renumber_prefix()
        e.preventDefault();
        return false;
    });
});

function renumber_prefix() {
    let controls = $('.controls');
    controls.children('.entry').each(function (index) {
        $(this).find(":input").each(function () {
            this.name = this.name.replace(/\[[0-9]+\]/g, '[' + index + ']');
        });
    });
    $("[name='entries']").val(controls.children().length);
}

function copyToClipboard(text, el) {
    let copyTest = document.queryCommandSupported('copy');
    let elOriginalText = el.attr('data-original-title');

    if (copyTest === true) {
        let copyTextArea = document.createElement("textarea");
        copyTextArea.value = text;
        document.body.appendChild(copyTextArea);
        copyTextArea.select();
        try {
            let successful = document.execCommand('copy');
            let msg = successful ? 'Copied!' : 'Whoops, not copied!';
            el.attr('data-original-title', msg).tooltip('show');
        } catch (err) {
            console.log('Oops, unable to copy');
        }
        document.body.removeChild(copyTextArea);
        el.attr('data-original-title', elOriginalText);
    } else {
        // Fallback if browser doesn't support .execCommand('copy')
        window.prompt("Copy to clipboard: Ctrl+C or Command+C, Enter", text);
    }
}


