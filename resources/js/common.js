$(function () {
    // Add token to any ajax requests.
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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
        placement: {
            from: "top",
            align: "center"
        },
        offset: 50,
        spacing: 10,
        animate: {
            enter: 'animated bounceInDown',
            exit: 'animated bounceOutUp'
        },
        delay: 3000,
    });
}

