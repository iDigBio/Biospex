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

    let clipboard = new ClipboardJS('.clipboard');

    let userGroup = $('#user-group')
    let groupInput = $('#group-input')
    userGroup.change(function () {
        this.value === 'new' ? groupInput.show() : groupInput.hide()
    })
    if (userGroup.length) {
        userGroup.val() === 'new' ? groupInput.show() : groupInput.hide()
    }

    let homeProjectList = $('a.home-project-list')
    homeProjectList.click(function (e) {
        let count = $(this).data('count')
        $.get($(this).attr('href') + '/' + count, function (data) {
            $('.recent-projects-pane').html(data)
            homeProjectList.data('count', count + 5)
        })
        e.preventDefault()
    })

    let textarea = $('.textarea')
    if (textarea.length) {
        textarea.summernote({
            height: 200,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ]
        })
    }

    $.datetimepicker.setLocale('en')
    $('.date-time-picker').datetimepicker({
        format: 'Y-m-d H:i',
        allowTimes: [
            '00:00', '00:30', '01:00', '01:30', '02:00', '02:30', '03:00', '03:30',
            '04:00', '04:30', '05:00', '05:30', '06:00', '06:30', '07:00', '07:30',
            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
            '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
            '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30',
            '20:00', '20:30', '21:00', '21:30', '22:00', '22:30'
        ]
    })

    // Commented out: jQuery-based dynamic field management replaced with Livewire ProjectResourceManager component
    // $(document).on('click', '.btn-add', function (e) {
    //     e.preventDefault()
    //
    //     let controls = $('.controls'),
    //         currentEntry = $(this).parents('.entry:first'),
    //         newEntry = $(currentEntry.clone(false, false)).appendTo(controls) // Shallow clone to avoid Livewire issues
    //
    //     newEntry.find(':input').each(function () {
    //         $(this).val('')
    //     })
    //     newEntry.find('.custom-file-label').each(function () {
    //         $(this).text('')
    //     });
    //     newEntry.find(':file').each(function () {
    //         $(this).val('')
    //     });
    //     controls.find('.entry:last span.btn-add')
    //         .removeClass('btn-add').addClass('btn-remove')
    //         .html('<i class="fas fa-minus"></i>')
    //     renumber_resource()
    // }).on('click', '.btn-remove', function (e) {
    //     $(this).parents('.entry:first').remove()
    //     renumber_resource()
    //     e.preventDefault()
    //     return false
    // })

    $(document).on('click', '[data-confirm=confirmation]', function () {
        let url = $(this).is("[data-href]") ? $(this).data("href") : $(this).attr('href')
        let method = $(this).data('method')
        bootbox.confirm({
            title: $(this).data('title'),
            message: $(this).data('content'),
            buttons: {
                cancel: {
                    label: '<i class="fas fa-times-circle"></i> Cancel',
                    className: 'btn btn-primary'
                },
                confirm: {
                    label: '<i class="fas fa-check-circle"></i> Confirm',
                    className: 'btn btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    $(this).append(function () {
                        let methodForm = "\n"
                        methodForm += "<form action='" + url + "' method='POST' style='display:none'>\n"
                        methodForm += "<input type='hidden' name='_method' value='" + method + "'>\n"
                        methodForm += "<input type='hidden' name='_token' value='" + $('meta[name=csrf-token]').attr('content') + "'>\n"
                        methodForm += "</form>\n"
                        return methodForm
                    }).find('form').submit()
                }
            }
        })
    })

    $('.project-banner').on('click', function () {
        let img = $(this).data('name')
        let $bannerFile = $('#banner-file')
        $bannerFile.val(img)
        $bannerFile.attr('value', img)
        $('#banner-img').attr('src', Laravel.habitatBannersPath + img)
        $("#project-banner-modal .close").click()
    })

    $(document).on('change', '.custom-file-input', function () {
        let fileName = $(this).val().split('\\').pop()
        $(this).prev('.custom-file-label').addClass("selected").html(fileName)
    })

    setInterval(function () {
        let $footer = $('#footer')
        let docHeight = $(window).height()
        let footerHeight = $footer.height()
        let footerTop = $footer.position().top + footerHeight
        let marginTop = (docHeight - footerTop + 10)

        if (footerTop < docHeight)
            $footer.css('margin-top', marginTop + 'px') // padding of 30 on footer
        else
            $footer.css('margin-top', '0px')
    }, 250);
})

// Commented out: renumber_resource function no longer needed with Livewire ProjectResourceManager component
// function renumber_resource() {
//     $('.controls').children('.entry').each(function (index) {
//         $(this).find('legend').html('Resources ' + (index + 1))
//         $(this).find(':input').each(function () {
//             let $input = $(this)
//             let currentId = $input.attr('id')
//             let currentName = $input.attr('name')
//
//             // Only update attributes if they exist and contain the expected pattern
//             if (currentId && currentId.match(/\[[0-9]+\]/)) {
//                 $input.attr('id', currentId.replace(/\[[0-9]+\]/g, '[' + index + ']'))
//             }
//             if (currentName && currentName.match(/\[[0-9]+\]/)) {
//                 $input.attr('name', currentName.replace(/\[[0-9]+\]/g, '[' + index + ']'))
//             }
//         })
//     })
// }
