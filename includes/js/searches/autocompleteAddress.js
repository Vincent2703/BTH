jQuery(document).ready(function($) {

    const input = $("#addressInput");

    const context = input.attr("data-context");
    const minLength = context === "searchAddress" ? 5 : 3;
    const url = typeof(variablesAddress) !== "undefined" ? variablesAddress.getAddressDataURL : variablesSearchBar.getAddressDataURL;
    const allCity = typeof(variablesAddress) !== "undefined" ? variablesAddress.allCity : variablesSearchBar.allCity

    const nonce = input.attr("data-nonce");

    input.autocomplete({
        source: function(request, response) {
            $.ajax({
                url: url,
                data: {
                    "query": input.val().normalize("NFD").replace(/[\u0300-\u036f]/g, ""),
                    "context": context,
                    "nonce": nonce
                },
                type: "POST",
                dataType: "json",
                success: function(data) {
                    var labels = [];
                    data.forEach(result => {
                        if(context === "searchBar") {
                            var valueLabel = result.city + '';
                            if(typeof(result.postCode) !== "undefined") {
                                valueLabel += ' ' + result.postCode;
                            }else{
                                valueLabel += " ("+allCity+")";
                            }
                            if(typeof(result.adminLvl2) !== "undefined") {
                                valueLabel += ' ' + result.adminLvl2;
                            }
                            if(typeof(result.adminLvl1) !== "undefined") {
                                valueLabel += ' ' + result.adminLvl1;
                            }
                            if(!labels.some(item => item.label === valueLabel)) {
                                labels.push({label: valueLabel, value: valueLabel, city: result.city, postCode: result.postCode, coords:{lat: result.lat, long: result.long}});
                            }
                        }else if(context === "searchAddress") {
                            var valueLabel = result.address;
                            var res = {label: valueLabel, value: valueLabel};
                            if(typeof(result.placeId) !== "undefined") {
                                res.placeId = result.placeId;
                            }
                            labels.push(res);
                        }
                    });
                    response(labels);
                },
                error: function(error) {
                    response([]);
                }
                
            });
        },
        select: function(event, ui) {
            let res = ui.item;
            if(context === "searchBar") {
               $(".searchBar input[name=city]").val(res.city);
               $(".searchBar input[name=postCode]").val(res.postCode);
               $(".searchBar input[name=lat]").val(res.coords.lat);
               $(".searchBar input[name=long]").val(res.coords.long);
               $(".searchBar input[type=submit]").prop("disabled", false);
            }else if(context === "searchAddress") {
                $("#address input[name=placeId]").val(res.placeId);
            }
        },
        minLength: minLength,
        mustMatch: false,
        delay: 1000
    });

});
