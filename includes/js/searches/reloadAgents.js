jQuery(document).ready(function($) {
    if(variablesAgents.currentUserRole !== "agent") {
        let select = $("#agents");
        select.click(function() {
            agentSelected = parseInt($("#agents :selected").val());
            $.ajax({
                url: variablesAgents.getAgentsURL,
                type: "GET",
                dataType: "json"
            }).success(function(response) {
                $("#agents").empty();
                response.forEach(function(val) {
                    $("<option/>")
                        .val(val.ID)
                        .text(val.display_name)
                        .appendTo("#agents");
                    if(agentSelected === val.ID) {
                        $("#agents option:last-child").attr("selected", "selected");
                    }
                });
            });
        });
    }
});