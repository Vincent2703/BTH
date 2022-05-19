function reloadAgents(/*savedAgent*/){
    jQuery.ajax({
        url: "../wp-content/plugins/"+pluginName+"/includes/php/getAgents.php",
        type: "GET"                 
    }).done(function(response) {
        jQuery("#agents").empty();
        let json = JSON.parse(response);
        json.forEach(function(val) {
            jQuery('<option/>')
                .val(val.ID)
                .text(val.post_title)
                .appendTo("#agents");
            /*if(savedAgent === val.ID) {
                jQuery('#agents option:last-child').attr('selected', 'selected');
            }*/
        });
    });
}