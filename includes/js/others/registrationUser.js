jQuery(document).ready(function($) {
    $("#role").click(function() {
        let role = $("#role option:selected").val();
        let extraInformation = $("#extraInformation");
        let roleName = $("#roleName");

        if(role === "agent" || role === "agency") {
            extraInformation.show();
            roleName.text(role);    
            $($('.'+role), extraInformation).show();
        }
    });
});

