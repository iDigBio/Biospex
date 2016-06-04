$(function(){

    $('.sidebar-menu li a[href="' + location.href + '"]')
        .addClass('active')
        .closest('li').addClass('active')
        .closest("ul")
        .css('display', 'block')
        .closest('li')
        .addClass('active');
});