jQuery(document).ready(function($) {
    /* Only way I see. I can't add a custom sidebar after the header but before the content for each theme */
    $.ajax({
        url: variablesSearchBar.searchBarURL+window.location.search,
        type: "GET"                 
    }).done(function(response) {
        nonce = variablesSearchBar.nonce;
        $("header:first").after(response);
        $.ajax({
           url: variablesSearchBar.autocompleteURL,
           dataType: "script"
        });
    });
    
});

function addFilters(button) {
    var complementaryFilters = jQuery(".compSearchBarInputs");
    if(complementaryFilters.is(":hidden")) {
        complementaryFilters.show("slow");
        jQuery(button).text(variablesSearchBar.filters+" -");
    }else{
        complementaryFilters.hide("slow");
        jQuery(button).text(variablesSearchBar.filters+" +");
    }
    
}

function searchByR(select) {
    var value = jQuery(select).val();
    if(value==="radius") {
        jQuery("#radiusInput").show("slow");
    }else{
        jQuery("#radiusInput").hide("slow");
    }
}

function changeAdType(select) {
    /*var type = jQuery(select).val();
    if(type==="rental") {
        console.log("coucou");
    }*/
}