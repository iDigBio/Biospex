webpackJsonp([1],[
/* 0 */,
/* 1 */,
/* 2 */,
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(4);
module.exports = __webpack_require__(7);


/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    __webpack_require__(0);
    //    require('jquery-ui-dist/jquery-ui');
    __webpack_require__(1);
    //    require('bootstrap-wysiwyg/js/bootstrap-wysiwyg.min');
    //    require('x-editable/dist/bootstrap3-editable/js/bootstrap-editable');
    //    require('x-editable/dist/inputs-ext/wysihtml5/wysihtml5');
    //    require('jquery-ujs/src/rails');
    //    require('icheck/icheck');
    __webpack_require__(2);
    //    require('bootstrap-confirmation2/bootstrap-confirmation');
    //    require('./delete-form');
    __webpack_require__(5);
    __webpack_require__(6);
} catch (e) {}

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {if (Laravel.flashMessage.length) {
    $.notify({
        icon: 'glyphicon glyphicon-' + Laravel.flashIcon,
        message: Laravel.flashMessage
    }, {
        type: Laravel.flashType,
        placement: {
            from: "top",
            align: "center"
        },
        offset: 50,
        spacing: 10,
        animate: {
            enter: 'animated fadeInDown',
            exit: 'animated fadeOutUp'
        }
    });
}
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(0)))

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {$(function () {
    alert('test');
    $('.sidebar-menu li a[href="' + location.href + '"]').addClass('active').closest('li').addClass('active').closest("ul").css('display', 'block').closest('li').addClass('active');

    $('.textarea').wysihtml5();

    $(".source li").draggable({
        addClasses: false,
        appendTo: "body",
        helper: "clone"
    });

    $(".target").droppable({
        addClasses: false,
        activeClass: "listActive",
        accept: ":not(.ui-sortable-helper)",
        drop: function drop(event, ui) {
            $(this).find(".placeholder").remove();
            var link = $("<a href='#' class='dismiss'>x</a>");
            var id = ui.draggable.attr('id');
            var list = $('<li id="' + id + '"></li>').text(ui.draggable.text());
            $(list).append(link);
            $(list).appendTo(this);
        }
    }).sortable({
        items: "li:not(.placeholder)",
        sort: function sort() {
            $(this).removeClass("listActive");
        }
    }).on("click", ".dismiss", function (event) {
        event.preventDefault();
        $(this).parent().remove();
    });

    $('#workflow').submit(function () {
        if ($('ul.target').children().length < 1) {
            $('#workflow').append($('<input>').attr({ 'type': 'hidden', 'name': 'actors[0][id]' }).val(''));
        } else {
            $('ul.target').children().each(function (i) {
                var id = $(this).attr('id');
                $('#workflow').append($('<input>').attr({ 'type': 'hidden', 'name': 'actors[' + i + '][id]' }).val(id));
            });
        }
    });

    //iCheck for checkbox and radio inputs
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_minimal-blue'
    });

    $('.edit-in-place').editable({
        error: function error(response, newValue) {
            if (response.status === 500) {
                return 'Service unavailable. Please try later.';
            } else {
                var obj = $.parseJSON(response.responseText); //  response.responseText;
                return obj.name;
            }
        }
    });

    $('.editable').editable().on('hidden', function (e, reason) {
        var locale = $(this).data('locale');
        if (reason === 'save') {
            $(this).removeClass('status-0').addClass('status-1');
        }
    });

    $('.group-select').on('change', function () {
        alert('test');
        var group = $(this).val();
        var url = $(this).find(':selected').data('route');
        if (group) {
            window.location.href = url + '/' + $(this).val();
        } else {
            window.location.href = url;
        }
    });

    $("a.delete-key").click(function (event) {
        event.preventDefault();
        var row = $(this).closest('tr');
        var url = $(this).attr('href');
        var id = row.attr('id');
        $.post(url, { id: id }, function () {
            row.remove();
        });
    });

    $('.form-import').on('ajax:success', function (e, data) {
        $('div.success-import strong.counter').text(data.counter);
        $('div.success-import').slideDown();
    });

    $('.form-find').on('ajax:success', function (e, data) {
        $('div.success-find strong.counter').text(data.counter);
        $('div.success-find').slideDown();
    });

    $('.form-publish').on('ajax:success', function (e, data) {
        $('div.success-publish').slideDown();
    });

    //Helper function to keep table row from collapsing when being sorted
    var fixHelperModified = function fixHelperModified(e, tr) {
        var $originals = tr.children();
        var $helper = tr.clone();
        $helper.children().each(function (index) {
            $(this).width($originals.eq(index).width());
        });
        return $helper;
    };

    //Make diagnosis table sortable
    $("#resources tbody").sortable({
        helper: fixHelperModified,
        stop: function stop(event, ui) {
            renumber_table('#resources');
        }
    }).disableSelection();

    $("#ocrCheckAll").change(function () {
        $("input:checkbox").prop('checked', $(this).prop("checked"));
    });

    $(".users-ajax").select2({
        tags: true,
        ajax: {
            url: "/admin/users/search",
            dataType: 'json',
            delay: 250,
            data: function data(params) {
                return {
                    q: params.term
                };
            },
            processResults: function processResults(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                return {
                    results: data.results,
                    pagination: data.pagination
                };
            },
            cache: true
        },
        escapeMarkup: function escapeMarkup(markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 1
        //templateResult: formatRepo, // omitted for brevity, see the source of this page
        //templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
    });

    $(".add-more").click(function () {
        var html = $(".copy").html();
        $(".after-add-more").after(html);
    });

    $("body").on("click", ".remove", function () {
        $(this).parents(".control-group").remove();
    });
});

//Renumber table rows
function renumber_table(tableID) {
    $(tableID + " tr").each(function () {
        id = this.id;
        count = $(this).parent().children().index($(this)) + 1;
        $(this).find('.order').html(count);
        if (id != '') {
            $.post('resources/' + id + '/order/' + count, { id: id, order: count }, function () {
                $(this).prop('id', count);
            });
        }
    });
}
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(0)))

/***/ }),
/* 7 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ })
],[3]);