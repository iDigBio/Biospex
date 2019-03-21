$(function () {
    /**
     * Use addInitHandler to do operations on the chart object
     * before it is drawn
     *
     **/
    let collections = [];

    AmCharts.addInitHandler(function (chart) {

        AmCharts.resizeCategory = function (chart) {
            let standardHeight = 400;
            let calculatedHeight = 100 * collections.length;
            let containerHeight = standardHeight > calculatedHeight ? standardHeight : calculatedHeight;

            chart.div.style.height = containerHeight + 'px';
        };

        // check for dataLoader
        let loader = chart.dataLoader;
        if (loader !== undefined && loader.url !== undefined) {
            if (loader.complete) {
                loader._complete = loader.complete;
            }
            loader.complete = function (chart) {
                // call original complete
                if (loader._complete) loader._complete.call(this, chart);

                // now let's do our thing
                AmCharts.resizeCategory(chart);
            };
        }
    }, ['serial']);


    if ($("#chartdiv").length > 0) {
        //let collections = [];

        let chart = AmCharts.makeChart("chartdiv", {
            type: "serial",
            titles: [{
                size: 15,
                text: "Cumulative Transcription Activity through Time"
            }],
            path: "/",
            pathToImages: "../images/",
            fontSize: 12,
            marginTop: 10,
            categoryField: 'day',
            categoryAxis: {
                gridAlpha: 0.07,
                axisColor: "#DADADA",
                startOnAxis: true,
                title: "Days elapsed"
            },
            resizeCategoryHeight: 4,
            chartCursor: {
                cursorAlpha: 1
            },
            responsive: {enabled: true},
            svgIcons: false,
            chartScrollbar: {
                color: "FFFFFF",
                dragIcon: "dragIconRoundSmall.png"
            },
            valueAxes: [{
                id: "a1",
                stackType: "regular",
                gridAlpha: 0.07,
                position: 'right',
                title: "Number of Transcriptions"
            }, {
                id: 'a2',
                gridAlpha: 0.07,
                stackType: "regular",
                position: "left",
                title: "Number of Transcriptions",
                includeHidden: true
            }],
            "dataLoader": {
                "url": "/project/" + $("#projectId").data('value') + "/chart",
                "format": "json",
                "showErrors": true,
                "postProcess": function (data, config, chart) {
                    let graphs = []
                        , hidden_graphs = []
                        , chartData = []
                        , current_day;
                    //prepare the data for consumption by amcharts
                    for (let i = 0; i < data.length; i++) {
                        let item = data[i]
                            , collection = item.collection
                            , count = item.count
                            , day = item.day
                            , obj;
                        if (collection !== "" && typeof(collection) !== 'undefined') {
                            if (collections.indexOf(collection) === -1 && collection !== "") collections.push(collection);
                            if (current_day !== day && typeof(day) != "undefined") {
                                if (obj) chartData.push(obj);
                                obj = {};
                                current_day = day;
                                obj["day"] = day;
                            }
                            if (typeof(count) != 'undefined') {
                                obj[collection] = count;
                            }
                            if (i + 1 === data.length) {
                                chartData.push(obj)
                            } //make sure we push last item if it's there
                        }
                    }
                    /////////////////////////////////////
                    //create a graph for each collection
                    /////////////////////////////////////
                    console.dir(collections);
                    for (let i = 0; i < collections.length; i++) {
                        let col = collections[i];
                        if (col !== "") {
                            graphs.push({
                                valueAxis: "a1",
                                type: "line",
                                title: col,
                                lineAlpha: 0,
                                valueField: col,
                                fillAlphas: 0.6,
                                balloonText: "[[title]] - [[value]] out of [[total]] total"
                            });
                            hidden_graphs.push({
                                valueAxis: "a2",
                                type: "line",
                                title: col,
                                hidden: false,
                                visibleInLegend: false,
                                categoryBalloonAlpha: 0,
                                //cursorAlpha: 0,
                                lineAlpha: 0,
                                lineColor: '',
                                valueField: col,
                                fillAlphas: 0,
                                balloonText: ""
                            })
                        }
                    }
                    chart.graphs = graphs.concat(hidden_graphs);
                    //////////////////////////////////////////
                    // if any of the data is missing a collection, set
                    // that collections count to zero
                    //////////////////////////////////////////
                    for (let i = 0; i < chartData.length; i++) {
                        let data = chartData[i];
                        collections.forEach(function (col) {
                            if (!data.hasOwnProperty(col)) {
                                data[col] = 0
                            }
                        })
                    }

                    return chartData;
                }
            },
            legend: {
                maxColumns: 1,
                position: "bottom",
                labelText: "[[title]]",
                valueText: "[[value]] transcriptions of [[total]] total in [[category]] day(s)",
                valueWidth: 100,
                valueAlign: "left",
                equalWidths: true,
                periodValueText: "Total: [[value.high]]"
            }
        });
    }

    if ($("#chartTranscriptionsDiv").length > 0) {
        let transcriptChart = AmCharts.makeChart("chartTranscriptionsDiv", {
            "type": "serial",
            "theme": "light",
            "marginRight": 70,
            "resizeCategoryHeight": 4,
            "titles": [{
                "size": 15,
                "text": "Transcriptions per Transcriber"
            }],
            "dataProvider": JSON.parse(Laravel.transcriptionChartData),
            "startDuration": 1,
            "graphs": [{
                "balloonText": "<b>[[category]]: [[value]]</b>",
                "fillColorsField": "color",
                "fillAlphas": 0.9,
                "lineAlpha": 0.2,
                "type": "column",
                "valueField": "users"
            }],
            "valueAxes": [{
                "title": "Number of Transcribers"
            }],
            "chartCursor": {
                "categoryBalloonEnabled": false,
                "cursorAlpha": 0,
                "zoomable": false
            },
            "categoryField": "count",
            "categoryAxis": {
                "gridThickness": 0,
                "gridPosition": "start",
                "labelRotation": 45,
                "title": "Number of Transcriptions"
            },
            "export": {
                "enabled": true
            }

        })
    }
});