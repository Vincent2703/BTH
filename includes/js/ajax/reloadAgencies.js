function reloadAgencies() {
    agencySelected = parseInt(jQuery("#agencies :selected").val());
    jQuery.ajax({
        url: "../wp-content/plugins/"+pluginName+"/models/ajax/getAgencies.php",
        type: "GET"                 
    }).done(function(response) {
        jQuery("#agencies").empty();
        let json = JSON.parse(response);
        json.forEach(function(val) {
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