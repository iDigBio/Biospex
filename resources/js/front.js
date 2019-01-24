$(function () {

    $('#external-carousel-btns li').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        let num = $(this).data('slide-to');
        $('.carousel-div').removeClass('active');
        $('.div-' + num).addClass('active');
    });
});
