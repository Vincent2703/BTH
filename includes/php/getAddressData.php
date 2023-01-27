<?php
require_once(preg_replace('/wp-content(?!.*wp-content).*/', '', __DIR__ )."wp-load.php");
if(isset($_GET["query"]) && isset($_GET["context"])/*&& isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest"*/) {
    $apisOptions = get_option(PLUGIN_RE_NAME."OptionsApis");
    $apiUsed = $apisOptions["apiUsed"];
    $context = $_GET["context"];

    $query = urlencode(addslashes(sanitize_text_field($_GET["query"])));
    
    
    if($apiUsed === "govFr") {
            
        $arrayCleaned = array();
        switch($context) {
            case "searchBar": //We need city name and post code for results in searchbar
                $resultsResponseDataAPI = wp_remote_get("https://api-adresse.data.gouv.fr/search/?q=$query&type=municipality&limit=5");
                if(wp_remote_retrieve_response_code($resultsResponseDataAPI) === 200) {
                    $resultsBodyDataAPI = wp_remote_retrieve_body($resultsResponseDataAPI);
                    $resultsArrayDataAPI = json_decode($resultsBodyDataAPI, true);

                    foreach($resultsArrayDataAPI["features"] as $city) {
                        $resultsCleaned = array();
                        $resultsReponseGeoAPI = wp_remote_get("https://geo.api.gouv.fr/communes/".$city["properties"]["citycode"]."?fields=codesPostaux&limit=1");

                        if(wp_remote_retrieve_response_code($resultsReponseGeoAPI) === 200) {
                            $resultsBodyGeoAPI = wp_remote_retrieve_body($resultsReponseGeoAPI);
                            $resultsArrayGeoAPI = json_decode($resultsBodyGeoAPI, true);

                            $resultsCleaned["city"] = $city["properties"]["city"];
                            $resultsCleaned["postCode"] = min($resultsArrayGeoAPI["codesPostaux"]);
                        }
                        array_push($arrayCleaned, $resultsCleaned);
                    }

                }
            break;
            
            case "searchAds": ///We need the postcode + city or a perimeter in coordinates                
                $resultsResponseDataAPI = wp_remote_get("https://api-adresse.data.gouv.fr/search/?q=$query&type=municipality&limit=1");
                if(wp_remote_retrieve_response_code($resultsResponseDataAPI) === 200) {
                    $resultsBodyDataAPI = wp_remote_retrieve_body($resultsResponseDataAPI);
                    $resultsArrayDataAPI = json_decode($resultsBodyDataAPI, true);

                    $city = $resultsArrayDataAPI["features"][0]["properties"]["city"];
                    $cityCode = $resultsArrayDataAPI["features"][0]["properties"]["citycode"];
                    $resultsReponseGeoAPI = wp_remote_get("https://geo.api.gouv.fr/communes/".$cityCode."?fields=centre,codesPostaux&limit=1");

                    if(wp_remote_retrieve_response_code($resultsReponseGeoAPI) === 200) {
                        $resultsBodyGeoAPI = wp_remote_retrieve_body($resultsReponseGeoAPI);
                        $resultsArrayGeoAPI = json_decode($resultsBodyGeoAPI, true);

                        if(isset($_GET["searchBy"]) && $_GET["searchBy"] === "city") {
                            $arrayCleaned["city"] = $city;
                            if(preg_match("/(?:0[1-9]|[1-8]\d|9[0-8])\d{3}/", $query)) {
                                $arrayCleaned["postCode"] = min($resultsArrayGeoAPI["codesPostaux"]);
                            }                
                        }else if(isset($_GET["radius"]) && isset($_GET["searchBy"]) && $_GET["searchBy"] === "radius"){
                            $radius = intval($_GET["radius"]);
                            $long = $resultsArrayGeoAPI["centre"]["coordinates"][0];
                            $lat = $resultsArrayGeoAPI["centre"]["coordinates"][1];

                            $arrayCleaned["minLat"] = $lat-$radius/111;
                            $arrayCleaned["maxLat"] = $lat+$radius/111;
                            $arrayCleaned["minLong"] = $long-$radius/76;
                            $arrayCleaned["maxLong"] = $long+$radius/76;
                        }
                    }
                }
            break;

            case "searchAddress": //We need full address cleaned
                $resultsResponseDataAPI = wp_remote_get("https://api-adresse.data.gouv.fr/search/?q=$query&type=housenumber&limit=5");
                if(wp_remote_retrieve_response_code($resultsResponseDataAPI) === 200) {
                    $resultsBodyDataAPI = wp_remote_retrieve_body($resultsResponseDataAPI);
                    $resultsArrayDataAPI = json_decode($resultsBodyDataAPI, true);

                    foreach($resultsArrayDataAPI["features"] as $addresses) {
                        $resultsCleaned = array("address" => $addresses["properties"]["label"]);
                        array_push($arrayCleaned, $resultsCleaned);

                    }
                }
            break;

            case "saveAd": //We need the full address cleaned, coordinates (of the city or of the precise address), postcode and city name
                $resultsResponseDataAPI = wp_remote_get("https://api-adresse.data.gouv.fr/search/?q=$query&limit=1");
                if(wp_remote_retrieve_response_code($resultsResponseDataAPI) === 200) {
                    $resultsBodyDataAPI = wp_remote_retrieve_body($resultsResponseDataAPI);
                    $resultsArrayDataAPI = json_decode($resultsBodyDataAPI, true);

                    $address = $resultsArrayDataAPI["features"][0];
                    $arrayCleaned = array(
                        "address" => $address["properties"]["label"],                     
                        "postCode" => $address["properties"]["postcode"],
                        "city"  => $address["properties"]["city"]
                    );
                    $cityCode = $address["properties"]["citycode"];

                    if(isset($_GET["coordsApprox"])) {
                        $resultsReponseGeoAPI = wp_remote_get("https://geo.api.gouv.fr/communes/".$cityCode."?fields=mairie&limit=1");

                        if(wp_remote_retrieve_response_code($resultsReponseGeoAPI) === 200) {
                            $resultsBodyGeoAPI = wp_remote_retrieve_body($resultsReponseGeoAPI);
                            $resultsArrayGeoAPI = json_decode($resultsBodyGeoAPI, true);
                            $arrayCleaned["coordinates"] = array("long" => $resultsArrayGeoAPI["mairie"]["coordinates"][0], "lat" => $resultsArrayGeoAPI["mairie"]["coordinates"][1]);
                        }
                    }else{
                        $arrayCleaned["coordinates"] = array("long" => $address["geometry"]["coordinates"][0], "lat" => $address["geometry"]["coordinates"][1]);
                    }                   
                }
            break;
        }


        echo json_encode($arrayCleaned);
        
        
    }else if($apiUsed === "google") {
        $apiKeyGoogle = $apisOptions["apiKeyGoogle"];
        $country = $apisOptions["apiLimitCountry"];
        $arrayCleaned = array();
        
        switch($context) {
            case "searchBar": //We need city name and post code for results in searchbar
                $optionsApis = get_option(PLUGIN_RE_NAME."OptionsApis");
                $displayAdminLvl1 = $optionsApis["apiAdminAreaLvl1"] == 1;
                $displayAdminLvl2 = $optionsApis["apiAdminAreaLvl2"] == 1;
                $resultsResponsePlaceAPI = wp_remote_get("https://maps.googleapis.com/maps/api/place/autocomplete/json?input=$query&language=$country&types=locality|sublocality&components=country:$country&key=$apiKeyGoogle");
                if(wp_remote_retrieve_response_code($resultsResponsePlaceAPI) === 200) {
                    $resultsBodyPlaceAPI = wp_remote_retrieve_body($resultsResponsePlaceAPI);
                    $resultsArrayPlaceAPI = json_decode($resultsBodyPlaceAPI, true);
                    
                    foreach($resultsArrayPlaceAPI["predictions"] as $city) {
                        $resultsCleaned = array();
                        $query = $city["description"];
                        
                        $resultsResponseGeocodeAPI = wp_remote_get("https://maps.googleapis.com/maps/api/geocode/json?address=$query&language=$country&components=country:$country&key=$apiKeyGoogle");
                        if(wp_remote_retrieve_response_code($resultsResponseGeocodeAPI) === 200) {

                            $resultsBodyGeocodeAPI = wp_remote_retrieve_body($resultsResponseGeocodeAPI);
                            $resultsArrayGeocodeAPI = json_decode($resultsBodyGeocodeAPI, true);

                            $addressComponents = $resultsArrayGeocodeAPI["results"][0]["address_components"];
                            foreach($addressComponents as $comp) {
                                if(in_array("postal_code", $comp["types"], true)) {
                                    $resultsCleaned["postCode"] = $comp["long_name"];
                                }
                                else if(in_array("locality", $comp["types"], true)) {
                                    $resultsCleaned["city"] = $comp["long_name"];
                                }
                                else if($displayAdminLvl1 && in_array("administrative_area_level_1", $comp["types"], true)) {
                                    $resultsCleaned["adminLvl1"] = $comp["long_name"];
                                }else if($displayAdminLvl2 && in_array("administrative_area_level_2", $comp["types"], true)) {
                                    $resultsCleaned["adminLvl2"] = $comp["long_name"];
                                }
                            }//Don't keep if no PC ?
                            if(!empty($resultsCleaned)) {
                                array_push($arrayCleaned, $resultsCleaned);
                            }
                        }
                    }               
                }
            break;
        
            case "searchAds": ///We need the postcode + city or a perimeter in coordinates     
                $resultsResponseGeocodeAPI = wp_remote_get("https://maps.googleapis.com/maps/api/geocode/json?address=$query&language=$country&components=country:$country&key=$apiKeyGoogle");
                if(wp_remote_retrieve_response_code($resultsResponseGeocodeAPI) === 200) {

                    $resultsBodyGeocodeAPI = wp_remote_retrieve_body($resultsResponseGeocodeAPI);
                    $resultsArrayGeocodeAPI = json_decode($resultsBodyGeocodeAPI, true);    
                    
                    $addressComponents = $resultsArrayGeocodeAPI["results"][0]["address_components"];
                    
                    if(isset($_GET["searchBy"]) && $_GET["searchBy"] === "city") {
                        foreach($addressComponents as $comp) {
                            if(in_array("postal_code", $comp["types"], true)) { 
                                $arrayCleaned["postCode"] = $comp["long_name"];
                            }
                            else if(in_array("locality", $comp["types"], true)) {
                                $arrayCleaned["city"] = $comp["long_name"];
                            }
                        }            
                    }else if(isset($_GET["radius"]) && isset($_GET["searchBy"]) && $_GET["searchBy"] === "radius") {
                        $radius = intval($_GET["radius"]);
                        $long = $resultsArrayGeocodeAPI["results"][0]["geometry"]["location"]["lng"];
                        $lat = $resultsArrayGeocodeAPI["results"][0]["geometry"]["location"]["lat"];

                        $arrayCleaned["minLat"] = $lat-$radius/111;
                        $arrayCleaned["maxLat"] = $lat+$radius/111;
                        $arrayCleaned["minLong"] = $long-$radius/76;
                        $arrayCleaned["maxLong"] = $long+$radius/76;
                    }    
                }              
            break;
            
            case "searchAddress": //We need full address cleaned
                $resultsResponsePlaceAPI = wp_remote_get("https://maps.googleapis.com/maps/api/place/autocomplete/json?input=$query&language=$country&types=address&components=country:$country&key=$apiKeyGoogle");
                if(wp_remote_retrieve_response_code($resultsResponsePlaceAPI) === 200) {
                    $resultsBodyPlaceAPI = wp_remote_retrieve_body($resultsResponsePlaceAPI);
                    $resultsArrayPlaceAPI = json_decode($resultsBodyPlaceAPI, true);
                    
                    foreach($resultsArrayPlaceAPI["predictions"] as $address) {
                        $resultsCleaned = array();
                        
                        $resultsCleaned["address"] = str_replace(',', '', substr($address["description"], 0, strrpos($address["description"], ',')));
                        array_push($arrayCleaned, $resultsCleaned);
                    }
                }
            break;
            
            case "saveAd": //We need the full address cleaned, coordinates (of the city or of the precise address), postcode and city name
                $optionsApis = get_option(PLUGIN_RE_NAME."OptionsApis");
                $displayAdminLvl1 = $optionsApis["apiAdminAreaLvl1"] == 1;
                $displayAdminLvl2 = $optionsApis["apiAdminAreaLvl2"] == 1;
                $resultsResponseGeocodeAPI = wp_remote_get("https://maps.googleapis.com/maps/api/geocode/json?address=$query&language=$country&components=country:$country&key=$apiKeyGoogle");
                if(wp_remote_retrieve_response_code($resultsResponseGeocodeAPI) === 200) {
                    
                    $resultsBodyGeocodeAPI = wp_remote_retrieve_body($resultsResponseGeocodeAPI);
                    $resultsArrayGeocodeAPI = json_decode($resultsBodyGeocodeAPI, true);    

                    $addressComponents = $resultsArrayGeocodeAPI["results"][0]["address_components"];
                    
                    $arrayCleaned = array(
                        "streetNumber" => '',
                        "route" => '',
                        "city" => '',
                        "postCode" => '',
                        "adminLvl1" => '',
                        "adminLvl2" => ''
                    );                   
                    foreach($addressComponents as $comp) {                       
                        if(in_array("street_number", $comp["types"], true)) {                       
                            $arrayCleaned["streetNumber"] = $comp["long_name"];
                        }else if(in_array("route", $comp["types"], true)) {
                            $arrayCleaned["route"] = $comp["long_name"];
                        }else if(in_array("locality", $comp["types"], true)) {
                            $arrayCleaned["city"] = $comp["long_name"];                            
                        }else if(in_array("postal_code", $comp["types"], true)) { 
                            $arrayCleaned["postCode"] = $comp["long_name"];
                        }else if($displayAdminLvl2 && in_array("administrative_area_level_2", $comp["types"], true)) {
                            $arrayCleaned["adminLvl1"] = $comp["long_name"];
                        }else if($displayAdminLvl1 && in_array("administrative_area_level_1", $comp["types"], true)) {
                            $arrayCleaned["adminLvl2"] = $comp["long_name"];
                        }
                    }               
                    $arrayCleaned["address"] = rtrim(implode(' ', $arrayCleaned));
                    

                    if(isset($_GET["coordsApprox"])) { //To economize the API requests, using randomness instead of looking for the cityhall
                        $minLat = $resultsArrayGeocodeAPI["results"][0]["geometry"]["viewport"]["southwest"]["lat"]-0.001;
                        $minLong = $resultsArrayGeocodeAPI["results"][0]["geometry"]["viewport"]["southwest"]["lng"]-0.001;
                        $maxLat = $resultsArrayGeocodeAPI["results"][0]["geometry"]["viewport"]["northeast"]["lat"]+0.001;
                        $maxLong = $resultsArrayGeocodeAPI["results"][0]["geometry"]["viewport"]["northeast"]["lng"]+0.001;
                        $mult = 10000000;
                        
                        $randLat = mt_rand($minLat*$mult, $maxLat*$mult)/$mult;
                        $randLong = mt_rand($minLong*$mult, $maxLong*$mult)/$mult;
                        
                        $arrayCleaned["coordinates"] = array("long" => $randLong, "lat" => $randLat);          
                    }else{
                        $arrayCleaned["coordinates"] = array("long" => $resultsArrayGeocodeAPI["results"][0]["geometry"]["location"]["lng"], "lat" => $resultsArrayGeocodeAPI["results"][0]["geometry"]["location"]["lat"]);
                    }                   
                }
            break;  
        
        }
            echo json_encode($arrayCleaned);
        
        
    }

}