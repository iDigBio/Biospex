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

import "./app.js";

try {

    window.$ = window.jQuery = require('jquery');
    window.ClipboardJS = require('clipboard');

    require('bootstrap');

    require("bootstrap-select/dist/js/bootstrap-select.min");
    require("bootstrap-notify/bootstrap-notify");

    require("jquery-ui/ui/widgets/dialog");
    require("jquery-ui/ui/widgets/draggable");
    require("jquery-ui/ui/widgets/droppable");
    require("jquery-ui/ui/widgets/resizable");
    require("jquery-datetimepicker/build/jquery.datetimepicker.full");

    require("./ui.multiselect");
    require("free-jqgrid/js/jquery.jqgrid.min");
    require("summernote/dist/summernote-bs4.min");

    window.bootbox = require("./bootbox");
    require("./common");
    require("./jqgrid");
    require("./admin");
    require("./admin-modals");
    require("./livewire/geolocate-field-manager");

} catch (e) {
    console.log(e);
}
