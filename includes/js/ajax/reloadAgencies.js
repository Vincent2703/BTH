function reloadAgencies() {
    agencySelected = parseInt(jQuery("#agencies :selected").val());
    jQuery.ajax({
        url: variables.getAgenciesURL,
        type: "GET",
        dataType: "json"
    }).success(function(response) {
        jQuery("#agencies").empty();
        response.forEach(function(val) {
            jQuery("<option/>")
                .val(val.ID)
                .text(val.post_title)
                .appendTo("#agencies");
            if(agencySelected === val.ID) {
                jQuery("#agencies option:last-child").attr("selected", "selected");
            }
        });
    });
}