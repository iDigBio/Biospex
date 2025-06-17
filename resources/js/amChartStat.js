/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

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