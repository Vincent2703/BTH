jQuery(document).ready(function($) {
    $("#role").click(function() {
        let role = $("#role option:selected").val();
        let extraInformationTitle = $("#extraInformationTitle");
        let extraInformation = $("#extraInformation");
        let roleName = $("#roleName");

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

