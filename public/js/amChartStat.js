let stats = am4core.createFromConfig(
    {
        "xAxes": [{
            "type": "CategoryAxis",
            "title": {
                "text": "Digitizations"
            },
            "dataFields": {
                "category": "transcriptions"
            },
            "tooltip": {
                "background": {
                    "fill": "#07BEB8",
                    "strokeWidth": 0,
                    "cornerRadius": 3,
                    "pointerLength": 0
                },
                "dy": 5
            }
        }],
        "yAxes": [{
            "type": "ValueAxis",
            "title": {
                "text": "Number of Participants"
            },
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
                "strokeOpacity": 0.2,
                "strokeDasharray": ""
            },
            "lineY": {
                "disabled": true
            }
        },
        "scrollbarX": {
            "type": "Scrollbar"
        },
        "series": [{
            "type": "ColumnSeries",
            "dataFields": {
                "valueY": "transcribers",
                "categoryX": "transcriptions"
            },
            "tooltipHTML": "<span style='color:#000000;'>{valueY.value} Participants: {categoryX} Digitizations</span>",
            "tooltip": {
                "background": {
                    "fill": "#FFF",
                    "strokeWidth": 1
                },
                "getStrokeFromObject": true,
                "getFillFromObject": false
            },
            "fillOpacity": 0.8,
            "strokeWidth": 0,
            "stacked": true
        }],
        "data": JSON.parse(Laravel.transcriptions),
}, "statDiv", am4charts.XYChart);