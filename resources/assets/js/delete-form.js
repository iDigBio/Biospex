$('[data-toggle=confirmation]').confirmation({
    rootSelector: '[data-toggle=confirmation]',
    singleton: true,
    onConfirm: function(event){
        $(this).append(function () {
            let methodForm = "\n";
            let url = $(this).is("[data-href]") ? $(this).data("href") : $(this).attr('href');
            methodForm += "<form action='" + url + "' method='POST' style='display:none'>\n";
            methodForm += "<input type='hidden' name='_method' value='" + $(this).data('method') + "'>\n";
            methodForm += "<input type='hidden' name='_token' value='" + $('meta[name=csrf-token]').attr('content') + "'>\n";
            methodForm += "</form>\n";
            return methodForm;
        }).find('form').submit();
    }
});
