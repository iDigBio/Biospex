var chart = AmCharts.makeChart("chartdiv", {
    type: "serial",
    titles: [{
        size: 15,
        text: "Project Total Transcription Activity"
    }],
    path: "/amcharts",
    fontSize: 12,
    marginTop: 10,
    categoryField: 'hour',
    categoryAxis: {
        gridAlpha: 0.07,
        axisColor: "#DADADA",
        startOnAxis: true,
        title: "Hours elapsed"
    },
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
        "url": "/chart_data.json",
        "format": "json",
        "showErrors": true,
        "postProcess": function (data, config, chart) {
            var graphs = []
                , hidden_graphs = []
                , collections = []
                , chartData = []
                , current_hour;
            //prepare the data for consumption by amcharts
            for (var i = 0; i < data.length; i++) {
                var item = data[i]
                    , collection = item.col0
                    , count = item.col1
                    , hour = item.col2
                    , obj;
                if (collection != "" && typeof(collection) != 'undefined'){
                    if (collections.indexOf(collection) === -1 && collection != "") collections.push(collection);
                    if (current_hour != hour && typeof(hour) != "undefined") {
                        if (obj) chartData.push(obj);
                        obj = {};
                        current_hour = hour;
                        obj["hour"] = hour;
                    }
                    if (typeof(count) != 'undefined'){
                        obj[collection] = count;
                    }
                    if (i+1 == data.length) {
                        chartData.push(obj)
                    } //make sure we push last item if it's there
                }
            }
            /////////////////////////////////////
            //create a graph for each collection
            /////////////////////////////////////
            for (var i = 0; i < collections.length; i++) {
                var col = collections[i];
                if (col != "") {
                    graphs.push({
                        valueAxis: "a1",
                        type: "line",
                        title: col,
                        lineAlpha: 0,
                        valueField: col,
                        fillAlphas: 0.6,
                        balloonText: "[[title]] - [[value]] out of [[total]] total"
                    })
                    hidden_graphs.push({
                        valueAxis: "a2",
                        type: "line",
                        title: col,
                        hidden: false,
                        visibleInLegend: false,
                        categoryBalloonAlpha: 0,
//                                cursorAlpha: 0,
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
            for (var i = 0; i < chartData.length; i++) {
                var data = chartData[i];
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
        position: "bottom",
        labelText: "[[title]]",
        valueText: "[[value]] transcriptions of [[total]] total in [[category]] hour(s)",
        valueWidth: 100,
        valueAlign: "left",
        equalWidths: true,
        periodValueText: "Total: [[value.high]]"
    }
});
