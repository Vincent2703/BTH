function reloadAgents() {
    agentSelected = parseInt(jQuery("#agents :selected").val());
    jQuery.ajax({
        url: "../wp-content/plugins/"+pluginName+"/includes/php/getAgents.php",
        type: "GET"                 
    }).done(function(response) {
        jQuery("#agents").empty();
        let json = JSON.parse(response);
        json.forEach(function(val) {
            jQuery("<option/>")
                .val(val.ID)
                .text(val.post_title)
                .appendTo("#agents");
            if(agentSelected === val.ID) {
                jQuery("#agents option:last-child").attr("selected", "selected");
            }
        });
    });
}