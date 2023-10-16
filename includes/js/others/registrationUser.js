jQuery(document).ready(function($) {
            var role = $("#role option:selected").val();
        var extraInformationTitle = $("#extraInformationTitle");
        var extraInformation = $("#extraInformation");
        var roleName = $("#roleName");
        
        
    if(role === "agent" || role === "agency") {
         extraInformationTitle.show();
            extraInformation.show();
            roleName.text(role);    
            $($('.'+role), extraInformation).show();
            $($("tr:not(."+role+')', extraInformation)).hide();
    }
    
    $("#role").click(function() {
        role = $("#role option:selected").val();
        extraInformationTitle = $("#extraInformationTitle");
        extraInformation = $("#extraInformation");
        roleName = $("#roleName");
        if(role === "agent" || role === "agency") {
            extraInformationTitle.show();
            extraInformation.show();
            roleName.text(role);    
            $($('.'+role), extraInformation).show();
            $($("tr:not(."+role+')', extraInformation)).hide();
        }else{
            extraInformationTitle.hide();
            extraInformation.hide();
        }
    });
});

