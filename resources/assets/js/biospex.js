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

                $('#ocrHtml').html(ocrHtml);
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

                $('#exportHtml').html(exportHtml);
            });
    }

    $('[data-toggle="tooltip"]').tooltip();
});

$(function () {
    $(document).on('click', '.btn-add', function (e) {
        e.preventDefault();

        let controls = $('.controls'),
            currentEntry = $(this).parents('.entry:first'),
            newEntry = $(currentEntry.clone()).appendTo(controls);

        newEntry.find(":input").each(function(){
            $(this).val('');
        });
        newEntry.find('.fileName').html('');
        controls.find('.entry:not(:last) .btn-add')
            .removeClass('btn-add').addClass('btn-remove')
            .removeClass('btn-success').addClass('btn-danger')
            .html('<span class="fa fa-minus fa-lrg"></span>');
        renumber_resources()
    }).on('click', '.btn-remove', function (e) {
        $(this).parents('.entry:first').remove();
        renumber_resources()
        e.preventDefault();
        return false;
    });
});

function renumber_resources() {
    let controls = $('.controls');
    controls.children('.entry').each(function(index) {
        let prefix = "resources[" + index + "]";
        $(this).find(":input").each(function() {
            this.name = this.name.replace(/resources\[\d+\]/, prefix);
        });
    });
    $("[name='resourceFields']").val(controls.children().length);
}

