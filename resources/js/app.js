/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

//import ConfettiGenerator from "confetti-js";

try {

    window.$ = window.jQuery = require("jquery");
    window.ConfettiGenerator = require("confetti-js/dist/index.min");
    window.Panzoom = require('@panzoom/panzoom/dist/panzoom.min');

    require('bootstrap');

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
    require("./jqgrid");
    require("./main");

} catch (e) {}
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */
import Echo from "laravel-echo"
window.io = require("socket.io-client");
window.Echo = new Echo({
    broadcaster: "socket.io",
    host: window.location.hostname,
    path: "/ws/socket.io",
});

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