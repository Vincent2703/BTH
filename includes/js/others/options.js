jQuery(document).ready(function($) {    
    if($("input[name='option_page']").val() === "BTHOptionsDisplayadsGroup") { //If we are in the displayads tab
            
        /* Add a row */
        $(".fieldPlus").click(function() {
            $("#customFields tr:last").after('<tr><td class="fieldName"><input type="text" placeholder="Ex : Orientation"></td><td class="section"><select><option id="mainFeatures">'+translations.mainFeatures+'</option><option id="complementaryFeatures">'+translations.complementaryFeatures+'</option></select></td><td><span class="dashicons-before dashicons-arrow-up-alt fieldUp" onclick="moveRow(this, \'up\');"></span><span class="dashicons-before dashicons-arrow-down-alt fieldDown" onclick="moveRow(this, \'down\');"></span></td><td><span onclick="deleteRow(this);" class="dashicons-before dashicons-trash fieldTrash"></span></td></tr>');
        });
        

        $("form").submit(function() { //Before the form is submitted
            updateCustomFieldsData(); //Get custom fields in the hidden input
        });
    }
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
        var sectionValue = jQuery(jQuery(this).find(".section select option:selected")).attr("id"); 
        if(fieldNameValue.length > 0 ) {
        field = {
            name: fieldNameValue,
            section: sectionValue
        };
        fields.push(field);
    }
    });
    jQuery("#customFieldsData").val(JSON.stringify(fields));
    
    }