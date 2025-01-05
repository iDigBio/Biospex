require("./app.js");

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

try {

    window.$ = window.jQuery = require('jquery');
    window.Panzoom = require('@panzoom/panzoom/dist/panzoom.min');
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

} catch (e) {
    console.log(e);
}
