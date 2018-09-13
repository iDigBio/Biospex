
/**
 * We"ll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');
    //require('bootstrap');
    require("bootstrap-sass/assets/javascripts/bootstrap");
    require("bootstrap-notify/bootstrap-notify");

    require("jquery-ui/ui/widgets/dialog");
    require("jquery-ui/ui/widgets/draggable");
    require("jquery-ui/ui/widgets/droppable");
    require("jquery-ui/ui/widgets/resizable");
    require("jquery-validation/dist/jquery.validate");
    require("jquery-validation/dist/additional-methods");
    require("jquery-datetimepicker/build/jquery.datetimepicker.full");
    require("free-jqgrid/dist/plugins/ui.multiselect");
    require("free-jqgrid/js/jquery.jqgrid.min");

    require("codemirror/lib/codemirror");
    require("summernote/dist/summernote");

    require("tablesorter/dist/js/jquery.tablesorter");
    require("tablesorter/dist/js/jquery.tablesorter.widgets.js");
    require("amcharts3/amcharts/amcharts");
    require("amcharts3/amcharts/serial");
    require("amcharts3/amcharts/plugins/dataloader/dataloader");
    require("amcharts3/amcharts/plugins/responsive/responsive");

    require("socket.io-client/dist/socket.io");
    require("./amchart");
    require("./bootbox");
    require("./flash");
    require("./biospex");
    require("./grid");

} catch (e) {}

/**
 * We"ll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require("axios");

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don"t have to attach every token manually.
 */

let token = document.head.querySelector("meta[name='csrf-token']");

if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
} else {
    console.error("CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token");
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */
import Echo from "laravel-echo"
window.io = require("socket.io-client");
window.Echo = new Echo({
    broadcaster: "socket.io",
    host: window.location.hostname + ":6001"
});
