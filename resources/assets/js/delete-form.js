$('.delete-form').append(function () {
    var methodForm = "\n";
    var url = $(this)[0].hasAttribute("data-href") ? $(this).data('href') : $(this).attr('href');
    methodForm += "<form action='" + url + "' method='POST' style='display:none'>\n";
    methodForm += "<input type='hidden' name='_method' value='" + $(this).data('method') + "'>\n";
    methodForm += "<input type='hidden' name='_token' value='" + $('meta[name=csrf-token]').attr('content') + "'>\n";
    methodForm += "</form>\n";

    return methodForm
})
    .attr('onclick', '$(this).find("form").submit();');
