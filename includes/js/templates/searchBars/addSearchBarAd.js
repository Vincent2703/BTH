jQuery(document).ready(function($) {
    $.ajax({
        url: "wp-content/plugins/"+pluginName+"/templates/searchBars/searchBarAd.php"+window.location.search,
        type: "GET"                 
    }).done(function(response) {
        $("header:first").after(response);
        $.ajax({
           url: "wp-content/plugins/"+pluginName+"/includes/js/ajax/autocompleteAddress.js",
           dataType: "script"
        });
    });
});

function addFilters(button) {
    var complementaryFilters = jQuery(".compSearchBarInputs");
    if(complementaryFilters.is(":hidden")) {
        complementaryFilters.show("slow");
        jQuery(button).text("FILTRES -");
    }else{
        complementaryFilters.hide("slow");
        jQuery(button).text("FILTRES +");
    }
    
}