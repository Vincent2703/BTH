(function($){
$(document).ready(function(){
 
    let input = $("#addressInput");
    input.keyup(function(){
        if($(this).val() !== '') {
            var getValue = encodeURIComponent($(this).val());
            if(input.attr("name") === "address") {
                var url = URLGetAddressDataFile+"?query="+getValue;
                var minLength = 5;
            }else if(input.attr("name") === "city") {
                var url = URLGetAddressDataFile+"?query="+getValue+"&city";
                var minLength = 3;
            }
            $.ajax({
                cache: false,
                url: url,
                dataType: "json",
                success: function(data){
                    let labels = [];
                    data.forEach(result => {
                        var valueLabel = result.address;
                        if(input.attr("name") === "city") {
                            valueLabel += ' '+result.postcode;
                        }
                        labels.push(valueLabel);
                    });
                    
                    input.autocomplete({
                        source: function(request, response) {
                            var matcher = new RegExp("^(?=.*\\b" + request.term.trim().split(/\s+/).join("\\b)(?=.*\\b") + ").*$", 'i' );
                            response($.grep(labels, function(value) {
                                value = value.label || value.value || value;
                                return matcher.test(value) || matcher.test(value.normalize("NFD").replace(/\p{Diacritic}/gu, ""));
                            }));
                        },
                        minLength: minLength,
                        mustMatch: false
                    });
                }
            });
        }
    });
});
})(jQuery);