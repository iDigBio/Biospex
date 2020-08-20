/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

$(function () {
    let $panzoom = $(".panzoom").panzoom();
    $panzoom.parent().on("mousewheel.focal", function (e) {
        e.preventDefault();
        let delta = e.delta || e.originalEvent.wheelDelta;
        let zoomOut = delta ? delta < 0 : e.originalEvent.deltaY > 0;
        $panzoom.panzoom("zoom", zoomOut, {
            animate: false,
            focal: e
        });
    });

    let options = {
        beforeSubmit: function () {
            $("#output").removeClass().html("<div class='loader mx-auto'></div>");
        },
        success: function (response) {
            let css = response.result === "false" ? "alert-danger" : "alert-success";
            $("#output").addClass(css).html(response.message);
        }
    };

    $("#frmReconcile").ajaxForm(options);
    $("form input:radio").on("change", function () {
        let id = $(this).data("column");
        $("[id='"+id+"']").text($(this).val());
    });

    $("img.lazy").one("load", function () {
        $(".loader").remove();
    }).each(function () {
        if (this.complete) {
            $(this).trigger("load");
        }
    });
});