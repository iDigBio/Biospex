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

$(function () {

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

    let imageSeriesTemplate = imageSeries.mapImages.template;
    let marker = imageSeriesTemplate.createChild(am4core.Image);
    marker.href = "https://s3-us-west-2.amazonaws.com/s.cdpn.io/t-160/marker.svg";
    marker.width = 20;
    marker.height = 20;
    marker.nonScaling = true;
    marker.tooltipText = "{city}";
    marker.horizontalCenter = "middle";
    marker.verticalCenter = "bottom";

    // Set property fields
    imageSeriesTemplate.propertyFields.latitude = "latitude";
    imageSeriesTemplate.propertyFields.longitude = "longitude";
    imageSeries.data = JSON.parse(Laravel.bingoUserData);

    Echo.channel(Laravel.channel)
        .listen('BingoEvent', (e) => {
            let data = JSON.parse(e.data);
            let winner = data.winner;
            let marker = data.marker;

            if (marker['uuid'] === Laravel.bingoUserUuid) {
                return;
            }

            // imageSeries.addData(item.value);
            imageSeries.addData(data.marker);

            if (winner === null) {
                return;
            }

            showWinnerModal($bingoRows, '<h3>We Have A Winner In ' + winner.city + '!!</h3>');
        });

    let $bingoRows = $('#bingo-rows');
    createRows($bingoRows);

    // Set winning combinations to array
    let winners = [['a1', 'a2', 'a3', 'a4', 'a5'], ['b1', 'b2', 'b3', 'b4', 'b5'], ['c1', 'c2', 'c3', 'c4', 'c5'], ['d1', 'd2', 'd3', 'd4', 'd5'], ['e1', 'e2', 'e3', 'e4', 'e5'], ['a1', 'b1', 'c1', 'd1', 'e1'], ['a2', 'b2', 'c2', 'd2', 'e2'], ['a3', 'b3', 'c3', 'd3', 'e3'], ['a4', 'b4', 'c4', 'd4', 'e4'], ['a5', 'b5', 'c5', 'd5', 'e5'], ['a1', 'b2', 'c3', 'd4', 'e5'], ['a5', 'b4', 'c3', 'd2', 'e1']];
    let possibleWinners = winners.length;

    // Initialize selected array with c3 freebie
    let selected = ['c3'];

    // Toggle clicked and not clicked
    $bingoRows.on('click', '.square', function () {
        $(this).toggleClass('clicked');

        // Push clicked object ID to 'selected' array
        selected.push($(this).attr('id'));

        // Compare winners array to selected array for matches
        for (let i = 0; i < possibleWinners; i++) {
            let cellExists = 0;

            for (let j = 0; j < 5; j++) {
                if ($.inArray(winners[i][j], selected) > -1) {
                    cellExists++;
                }
            }

            // If all 5 winner cells exist in selected array alert success message
            if (cellExists === 5) {
                $.get(Laravel.winnerUrl);
                showWinnerModal($bingoRows, '<h3>You are a winner!! Congratulations!!</h3>', true);
                selected = ['c3'];
            }
        }
    }).data('clicked', 0)
        .click(function () {
            let counter = $(this).data('clicked');
            $(this).data('clicked', counter++);
        });

    /*
    Echo.join(Laravel.channel)
        .here('here')
        .joining('joining')
        .leaving('leaving')
        .listen('BingoEvent', (e) => {
            let data = JSON.parse(e.data);
            let winner = data.winner;
            let marker = data.marker;

            console.log('Marker: ' + marker['uuid']);
            console.log('Winner: ' + winner);

            if (marker['uuid'] === Laravel.bingoUserUuid) {
                console.log('Marker is already on the map');
                return;
            }

            // imageSeries.addData(item.value);
            imageSeries.addData(data.marker);

            if (winner === null) {
                console.log('No winner');
                return;
            }

            showWinnerModal('<h3>We Have A Winner In ' + winner.city + '!!</h3>');
        });

     */
});

function showWinnerModal($bingoRows, msg, owner = false) {
    $('#bingo-modal').modal('show').on('shown.bs.modal', function () {
        $('#bingo-conffeti').collapse('show');
        $body = $(this).find('.modal-body');
        $body.html(msg);

        timeout = setTimeout(function () {
            $('#bingo-modal').modal('hide')
            $('#bingo-conffeti').collapse('hide');
            $body.html('');
        }, 10000);
    }).on('hidden.bs.modal', function () {
        createRows($bingoRows);
        $('#bingo-conffeti').collapse('hide');
    });
}

function createRows($bingoRows) {
    $bingoRows.html('');
    $.post(Laravel.rowsUrl)
        .done(function (data) {
            $bingoRows.html(data);
        })
        .fail(function () {
            $bingoRows.html('<p>Failed to load bingo rows.</p>');
        });
}