jQuery(document).ready(function(){
    jQuery.ajax({
        url: "wp-content/plugins/"+pluginName+"/templates/searchBars/searchBarAd.php"+window.location.search,
        type: "GET"                 
    }).done(function(response) {
        jQuery("header:first").after(response);
    });
});

