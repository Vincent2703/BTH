jQuery(document).ready(function(){
    jQuery('.table-add').click(function() {
        //var arrayIDs = [];
        /*jQuery("#table tr:not(:first-child)").each(function() {
            arrayIDs.push(jQuery(this).find("td").first().text());
        });*/
        
        //console.log(arrayIDs);
        jQuery("#table tr:last").after(
            `<tr>
                <td class="editable" contenteditable="true"><span class="id contentTD">[...]</span></td>
                <td class="editable" contenteditable="true"><span class="name contentTD">[...]</span></td>
                <td class="editable">            
                    <select class="kindField">
                        <option>text</option>
                        <option>number</option>
                        <option>radio</option>
                        <option>select</option>
                        <option>checkbox</option>
                        <option>picture</option>
                    </select
                </td>
                <td class="editable">
                    <select class="section">
                        <option>basics</option>
                        <option>complementary</option>
                        <option>category</option>
                        <option>title</option>
                        <option>description</option>
                        <option>status</option>
                    </select>
                </td>
                <td>
                    <span class="config-toggle dashicons-before dashicons-arrow-down-alt2"></span>
                </td>
                <td>
                    <span class="table-up dashicons-before dashicons-arrow-up-alt"></span>
                    <span class="table-down dashicons-before dashicons-arrow-down-alt"></span>
                </td>
                <td>
                    <span class="table-remove dashicons-before dashicons-trash"></span>
                </td>
            </tr>
            <tr class="collapse">
                <td class="editable">Style CSS : <textarea class="css"></textarea></td>
                <td class="editable writeOptions">Options possibles : <textarea class="options" placeholder="option1;option2;option3;"></textarea></td>
            </tr>`
        );
    });

    jQuery('#table').on('click', '.table-remove', function() {
        jQuery(this).parents('tr')[0].remove();
    });
    
    jQuery('#table').on('click', '.table-visible', function() {
        jQuery(this).toggleClass("dashicons-visibility dashicons-hidden ");
        jQuery(this).toggleClass("table-visible table-hidden");
        var row = jQuery(this).closest('tr');
        row.removeClass('hiddenField');
        //row.find(".section").prop("disabled", false);
    });
    
    jQuery('#table').on('click', '.table-hidden', function() {
        jQuery(this).toggleClass("dashicons-hidden dashicons-visibility");
        jQuery(this).toggleClass("table-hidden table-visible");
        var row = jQuery(this).closest('tr');
        row.addClass("hiddenField");
        //row.find(".section").prop("disabled", true);
    });

    jQuery('#table').on('click', '.table-up', function() {
           var row = jQuery(this).parents('tr');
           if (row.index() === 1 || jQuery(this).closest("tr").prev("tr").attr("class") === "table-separator") return; // Don't go above the header
           row.prev().before(row.get(0));
       });

    jQuery('#table').on('click', '.table-down', function() {
           var row = jQuery(this).parents('tr');
           row.next().after(row.get(0));
    });
       
    jQuery('#table').on('click', '.config-toggle', function() {
        jQuery(this).closest("tr").next("tr").toggle();
        if(jQuery(this).closest("tr").next("tr").is(":hidden")) {
            jQuery(this).toggleClass("dashicons-arrow-down-alt2 dashicons-arrow-up-alt2");
        }else{
            jQuery(this).toggleClass("dashicons-arrow-up-alt2 dashicons-arrow-down-alt2");
        }
    });
    
    jQuery("#table").on("change", ".kindField", function() {
        if(jQuery(this).closest("tr").find(".kindField option:selected").text() === "select" || jQuery(this).closest("tr").find(".kindField option:selected").text() === "radio") {
            jQuery(this).parents("tr").find(".writeOptions").show();
        }else{
            jQuery(this).parents("tr").find(".writeOptions").hide();
        }
    });

    jQuery("#table").on("change click", "span, select, textarea", function() {
        var listFields = []
        jQuery("#table tbody tr:not(.table-separator):nth-child(odd").each(function() {
           listFields.push({
                id: jQuery(this).find(".id").text(),
                name: jQuery(this).find(".name").text(),
                kindField: jQuery(this).find(".kindField option:selected").text(),
                section: jQuery(this).find(".section option:selected").text(),
                unchangeable: (jQuery(this).find(".section").is(":disabled") ? "true" : "false"), 
                perso: (jQuery(this).find(".name").prev("td").isContentEditable ? "true" : "false"),
                hidden: (jQuery(this).find(".dashicons-before").hasClass("table-visible") ? "true" : "false"),
                css: jQuery(this).next("tr").find(".css").val(),
                options: jQuery(this).next("tr").find(".options").val()
           });
        });
        jQuery("#mappingFields").text(JSON.stringify(listFields));
    });

});



function readOnlyFields(checkbox, fields) {
    if(checkbox.checked === true) {
        fields.forEach(function(field){
            document.getElementById(field).readOnly = false;
            document.getElementById(field).required = true;
        });
    }else{
        fields.forEach(function(field){
            document.getElementById(field).readOnly = true;
            document.getElementById(field).required = false;
        });
    }
}