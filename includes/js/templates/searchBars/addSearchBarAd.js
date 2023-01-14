jQuery(document).ready(function($) {

    $.ajax({
        url: "wp-content/plugins/"+variables.pluginName+"/templates/searchBars/searchBarAd.php"+window.location.search,
        type: "GET"                 
    }).done(function(response) {
        $("header:first").after(response);
        $.ajax({
           url: "wp-content/plugins/"+variables.pluginName+"/includes/js/ajax/autocompleteAddress.js",
           dataType: "script"
        });
    });
    
});

function addFilters(button) {
    var complementaryFilters = jQuery(".compSearchBarInputs");
    if(complementaryFilters.is(":hidden")) {
        complementaryFilters.show("slow");
        jQuery(button).text(variables.filters+" -");
    }else{
        complementaryFilters.hide("slow");
        jQuery(button).text(variables.filters+" +");
    }
    
}

function changeAdType(select) {
    var type = jQuery(select).val();
    if(type==="rental") {
        console.log("coucou");
    }
}