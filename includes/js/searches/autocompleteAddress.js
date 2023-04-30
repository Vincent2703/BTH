(function($) {
    $(document).ready(function() {

        const input = $("#addressInput");

        const context = input.attr("name") === "address" ? "searchAddress" : "searchBar";
        const minLength = input.attr("name") === "address" ? 5 : 3;
        const url = typeof(variablesAddress) !== "undefined" ? variablesAddress.getAddressDataURL : variablesSearchBar.getAddressDataURL
        
        input.autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: url,
                    data: {
                        "query": input.val(),
                        "context": context
                    },
                    type: "GET",
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
                    },
                    error: function(error) {
                        response([]);
                    }
                });
            },
            minLength: minLength,
            mustMatch: false,
            delay: 1000
        });

    });
})(jQuery);