(function($) {
    $(document).ready(function() {

        var input = $("#addressInput");

        if(input.attr("name") === "address") {
            var context = "searchAddress";
            var minLength = 5;
        } else if(input.attr("name") === "city") {
            var context = "searchBar";
            var minLength = 3;
        }

        input.autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: URLGetAddressDataFile,
                    headers: {
                        'X-CSRF-Token': tokenValue
                    },
                    data: {
                        "query": input.val(),
                        "context": context
                    },
                    dataType: "json",
                    success: function(data) {
                        var labels = [];
                        data.forEach(result => {
                            if(input.attr("name") === "city") {
                                var valueLabel = result.city + '';
                                if(typeof(result.postCode) !== "undefined") {
                                    valueLabel += ' ' + result.postCode;
                                }
                                if(typeof(result.adminLvl2) !== "undefined") {
                                    valueLabel += ' ' + result.adminLvl2;
                                }
                                if(typeof(result.adminLvl1) !== "undefined") {
                                    valueLabel += ' ' + result.adminLvl1;
                                }
                            }else if(input.attr("name") === "address") {
                                var valueLabel = result.address;
                            }
                            labels.push(valueLabel);
                        });
                        response(labels);
                    }
                });
            },
            minLength: minLength,
            mustMatch: false,
            delay: 500
        });

    });
})(jQuery);