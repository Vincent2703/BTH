//Manage the custom fields section in the general tab
jQuery(document).ready(function($) {        
    const customFields = $("#customFields");
    const form = $("form");
    
    /* Add a row */    
    $(".fieldPlus").click(function() {
        $("#customFields tr:last").after('<tr><td class="fieldName"><input type="text" placeholder="Ex : Orientation"></td><td class="section"><select><option id="mainFeatures">'+variablesOptions.mainFeatures+'</option><option id="additionalFeatures">'+variablesOptions.additionalFeatures+'</option></select></td><td><span class="dashicons-before dashicons-arrow-up-alt fieldUp"></span><span class="dashicons-before dashicons-arrow-down-alt fieldDown"></span></td><td><span class="dashicons-before dashicons-trash fieldTrash"></span></td></tr>');
        
        $(".fieldUp").click(function() {
           moveRow(this, "up");
        });

        $(".fieldDown").click(function() {
           moveRow(this, "down");
        });

        $(".fieldTrash").click(function() {
           deleteRow(this);
        });
        
        $(".fieldName input").on("input", function() {
           removeDemo(); 
        });
    });
    
    $(".fieldUp").click(function() {
        moveRow(this, "up");
    });

     $(".fieldDown").click(function() {
        moveRow(this, "down");
    });

     $(".fieldTrash").click(function() {
        deleteRow(this);
    });

    $("form").submit(function() { //Before the form is submitted
        updateCustomFieldsData(); //Get custom fields in the hidden input
    });
});

function deleteRow(element) {
    if(!jQuery(jQuery(element).parents().get(1)).hasClass("demo")) {
        jQuery(jQuery(element).parents().get(1)).remove();
    }
}

function moveRow(element, direction) {
    var row = jQuery(element).parents("tr:first");
    if(direction === "down") {
        row.insertAfter(row.next());
    }else {
        row.insertBefore(row.prev());
    }
}

function removeDemo() {
    jQuery(".demo").removeClass("demo");
}

function updateCustomFieldsData() {
    var fields = [];
    var field = {};
    jQuery("#customFields tbody tr").each(function() {
        var fieldNameValue = jQuery(jQuery(this).find(".fieldName input")).val();
        var sectionValue = jQuery(jQuery(this).find(".section select option:selected")).prop("id"); 
        if(fieldNameValue.length > 0 ) {
        field = {
            name: fieldNameValue,
            nameAttr: fieldNameValue.replace(/[^a-zA-Z0-9]/i, ''),
            section: sectionValue
        };
        fields.push(field);
    }
    });
    jQuery("#customFieldsData").val(JSON.stringify(fields));
    
}