let chart;
am4core.ready(function () {


    $('#step-chart-modal').on('show.bs.modal', function (e) {

        let teams = $(e.relatedTarget).data('teams').split(',');
        let timezone = $(e.relatedTarget).data('timezone');
        let url = $(e.relatedTarget).data('href');
        let refresh = initialRefresh();

        am4core.useTheme(am4themes_animated);

        chart = am4core.create("chartdiv", am4charts.XYChart);
        chart.dataSource.url = url;
        chart.dataSource.reloadFrequency = refresh;
        chart.dataSource.incremental = true;
        chart.dataSource.adapter.add("url", function (url, target) {
            // "target" contains reference to the dataSource itself
            if (target.lastLoad) {
                chart.dataSource.reloadFrequency = 300000;
                url += "/" + target.lastLoad.getTime();
            }
            return url;
        });
        chart.dateFormatter.inputDateFormat = "yyyy-MM-dd HH:mm:ss";
        chart.hiddenState.properties.opacity = 0;
        chart.padding(0, 0, 0, 0);
        chart.zoomOutButton.disabled = true;

        chart.events.on("datavalidated", function () {
            dateAxis.zoom({start: 1 / 15, end: 1.2}, false, true);
        });

        chart.scrollbarX = new am4core.Scrollbar();

        let dateAxis = chart.xAxes.push(new am4charts.DateAxis());
        dateAxis.baseInterval = {
            "timeUnit": "minute",
            "count": 5
        };

        dateAxis.renderer.minGridDistance = 20;
        dateAxis.title.text = timezone;
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

        let valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
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

        $.each(teams, function (index, value) {
            let team = chart.series.push(new am4charts.LineSeries());
            team.dataFields.dateX = "date";
            team.dataFields.valueY = value;
            team.name = value;
            team.strokeWidth = 2;
            team.dataItems.template.locations.dateX = 0;
            team.interpolationDuration = 500;
            team.defaultState.transitionDuration = 0;
            team.tensionX = 0.8;

            let bullet = team.createChild(am4charts.CircleBullet);
            bullet.circle.radius = 5;
            bullet.fillOpacity = 1;
            bullet.fill = chart.colors.getIndex(1);
            bullet.isMeasured = false;

            team.events.on("validated", function () {
                if(team.dataItems.last == null) return;

                bullet.moveTo(team.dataItems.last.point);
                bullet.validatePosition();
            });
        });

        chart.legend = new am4charts.Legend();
        chart.legend.labels.template.text = "[bold]{name}";
        chart.legend.useDefaultMarker = true;
        let marker = chart.legend.markers.template.children.getIndex(0);
        marker.cornerRadius(12, 12, 12, 12);
        marker.strokeWidth = 2;
        marker.strokeOpacity = 1;
        marker.stroke = am4core.color("#ccc");

        // Set initial refresh rate so next 5 minutes loads correctly
        function initialRefresh() {
            let coeff = 1000 * 60 * 5;
            let date = new Date();
            let rounded = new Date(Math.ceil(date.getTime() / coeff) * coeff);

            return (rounded.getTime() - date.getTime());
        }

    }).on('hidden.bs.modal', function () {
        chart.dispose();
    });

}); // end am4core.ready()