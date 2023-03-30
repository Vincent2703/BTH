jQuery(document).ready(function($) {   
    $(".importLink").click(function() {
        var confirmation = confirm(variablesImport.confirmation);
        if(confirmation) {
            var url = encodeURI(variablesImport.url);
            var file = $(this).attr("data-file");

            if(jQuery("#publishAds").is(":checked")) {
                url = url+"&publishAds";
            }
            if(jQuery("#replaceAds").is(":checked")) {
                url = url+"&replaceAds";
            }
            url = url+"&import="+file;
            window.location.href = url;
        }
        return confirmation;
    });
});