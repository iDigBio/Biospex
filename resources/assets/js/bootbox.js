$('[data-toggle=confirmation]').on('click', function(e){
    let url = $(this).is("[data-href]") ? $(this).data("href") : $(this).attr('href');
    let method = $(this).data('method');
    bootbox.confirm({
        title: "Destroy record?",
        message: "Do you want to delete now? This cannot be undone.",
        buttons: {
            cancel: {
                label: '<i class="fa fa-times"></i> Cancel',
                className: 'btn-danger'
            },
            confirm: {
                label: '<i class="fa fa-check"></i> Confirm',
                className: 'btn-success'
            }
        },
        callback: function (result) {
            if (result) {
                $(this).append(function () {
                    let methodForm = "\n";
                    methodForm += "<form action='" + url + "' method='POST' style='display:none'>\n";
                    methodForm += "<input type='hidden' name='_method' value='" + method + "'>\n";
                    methodForm += "<input type='hidden' name='_token' value='" + $('meta[name=csrf-token]').attr('content') + "'>\n";
                    methodForm += "</form>\n";
                    return methodForm;
                }).find('form').submit();
            }
        }
    });
})