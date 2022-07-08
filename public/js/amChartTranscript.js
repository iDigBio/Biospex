$(function () {
    am4core.options.autoDispose = true;

    let buildChart = function (data) {
            let transcripts = am4core.createFromConfig(data, "transcripts", am4charts.XYChart);
            let cellSize = 1.5;
            transcripts.events.on("datavalidated", function (ev) {
                // Get objects of interest
                let chart = ev.target;
                let categoryAxis = chart.yAxes.getIndex(0);

                // Calculate how we need to adjust chart height
                let adjustHeight = chart.data.length * cellSize - categoryAxis.pixelHeight;

                // get current chart height
                let targetHeight = chart.pixelHeight + adjustHeight;

                // Set it on chart's container
                chart.svgContainer.htmlElement.style.height = targetHeight + "px";
            });
            transcripts.events.on('ready', function () {
                $("#script-modal").modal("hide");
            });
        },
        loadChart = function (url) {
            let ds = new am4core.DataSource();
            ds.url = url;
            ds.disableCache = true;
            ds.events.on("done", function (ev) {
                buildChart(ev.target.data);
            });
            ds.load();
        };

    let years = Laravel.years;
    if (years.length > 0) {
        let el = $('#year' + years[0]);
        let url = el.data('href');
        el.removeClass('btn-primary').addClass('btn-transcription-year');
        loadChart(url);
    }

    $('.btn-transcription').on('click', function () {
        $("#script-modal").modal("show");
        $(this).removeClass('btn-primary').addClass('btn-transcription-year');
        $(this).siblings().removeClass('btn-transcription-year').addClass('btn-primary');
        let url = $(this).data('href');
        loadChart(url);
    });
});