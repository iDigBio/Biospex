let weDigBioRateChart;
am4core.ready(function () {

    $('#wedigbio-rate-modal').on('show.bs.modal', function (e) {
        getProjects().fail(function (error) {
            console.log(error);
        }).done(function (projects) {
            console.log(projects);
            let url = $(e.relatedTarget).data('href');
            console.log(url);

            let refresh = initialRefresh();

            am4core.useTheme(am4themes_animated);

            weDigBioRateChart = am4core.create("weDigBioRateChartDiv", am4charts.XYChart);
            weDigBioRateChart.dataSource.url = url;
            weDigBioRateChart.dataSource.reloadFrequency = refresh;
            weDigBioRateChart.dataSource.incremental = true;
            weDigBioRateChart.dataSource.adapter.add("url", function (url, target) {
                // "target" contains reference to the dataSource itself
                if (target.lastLoad) {
                    weDigBioRateChart.dataSource.reloadFrequency = 300000;
                    url += "/" + target.lastLoad.getTime();
                }
                return url;
            });
            weDigBioRateChart.dateFormatter.inputDateFormat = "yyyy-MM-dd HH:mm:ss";
            weDigBioRateChart.hiddenState.properties.opacity = 0;
            weDigBioRateChart.padding(0, 0, 0, 0);
            weDigBioRateChart.zoomOutButton.disabled = true;

            weDigBioRateChart.events.on("datavalidated", function () {
                dateAxis.zoom({start: 1 / 15, end: 1.2}, false, true);
            });

            weDigBioRateChart.scrollbarX = new am4core.Scrollbar();

            let dateAxis = weDigBioRateChart.xAxes.push(new am4charts.DateAxis());
            dateAxis.baseInterval = {
                "timeUnit": "minute",
                "count": 5
            };

            dateAxis.renderer.minGridDistance = 20;
            dateAxis.title.text = 'UTC';
            dateAxis.title.fontSize = 20;
            dateAxis.dateFormats.setKey("minute", "hh:mm");
            dateAxis.periodChangeDateFormats.setKey("minute", "[bold]h:mm a");
            dateAxis.periodChangeDateFormats.setKey("hour", "[bold]h:mm a");
            dateAxis.renderer.axisFills.template.disabled = true;
            dateAxis.renderer.ticks.template.disabled = true;
            dateAxis.interpolationDuration = 500;
            dateAxis.rangeChangeDuration = 500;

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
            valueAxis.interpolationDuration = 500;
            valueAxis.rangeChangeDuration = 500;
            valueAxis.renderer.minLabelPosition = 0.05;
            valueAxis.renderer.maxLabelPosition = 0.95;
            valueAxis.renderer.axisFills.template.disabled = true;
            valueAxis.renderer.ticks.template.disabled = true;

            $.each(projects, function (index, value) {
                console.log(value);
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
            marker.stroke = am4core.color("#ccc");
        });
    }).on('hidden.bs.modal', function () {
        weDigBioRateChart.dispose();
    });

}); // end am4core.ready()

function getProjects() {
    return $.get('ajax/wedigbio-projects');
}

// Set initial refresh rate so next 5 minutes loads correctly
function initialRefresh() {
    let coeff = 1000 * 60 * 5;
    let date = new Date();
    let rounded = new Date(Math.ceil(date.getTime() / coeff) * coeff);

    return (rounded.getTime() - date.getTime());
}