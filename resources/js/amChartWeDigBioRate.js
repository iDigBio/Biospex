let weDigBioRateChart = null;
let interval = null;
am4core.ready(function () {

    $('#wedigbio-rate-modal').on('show.bs.modal', function (e) {
        let $div = $(this).find('#weDigBioRateChartDiv');
        let projectsUrl = $(e.relatedTarget).data('projects');
        let url = $(e.relatedTarget).data('href');

        let createChart = function () {
            am4core.disposeAllCharts();
            $.get(projectsUrl).fail(function () {
                $div.html('<p class="d-flex justify-content-center">Failed to load projects</p>');
            }).done(function (projects) {
                if (!projects) {
                    $div.html('<p class="d-flex justify-content-center">No current WeDigBio Event</p>');
                    return;
                }
                buildChart(url, projects)
            });
        }
        createChart();
        setInterval(createChart, 300000);
    }).on('hidden.bs.modal', function () {
        am4core.disposeAllCharts();
        clearInterval(interval);
    });

}); // end am4core.ready()


function buildChart(url, projects) {
    weDigBioRateChart = am4core.create("weDigBioRateChartDiv", am4charts.XYChart);
    am4core.useTheme(am4themes_animated);
    weDigBioRateChart.dataSource.url = url;
    weDigBioRateChart.dateFormatter.inputDateFormat = "yyyy-MM-dd HH:mm:ss";
    weDigBioRateChart.hiddenState.properties.opacity = 0;
    weDigBioRateChart.padding(0, 0, 0, 0);
    weDigBioRateChart.zoomOutButton.disabled = true;

    let cellSize = .3;
    weDigBioRateChart.events.on("datavalidated", function (ev) {
        dateAxis.zoom({start: 1 / 15, end: 1.2}, false, true);
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

    weDigBioRateChart.scrollbarX = new am4core.Scrollbar();

    let dateAxis = weDigBioRateChart.xAxes.push(new am4charts.DateAxis());

    dateAxis.renderer.minGridDistance = 20;
    dateAxis.title.text = 'UTC Timezone';
    dateAxis.title.fontSize = 20;
    dateAxis.title.align = 'center';
    dateAxis.title.fontWeight = 600;
    dateAxis.dateFormats.setKey("minute", "hh:mm");
    dateAxis.periodChangeDateFormats.setKey("minute", "[bold]h:mm a");
    dateAxis.periodChangeDateFormats.setKey("hour", "[bold]h:mm a");
    dateAxis.renderer.axisFills.template.disabled = true;
    dateAxis.renderer.ticks.template.disabled = true;

    // this makes date axis labels which are at equal minutes to be rotated
    dateAxis.renderer.labels.template.adapter.add("rotation", function (rotation, target) {
        target.verticalCenter = "middle";
        target.horizontalCenter = "left";
        return -90;
    });

    let valueAxis = weDigBioRateChart.yAxes.push(new am4charts.ValueAxis());
    //valueAxis.min = -0.1;
    valueAxis.title.text = "Estimated Records Per Hour";
    valueAxis.title.fontSize = 20;
    valueAxis.tooltip.disabled = true;
    valueAxis.renderer.minLabelPosition = 0.05;
    valueAxis.renderer.maxLabelPosition = 0.95;
    valueAxis.renderer.axisFills.template.disabled = true;
    valueAxis.renderer.ticks.template.disabled = true;

    $.each(projects, function (index, value) {
        let project = weDigBioRateChart.series.push(new am4charts.LineSeries());
        project.dataFields.dateX = "date";
        project.dataFields.valueY = value;
        project.name = value;
        project.strokeWidth = 2;
        project.dataItems.template.locations.dateX = 0;
        project.interpolationDuration = 500;
        project.defaultState.transitionDuration = 0;
        project.tensionX = 0.8;

        let bullet = project.createChild(am4charts.CircleBullet);
        bullet.circle.radius = 5;
        bullet.fillOpacity = 1;
        bullet.fill = weDigBioRateChart.colors.getIndex(1);
        bullet.isMeasured = false;

        project.events.on("validated", function () {
            if (project.dataItems.last == null) return;

            bullet.moveTo(project.dataItems.last.point);
            bullet.validatePosition();
        });
    });

    weDigBioRateChart.legend = new am4charts.Legend();
    weDigBioRateChart.legend.labels.template.text = "[bold]{name}";
    weDigBioRateChart.legend.useDefaultMarker = true;
    let marker = weDigBioRateChart.legend.markers.template.children.getIndex(0);
    marker.cornerRadius(12, 12, 12, 12);
    marker.strokeWidth = 2;
    marker.strokeOpacity = 1;
}
