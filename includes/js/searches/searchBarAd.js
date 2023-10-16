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

function addFilters(elem) {
    var complementaryFilters = jQuery(".filtersSearchBarInputs");
    jQuery(".dashicons", elem).toggleClass("dashicons-plus-alt dashicons-minus");
    if(complementaryFilters.is(":hidden")) {
        complementaryFilters.show("slow");
        jQuery(".searchBar .filtersSearchBarInputs input[type=submit]").show();
        jQuery(".searchBar .mainSearchBarInputs input[type=submit]").hide();
    }else{
        complementaryFilters.hide("slow");
        jQuery(".searchBar .filtersSearchBarInputs input[type=submit]").hide();
        jQuery(".searchBar .mainSearchBarInputs input[type=submit]").show();
    }   
}

function searchByR(value) {
    const searchBar = jQuery(".searchBar");
    if(value==="radius") {
        jQuery("#radiusInput").show("slow");
    }else{
        jQuery("#radiusInput").hide("slow");
    }
}
