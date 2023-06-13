jQuery(document).ready(function($) {
    let select = $("#agencies");
    select.click(function() {
        agencySelected = parseInt($("#agencies :selected").val());
        $.ajax({
            url: variablesAgencies.getAgenciesURL,
            type: "GET",
            dataType: "json"
        }).success(function(response) {
            $("#agencies").empty();
            response.forEach(function(val) {
                $("<option/>")
                    .val(val.ID)
                    .text(val.data.display_name)
                    .appendTo("#agencies");
                if(agencySelected === val.ID) {
                    $("#agencies option:last-child").attr("selected", "selected");
                }
            });
        });
    });
});