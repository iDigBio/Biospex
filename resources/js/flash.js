if (Laravel.flashMessage.length) {
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