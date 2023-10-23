jQuery(document).ready(function($) {
    $.ajax({
        url: variablesSearchBar.searchBarURL+window.location.search,
        type: "GET"                 
    }).done(function(response) {
        $("header:first").after(response);
            $("#searchBySelect").change(function() {
                searchByR($("#searchBySelect :selected").val());
            });
            $("#filters").click(function() {
                manageFilters($("#filters"));
            });
            $(".searchBar #addressInput").on("input", function() {
                $(".searchBar input[type=submit]").prop("disabled", true);
                $(".searchBar #addressInput").removeClass("inputHighlighted");
            });
            $(".searchBar .searchBtn").on("click", function() {
                if($(this).has("input[type=submit]:disabled").length === 1) {
                    $(".searchBar #addressInput").addClass("inputHighlighted");
                }
            });
            $(".searchBar #btnSearchBarSmallScreens").on("click", function() {
                $(this).hide();
                $(".searchForm").show("slow");
                $(".searchBar #btnCloseSearchBarSmallScreens").show();
            });
            $(".searchBar #btnCloseSearchBarSmallScreens").on("click", function() {
                $(this).hide();
                $(".searchForm").hide("slow");
                $(".searchBar #btnSearchBarSmallScreens").show();
            });
        $.ajax({
           url: variablesSearchBar.autocompleteURL,
           dataType: "script"
        });
    }); 
});