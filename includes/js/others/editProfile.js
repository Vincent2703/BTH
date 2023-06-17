jQuery(document).ready(function($) {
    var role = jQuery("#role option:selected").val();
    var extraInformation = $("#extraInformation");
    var extraInformationTitle = $("#extraInformationTitle");
    var roleName = $("#roleName");
    var applicationPasswords = $(".application-passwords");

    role = role || roleName.text().toLowerCase();
    
    if(role === "customer" || role === "agent" || role === "agency") {
        applicationPasswords.hide();
        extraInformation.show();
         $($('.'+role), extraInformation).show();
    }else{
        extraInformationTitle.hide();
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
