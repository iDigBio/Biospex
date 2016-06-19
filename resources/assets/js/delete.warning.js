$(function(){
    $('[data-method]').not(".disabled").append(function(){
        var methodForm = "\n";
        methodForm += "<form action='"+$(this).attr('href')+"' method='POST' style='display:none'>\n";
        methodForm += " <input type='hidden' name='_method' value='"+$(this).attr('data-method')+"'>\n";
        methodForm +="<input type='hidden' name='_token' value='"+$('meta[property="csrf-token"]').attr('content')+"'>\n";
        methodForm += "</form>\n";

        return methodForm
    })
    .removeAttr('href')
    .attr('onclick',' if ($(this).attr(\'data-confirm\')) { if(confirm("Are you sure you want to delete?")) { $(this).find("form").submit(); } } else { $(this).find("form").submit(); }')
});
