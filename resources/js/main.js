$(function () {

    // Add token to any ajax requests.
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#external-carousel-btns li').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
    });

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

    /*
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
     */

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
        renumber_prefix()
    }).on('click', '.btn-remove', function (e) {
        $(this).parents('.entry:first').remove();
        renumber_prefix();
        e.preventDefault();
        return false;
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
        $('#banner-img').attr('src','/images/habitat-banners/'+img);
        $("#project-banner-modal .close").click();
    });

    $('#jqgrid-modal').on('show.bs.modal', function (e) {
        let $button = $(e.relatedTarget);
        let $modal = $(this).find('.modal-body');
        $modal.html('<div class="loader mx-auto"></div>');
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

    setInterval(function() {
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

    // Set prevent default for links
    $(document).on('click', '.prevent-default', function (e) {
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

    if (Laravel.flashMessage.length) {
        notify(Laravel.flashIcon, Laravel.flashMessage, Laravel.flashType);
    }

    $('.sort-page').on('click', function () {
        sortPage($(this));
    });

    $('.toggle-view-btn').on('click', function () {
        let html = $(this).html();
        let value = $(this).data('value');
        $(this).html(value);
        $(this).data('value', html);
    });

    $('#scoreboard-modal').on('show.bs.modal', function (e) {
        let $modal = $(this).find('.modal-body');
        let $button = $(e.relatedTarget); // Button that triggered the modal
        let channel = $button.data('channel');
        let eventId = $button.data('event');

        $modal.html('<div class="loader mx-auto"></div>');

        $modal.load($button.data('href'), function () {
            let $clock = $modal.find('.clockdiv');
            let deadline = $modal.find('#date').html(); // Sun Sep 30 2018 14:26:26 GMT-0400 (Eastern Daylight Time)
            if (deadline === null) {
                return;
            }
            initializeClock($clock, deadline);

            Echo.channel(channel)
                .listen('ScoreboardEvent', (e) => {
                    $.each(e.data, function (id, val) {
                        if (Number(id) === Number(eventId)) {
                            $modal.html(val);
                            $clock = $modal.find('.clockdiv');
                            deadline = $modal.find('#date').html();
                            initializeClock($clock, deadline);
                        }
                    });
                });
        });
    }).on('hidden.bs.modal', function () {
        $(this).find('.modal-body').html('');
        clearInterval(timeInterval);
    });

    // Used in Admin but placed in common.js because it calls notify function.
    $('.event-export').on('click', function () {
        let url = $(this).data('href');
        let successMsg = $(this).data('success');
        let errorMsg = $(this).data('error');
        notify('info-circle', 'Request is being sent.', 'info');
        $.get(url, function (data) {
            let icon = data === true ? 'check-circle' : 'times-circle';
            let msg = data === true ? successMsg : errorMsg;
            let type = data === true ? 'success' : 'danger';
            notify(icon, msg, type);
        });
    });

    clockDiv();

    if ($('#process-modal').length) {
        $('#process-modal').on('shown.bs.modal', function (e) {
            fetchPoll();
            pollInterval = setInterval(fetchPoll, 60000);
        }).on('hidden.bs.modal', function () {
            clearInterval(pollInterval);
        });

        Echo.channel(Laravel.ocrChannel)
            .listen('PollOcrEvent', (e) => {
                let ocrHtml = polling_data(e.data);
                $('#ocr-html').html(ocrHtml);
            });

        Echo.channel(Laravel.exportChannel)
            .listen('PollExportEvent', (e) => {
                let exportHtml = polling_data(e.data);
                $('#export-html').html(exportHtml);
            });
    }

});

function renumber_prefix() {
    let controls = $('.controls');
    controls.children('.entry').each(function (index) {
        $(this).find('legend').html('Resource ' + (index+1));
        $(this).find(':input').each(function () {
            $(this).attr('id', $(this).attr('id').replace(/\[[0-9]+\]/g, '[' + index + ']'));
            $(this).attr('name', $(this).attr('name').replace(/\[[0-9]+\]/g, '[' + index + ']'));
        });
    });
    $('[name="entries"]').val(controls.children().length);
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


// Fetch poll data
function fetchPoll(){
    $.get( "/poll");
}

// Loop data from polling
function polling_data(data) {
    let groupIds = $.parseJSON(Laravel.groupIds);
    let groupData = '';
    $.each(data['payload'], function (index) {
        if ($.inArray(data['payload'][index].groupId, groupIds) === -1) {
            return true;
        }
        groupData += data['payload'][index].notice;
    });

    return !groupData ? data['message'] : groupData;
}

/**
 * data attributes: project-id, type (active, completed), sort, order, url, target
 * @param element
 */
function sortPage(element) {
    let data = element.data();
    let $target = $('#' + data.target); // target container

    $target.html('<span class="loader"></span>');

    $.post(data.url, data)
        .done(function (response) {
            $target.html(response);
            setOrder(data.order, element);
            clockDiv();
        });
}

function setOrder(order, element) {
    let $icon = element.find('i');
    element.siblings('span').data('order', 'asc').find('i').removeClass().addClass('fas fa-sort');

    switch (order) {
        case 'asc':
            element.data('order', 'desc');
            $icon.removeClass().addClass('fas fa-sort-up');
            break;
        case 'desc':
            element.data('order', '');
            $icon.removeClass().addClass('fas fa-sort-down');
            break;
        default:
            element.data('order', 'asc');
            $icon.removeClass().addClass('fas fa-sort');
            break;
    }
}

let timeInterval;

function getTimeRemaining(endTime) {
    let t = Date.parse(endTime) - Date.parse(new Date());
    let seconds = Math.floor((t / 1000) % 60);
    let minutes = Math.floor((t / 1000 / 60) % 60);
    let hours = Math.floor((t / (1000 * 60 * 60)) % 24);
    let days = Math.floor(t / (1000 * 60 * 60 * 24));
    return {
        'total': t,
        'days': days,
        'hours': hours,
        'minutes': minutes,
        'seconds': seconds
    };
}

function clockDiv() {
    $('.clockdiv').each(function () {
        let deadline = $(this).data('value'); // Sun Sep 30 2018 14:26:26 GMT-0400 (Eastern Daylight Time)
        initializeClock($(this), deadline);
    });
}

function initializeClock($clock, endTime) {

    let daysSpan = $clock.find('.days');
    let hoursSpan = $clock.find('.hours');
    let minutesSpan = $clock.find('.minutes');
    let secondsSpan = $clock.find('.seconds');

    function updateClock() {
        let t = getTimeRemaining(endTime);
        daysSpan.html(t.days);
        hoursSpan.html(('0' + t.hours).slice(-2));
        minutesSpan.html(('0' + t.minutes).slice(-2));
        secondsSpan.html(('0' + t.seconds).slice(-2));

        if (t.total <= 0) {
            clearInterval(timeInterval);
        }
    }

    updateClock();
    timeInterval = setInterval(updateClock, 1000);
}

function notify(icon, msg, type) {
    $.notify({
        icon: 'fas fa-' + icon + ' fa-2x',
        message: msg
    }, {
        type: type,
        allow_dismiss: true,
        placement: {
            from: "top",
            align: "center"
        },
        offset: 5,
        spacing: 10,
        animate: {
            enter: "animate__animated animate__fadeInDown",
            exit: "animate__animated animate__fadeOutUp"
        },
        delay: 3000,
    });
}
