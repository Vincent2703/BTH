(function($){
$(document).ready(function(){
    var accentMap = {
      "à": "a",
      "â": "a",
      "é": "e",
      "è": "e",
      "ê": "e",
      "ë": "e",
      "ï": "i",
      "î": "i",
      "ô": "o",
      "ö": "o",
      "û": "u",
      "ù": "u",
      "À": "A",
      "Â": "A",
      "É": "E",
      "È": "E",
      "Ê": "E",
      "Ë": "E",
      "Ï": "I",
      "Î": "I",
      "Ô": "O",
      "Ö": "O",
      "Û": "U",
      "Ù": "U"
    };
    function normalize(value) {
        let res = '';
        for (let i = 0; i < value.length; i++) {
            res += accentMap[value.charAt(i)] || value.charAt(i);
        }
        return res;
    };
 
    let input = $("#addressInput");
    input.keyup(function(){
        if($(this).val() !== '') {
            var getValue = encodeURIComponent($(this).val());
            if(input.attr("name") === "address") {
                var url = "https://api-adresse.data.gouv.fr/search/?q="+getValue+"&limit=5";
                var minLength = 5;
            }else if(input.attr("name") === "city") {
                var url = "https://api-adresse.data.gouv.fr/search/?q="+getValue+"&type=municipality&limit=5";
                var minLength = 3;
            }
            $.ajax({
                cache: false,
                url: url,
                dataType: "json",
                success: function(data){
                    let labels = [];
                    data.features.forEach(feature => {
                        var valueLabel = feature.properties.label;
                        if(input.attr("name") === "city") {
                            valueLabel += ' '+feature.properties.postcode;
                        }
                        labels.push(valueLabel);
                    });
                    
                    input.autocomplete({
                        source: function(request, response) {
                            var matcher = new RegExp("^(?=.*\\b" + request.term.trim().split(/\s+/).join("\\b)(?=.*\\b") + ").*$", 'i' );
                            response($.grep(labels, function(value) {
                                value = value.label || value.value || value;
                                return matcher.test(value) || matcher.test(normalize(value));
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