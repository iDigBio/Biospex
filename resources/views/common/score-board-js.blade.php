<script>
$(function () {
    $('#scoreboardModal').on('show.bs.modal', function (e) {
        let $modal = $(this).find('.modal-body');
        let $button = $(e.relatedTarget); // Button that triggered the modal
        let channel = $button.data('channel');
        let eventId = $button.data('event');

        $modal.html('<div class="loader mx-auto"></div>');

        $modal.load($button.data('href'), function () {
            let $clock = $modal.find('.clockdiv');
            let deadline = $modal.find('#date').html(); // Sun Sep 30 2018 14:26:26 GMT-0400 (Eastern Daylight Time)
            if (deadline === 'Completed') {
                $clock.html('<h2 class="text-center">Completed</h2>');
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

    clockDiv();
});

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
</script>