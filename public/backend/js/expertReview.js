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
    const elem = document.getElementById('panzoom');
    const panzoom = Panzoom(elem);
    const parent = elem.parentElement
    // No function bind needed
    parent.addEventListener('wheel', panzoom.zoomWithWheel)

    $('#btnZoomIn').on('click', panzoom.zoomIn);
    $('#btnZoomOut').on('click', panzoom.zoomOut);
    $('#btnZoomReset').on('click', panzoom.reset);

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

    $('#pagination').on('change', function() {
        window.location.replace($(this).val());
    });
});