jQuery(document).ready(function($) {
    
    $("#customFields .fieldName, #customFields .valuesToReplace").click(function() {
       $(this).text(''); 
    });
    
    $("#customFields .fieldName").each(function() {
       $(this).text("Ex : Orientation"); 
    });
       
    $("#customFields .valuesToReplace").each(function() {
       $(this).text("Ex : 1:North,2:South,3:Est,4:West"); 
    });
});