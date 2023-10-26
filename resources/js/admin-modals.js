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
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

$(function () {
    let $body = $('body')
    $(document).on('shown.bs.modal', function () {
        if ($body.hasClass('modal-open') === false) {
            $body.addClass('modal-open')
        }
    })

    $('#global-modal').on('show.bs.modal', function (e) {
        let $target = $(e.relatedTarget)
        let $modalBody = $(this).find('.modal-body')
        let size = $target.data('size')

        $(this).data('size', size)
        $(this).find('div.modal-dialog').addClass(size)
        $('#modal-title').html($target.data('title'))

        $modalBody.html('<div class="loader mx-auto"></div>')
        $modalBody.load($target.data("url"), function (){
            makeSelect($('.controls'));
            toggleGeoLocateCommunityForm()
        });
    }).on('hidden.bs.modal', function () {
        let size = $(this).data('size');
        $(this).find('div.modal-dialog').removeClass(size).data('size', '')
        $(this).find('.modal-body').html('')
        $('#modal-title').html('')
    })

    // GeoLocate Community and datasource form.
    $('.modal-body').on('change', '#community-form-select', function (){
        toggleGeoLocateCommunityForm()
    }).on('submit', '.modal-form', function (e) { // used in workflow id and geolocate community modal forms.
        e.preventDefault() // avoid to execute the actual submit of the form.
        formPost($(this).attr('action'), $(this).serialize())
        $('#global-modal').modal('toggle');
    }) // Geolocate Export form
    .on('change', '#geolocate-form-select', function (){
        if ($(this).val() === '') {
            $('#name').val('');
            return;
        }

        let $ajaxResults = $('#geolocate-results')
        $ajaxResults.html('<div class="mt-5 loader mx-auto"></div>')
        $.post($(this).data('url'), {formId: $('#geolocate-form-select').val(), source: $("input:radio.source:checked").val()}, function (data){
            $ajaxResults.html(data).find("div.entry").each(function () {
                makeSelect($(this))
            })
            renumber_geolocate()
        }).fail(function (response){
            let json = JSON.parse(response.responseText)
            $('#global-modal').modal('toggle');
            notify("exclamation-circle", json.message, "warning")
        })
    }).on('click', '.geolocate-btn-add', function () {
        let $entry = $('.default').clone().appendTo($('#controls'))
        $entry.find('.geolocate-field-default').removeClass('geolocate-field-default').addClass('geolocate-field').prop('required', true)
        $entry.find('.header-select-default').removeClass('header-select-default').addClass('header-select').prop('required', true)
        $entry.removeClass('default').addClass('entry').show()

        makeSelect($entry)
        renumber_geolocate()
    }).on('click', '.geolocate-btn-remove', function () {
        if ($('#controls').children('div.entry').length === 1) {
            return
        }
        $('#controls div.entry:last').remove()
        renumber_geolocate()
    }).on('click', '#process', function () {
        //$('form#geolocate-form').attr('action', $(this).data('url')).trigger('submit')
        formPost($(this).data('url'), $(this).serialize())
        $('#global-modal').modal('toggle');
    }).on('submit', 'form#geolocate-form', function (e) {
        e.preventDefault() // avoid to execute the actual submit of the form.
        let $modal = $('#global-modal')
        if (checkDuplicates()) {
            notify("exclamation-circle", "GeoLocate Export field cannot contain duplicate values.", "warning")
            $modal.modal('toggle');
            return;
        }

        let fields = checkRequiredValues()
        if (fields.length > 0) {
            notify("exclamation-circle", fields.toString() + ' GeoLocate Export fields are required.', "warning")
            $modal.modal('toggle');
            return;
        }

        formPost($(this).attr('action'), $(this).serialize())
        $modal.modal('toggle');
    })

})

// Make select box rows sortable and bootstrap-select
makeSelect = function ($entry) {
    $entry.find('select').each(function () {
        $(this).selectpicker();
    }).disableSelection();
}

// Renumber prefixes when rows add and removed.
renumber_geolocate = function () {
    let $entries = $('#controls > div.entry');
    $entries.each(function (index) {
        $(this).find('select').each(function () {
            $(this).attr('name', $(this).attr('name').replace(/\[[0-9]+\]/g, '[' + index + ']'));
        });
    })
    $("#entries").val($entries.length);
}

formPost = function (url, data) {
    $.post(url, {data: data}, function (data) {
        notify("exclamation-circle", data.message, "success")
    }, "json").fail(function (response) {
        let json = JSON.parse(response.responseText)
        notify("exclamation-circle", json.message, "warning")
    })
}

toggleGeoLocateCommunityForm = function() {
    let val = $('#community-form-select').val()
    if (val === '') {
        $('#community-row').collapse('show')
        $('#community').attr("required", true)
        $('#community-label').attr("required", true)
    } else {
        $('#community-row').collapse('hide')
        $('#community').removeAttr("required")
        $('#community-label').removeAttr("required")
    }
}

// Check duplicate export field selection before submitting form.
checkDuplicates = function () {
    let dup = false;
    let fieldOptions = [];
    $('select.geolocate-field').each(function () {
        if ($.inArray($(this).val(), fieldOptions) > -1) {
            dup = true;
        }

        fieldOptions.push($(this).val());
    });

    return dup;
}

checkRequiredValues = function () {
    let list = ["County", "Country", "Locality", "ScientificName", "StateProvince"];
    $('select.geolocate-field').each(function () {
        if ($.inArray($(this).val(), list) > -1) {
            list.splice($.inArray($(this).val(), list), 1);
        }
    });

    return list;
}