jQuery(document).ready(function($) {
    /* Only way I see. I can't add a custom sidebar after the header but before the content for each theme */
    $.ajax({
        url: variablesSearchBar.searchBarURL+window.location.search,
        type: "GET"                 
    }).done(function(response) {
        nonce = variablesSearchBar.nonce;
        $("header:first").after(response);
            $("#searchBySelect").change(function() {
                searchByR($("#searchBySelect :selected").val());
            });
            $("#filters").click(function() {
                addFilters($("#filters"));
            });
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

function searchByR(value) {
    if(value==="radius") {
        jQuery("#radiusInput").show("slow");
    }else{
        jQuery("#radiusInput").hide("slow");
    }
}
