// Create map instance
let chart = am4core.create("bingodiv", am4maps.MapChart);

// Set map definition
chart.geodata = am4geodata_worldLow;

// Set projection
chart.projection = new am4maps.projections.Miller();

// Create map polygon series
let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());

// Make map load polygon (like country names) data from GeoJSON
polygonSeries.useGeodata = true;

// Configure series
let polygonTemplate = polygonSeries.mapPolygons.template;
polygonTemplate.tooltipText = "{name}";
polygonTemplate.fill = am4core.color("#74B266");

// Create hover state and set alternative fill color
let hs = polygonTemplate.states.create("hover");
hs.properties.fill = am4core.color("#367B25");

// Remove Antarctica
polygonSeries.exclude = ["AQ"];

// Create image series
let imageSeries = chart.series.push(new am4maps.MapImageSeries());

// Create a circle image in image series template so it gets replicated to all new images
let imageSeriesTemplate = imageSeries.mapImages.template;
let circle = imageSeriesTemplate.createChild(am4core.Circle);
circle.radius = 4;
circle.fill = am4core.color("#B27799");
circle.stroke = am4core.color("#FFFFFF");
circle.strokeWidth = 2;
circle.nonScaling = true;
circle.tooltipText = "{city}";

// Set property fields
imageSeriesTemplate.propertyFields.latitude = "latitude";
imageSeriesTemplate.propertyFields.longitude = "longitude";

// Add data for the three cities
/*
imageSeries.data.push({
    "latitude": 48.856614,
    "longitude": 2.352222,
    "city": "Paris"
}, {
    "latitude": 40.712775,
    "longitude": -74.005973,
    "city": "New York"
}, {
    "latitude": 49.282729,
    "longitude": -123.120738,
    "city": "Vancouver"
}, {
    "latitude": 35.282729,
    "longitude": -123.120738,
    "city": "Test"
});
*/

Echo.channel(Laravel.channel)
    .listen('BingoEvent', (e) => {
        console.log(e)
        imageSeries.data.push(e.data);
    });

function buildCountryMap() {
/*
    am4core.useTheme(am4themes_animated);

    let map = am4core.create("mapDiv", am4maps.MapChart);
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
    let heatLegend = am4core.create("mapLegendDiv", am4maps.HeatLegend);
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
        let statevar = 'am4geodata_region_usa_'+stateabbr.toLowerCase()+'Low';
        map.dispose();
        heatLegend.dispose();
        $('#mapDiv').html('<div class="loader mx-auto align-self-center"></div>');
        $.getScript( '//www.amcharts.com/lib/4/geodata/region/usa/' + stateabbr.toLowerCase() + 'Low.js' )
            .done(function( script, textStatus ) {
                buildCountyMap(stateabbr,statenum, statevar);
            })
            .fail(function( jqxhr, settings, exception ) {
                $('#mapDiv').html( "Error getting State map and counties." );
            });
    });


    polygonSeries.mapPolygons.template.strokeOpacity = 0.4;
    polygonSeries.mapPolygons.template.events.on("out", function(){
        heatLegend.valueAxis.hideTooltip();
    });

    map.zoomControl = new am4maps.ZoomControl();
    map.zoomControl.valign = "top";

    polygonSeries.data = JSON.parse(Laravel.states);
}

function buildCountyMap(stateabbr,statenum, statevar) {

    let request = $.get($('#projectUrl').data('href') + '/' + statenum);

    request.done(function(data) {
        am4core.useTheme(am4themes_animated);

        let stateMap = am4core.create("mapDiv", am4maps.MapChart);
        stateMap.hiddenState.properties.opacity = 0; // this creates initial fade-in

        stateMap.geodata = window[statevar];
        stateMap.projection = new am4maps.projections.Miller();

        stateMap.events.on('hit', event => {
            stateMap.dispose();
            heatLegend.dispose();
            buildCountryMap();
        });

        let polygonSeries = stateMap.series.push(new am4maps.MapPolygonSeries());
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

        let heatLegend = am4core.create("mapLegendDiv", am4maps.HeatLegend);
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
        polygonSeries.mapPolygons.template.events.on("out", function() {
            heatLegend.valueAxis.hideTooltip();
        });

        stateMap.zoomControl = new am4maps.ZoomControl();
        stateMap.zoomControl.valign = "top";

        polygonSeries.data = JSON.parse(data.counties);
    });

    request.fail(function(jqXHR, textStatus, errorThrown) {
        alert(errorThrown);
        stateMap.dispose();
        heatLegend.dispose();
        buildCountryMap();
    });

 */
}