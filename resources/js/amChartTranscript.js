let legendContainer = am4core.createFromConfig({
    "width": "100%",
    "height": "100%"
}, "transcriptLegendDiv", am4core.Container);

let transcripts = am4core.createFromConfig(
    {
        "xAxes": [{
            "type": "DateAxis",
            "renderer": {
                "minGridDistance": 50
            },
            "startLocation": 0.5,
            "endLocation": 0.5,
            "baseInterval": {
                "timeUnit": "day",
                "count": 1
            },
            "tooltip": {
                "background": {
                    "fill": "#07BEB8",
                    "strokeWidth": 0
                },
                "dy": 5
            }
        }],
        "yAxes": [{
            "type": "ValueAxis",
            "tooltip": {
                "disabled": true
            },
            "calculateTotals": true
        }],
        "cursor": {
            "type": "XYCursor",
            "lineX": {
                "stroke": "#8F3985",
                "strokeWidth": 4,
                "strokeOpacity": 1,
                "strokeDasharray": ""
            },
            "lineY": {
                "disabled": true
            }
        },
        "scrollbarX": {
            "type": "Scrollbar"
        },
        "legend": {
            "parent": legendContainer
        },
        "dateFormatter": {
            "inputDateFormat": "yyyy-MM-dd"
        },
        "series": JSON.parse(Laravel.series),
        "data": JSON.parse(Laravel.data),
        "preloader": { "disabled" : true },
        "events": {
            "ready": function (e) {
                console.log('hide');
                $("#script-modal").modal("hide");
            }
        }
    }, "transcriptDiv", am4charts.XYChart);