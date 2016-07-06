$(function () {

    $('.sidebar-menu li a[href="' + location.href + '"]')
        .addClass('active')
        .closest('li').addClass('active')
        .closest("ul")
        .css('display', 'block')
        .closest('li')
        .addClass('active');

    $('.ckeditor').ckeditor();

    $(".source li").draggable({
        addClasses: false,
        appendTo: "body",
        helper: "clone"
    });

    $(".target").droppable({
        addClasses: false,
        activeClass: "listActive",
        accept: ":not(.ui-sortable-helper)",
        drop: function(event, ui) {
            $(this).find(".placeholder").remove();
            var link = $("<a href='#' class='dismiss'>x</a>");
            var id = ui.draggable.attr('id');
            var list = $('<li id="'+ id +'"></li>').text(ui.draggable.text());
            $(list).append(link);
            $(list).appendTo(this);
        }
    }).sortable({
        items: "li:not(.placeholder)",
        sort: function() {
            $(this).removeClass("listActive");
        }
    }).on("click", ".dismiss", function(event) {
        event.preventDefault();
        $(this).parent().remove();
    });

    $('#workflow').submit(function(){
        if ($('ul.target').children().length < 1) {
            $('#workflow').append($('<input>').attr({'type':'hidden','name':'actors[0][id]'}).val(''));

        } else {
            $('ul.target').children().each(function (i) {
                var id = $(this).attr('id');
                $('#workflow').append($('<input>').attr({'type': 'hidden', 'name': 'actors[' + i + '][id]'}).val(id));
            });
        }
    });
});
