(function($) {
    $(document).ready(function() {

        var input = $("#addressInput");

        if(input.attr("name") === "address") {
            var url = URLGetAddressDataFile;
            var minLength = 5;
        } else if(input.attr("name") === "city") {
            var url = URLGetAddressDataFile + "?city";
            var minLength = 3;
        }

        input.autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: url,
                    data: {
                        "query": input.val()
                    },
                    dataType: "json",
                    success: function(data) {
                        var labels = [];
                        data.forEach(result => {
                            var valueLabel = result.address;
                            if(input.attr("name") === "city") {
                                valueLabel += ' ' + result.postcode;
                            }
                            labels.push(valueLabel);
                        });
                        response(labels);
                    }
                });
            },
            minLength: minLength,
            mustMatch: false
        });

    });
})(jQuery);