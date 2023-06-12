jQuery(document).ready(function($) {
    var role = jQuery("#role option:selected").val();
    var extraInformation = $("#extraInformation");
    var roleName = $("#roleName");
    if(role === "customer" || role === "agent" || role === "agency") {
        extraInformation.show();
         $($('.'+role), extraInformation).show();
    }
    
    $("#role").click(function() {
        role = $("#role option:selected").val();

        if(role === "customer" || role === "agent" || role === "agency") {
            extraInformation.show();
            roleName.text(role);    
            $($('.'+role), extraInformation).show();
            $($("tr:not(."+role+')', extraInformation)).hide();
        }
    });
});
