$(function () {
    let map = null, heatLegend = null;
    am4core.options.autoDispose = true;

    let countyMap = function (stateabbr, statenum, statevar) {
        let request = $.get($('#projectUrl').data('href') + '/' + statenum);

        request.done(function (data) {
            am4core.useTheme(am4themes_animated);

            map = am4core.create("mapDiv", am4maps.MapChart);
            map.hiddenState.properties.opacity = 0; // this creates initial fade-in

            map.geodata = window[statevar];
            map.projection = new am4maps.projections.Miller();

            map.events.on('hit', event => {
                countryMap();
            });

            let polygonSeries = map.series.push(new am4maps.MapPolygonSeries());
            let polygonTemplate = polygonSeries.mapPolygons.template;
            polygonTemplate.tooltipText = "{name}: {value.value.formatNumber('#')}";
            polygonSeries.heatRules.push({
                property: "fill",
                target: polygonSeries.mapPolygons.template,
                //min: am4core.color("#00abff"),
                min: am4core.color("#a7abab"),
                max: am4core.color("#aa0002"),
                minValue: 0,
                maxValue: data.max
            });
            polygonSeries.useGeodata = true;

            heatLegend = am4core.create("mapLegendDiv", am4maps.HeatLegend);
            heatLegend.width = am4core.percent(100);
            heatLegend.series = polygonSeries;
            heatLegend.orientation = "horizontal";
            heatLegend.padding(20, 20, 20, 20);
            heatLegend.valueAxis.renderer.labels.template.fontSize = 10;
            heatLegend.valueAxis.renderer.minGridDistance = 40;
            heatLegend.markerCount = 6;
            heatLegend.maxValue = data.max;

            polygonSeries.mapPolygons.template.events.on("over", event => {
                heatLegend.valueAxis.showTooltipAt(event.target.dataItem.value);
            });

            polygonSeries.mapPolygons.template.strokeOpacity = 0.4;
            polygonSeries.mapPolygons.template.events.on("out", function () {
                heatLegend.valueAxis.hideTooltip();
            });

            map.zoomControl = new am4maps.ZoomControl();
            map.zoomControl.valign = "top";

            polygonSeries.data = JSON.parse(data.counties);
        });

        request.fail(function (jqXHR, textStatus, errorThrown) {
            alert(errorThrown);
            countryMap();
        });
    };

    let countryMap = function () {
        am4core.useTheme(am4themes_animated);

        map = am4core.create("mapDiv", am4maps.MapChart);
        map.hiddenState.properties.opacity = 0; // this creates initial fade-in
        map.geodata = am4geodata_usaLow;
        map.projection = new am4maps.projections.AlbersUsa();

        let polygonSeries = map.series.push(new am4maps.MapPolygonSeries());
        let polygonTemplate = polygonSeries.mapPolygons.template;
        polygonTemplate.tooltipText = "{name}: {value.value.formatNumber('#')}";
        polygonSeries.heatRules.push({
            property: "fill",
            target: polygonSeries.mapPolygons.template,
            min: am4core.color("#a7abab"),
            max: am4core.color("#aa0002"),
            minValue: 0,
            maxValue: Laravel.max
        });
        polygonSeries.useGeodata = true;

        // add heat legend
        heatLegend = am4core.create("mapLegendDiv", am4maps.HeatLegend);
        heatLegend.width = am4core.percent(100);
        heatLegend.series = polygonSeries;
        heatLegend.orientation = "horizontal";
        heatLegend.padding(20, 20, 20, 20);
        heatLegend.valueAxis.renderer.labels.template.fontSize = 10;
        heatLegend.valueAxis.renderer.minGridDistance = 40;
        heatLegend.markerCount = 6;

        polygonSeries.mapPolygons.template.events.on("over", event => {
            heatLegend.valueAxis.showTooltipAt(event.target.dataItem.value);
        });

        polygonSeries.mapPolygons.template.events.on("hit", event => {
            let stateabbr = event.target.dataItem.dataContext.name;
            let statenum = event.target.dataItem.dataContext.statenum;
            let statevar = 'am4geodata_region_usa_' + stateabbr.toLowerCase() + 'Low';
            $('#mapDiv').html('<div class="loader mx-auto align-self-center"></div>');
            $.getScript('//www.amcharts.com/lib/4/geodata/region/usa/' + stateabbr.toLowerCase() + 'Low.js')
                .done(function (script, textStatus) {
                    countyMap(stateabbr, statenum, statevar);
                })
                .fail(function (jqxhr, settings, exception) {
                    $('#mapDiv').html("Error getting State map and counties.");
                });
        });


        polygonSeries.mapPolygons.template.strokeOpacity = 0.4;
        polygonSeries.mapPolygons.template.events.on("out", function () {
            heatLegend.valueAxis.hideTooltip();
        });

        map.zoomControl = new am4maps.ZoomControl();
        map.zoomControl.valign = "top";

        polygonSeries.data = JSON.parse(Laravel.states);
    };

    countryMap();
});
