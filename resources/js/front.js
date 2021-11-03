$(function () {

    $('#external-carousel-btns li').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
    });
});
