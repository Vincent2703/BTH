jQuery(document).ready(function($) {          
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
});

function manageFilters(elem) {
    var complementaryFilters = jQuery(".filtersSearchBarInputs");
    jQuery(".dashicons", elem).toggleClass("dashicons-plus-alt dashicons-minus");
    if(complementaryFilters.is(":hidden")) {
        complementaryFilters.show("slow");
        jQuery(".searchBar .filtersSearchBarInputs .searchBtn").show();
        jQuery(".searchBar .mainSearchBarInputs .searchBtn").hide();
    }else{
        complementaryFilters.hide("slow");
        jQuery(".searchBar .filtersSearchBarInputs .searchBtn").hide();
        jQuery(".searchBar .mainSearchBarInputs .searchBtn").show();
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