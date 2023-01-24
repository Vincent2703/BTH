<?php
require_once(preg_replace('/wp-content(?!.*wp-content).*/', '', __DIR__ )."wp-load.php");
if(/*isset($_GET["query"]) && isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest"*/true) {
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
                            $resultsCleaned["postcode"] = min($resultsArrayGeoAPI["codesPostaux"]);
                        }
                        array_push($arrayCleaned, $resultsCleaned);
                    }

                }
            break;
            
            case "searchAds": ///We need the postcode or a perimeter in coordinates                
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
                $resultsResponseDataAPI = wp_remote_get("https://api-adresse.data.gouv.fr/search/?q=$query&limit=5");
                if(wp_remote_retrieve_response_code($resultsResponseDataAPI) === 200) {
                    $resultsBodyDataAPI = wp_remote_retrieve_body($resultsResponseDataAPI);
                    $resultsArrayDataAPI = json_decode($resultsBodyDataAPI, true);

                    foreach($resultsArrayDataAPI["features"] as $addresses) {
                        $resultsCleaned = array("address" => $addresses["properties"]["label"]);
                        array_push($arrayCleaned, $resultsCleaned);

                    }
                }
            break;

            case "saveAd": //We need full adress cleaned, coordinates (of the city or of the precise address)n postcode and city name
                $resultsResponseDataAPI = wp_remote_get("https://api-adresse.data.gouv.fr/search/?q=$query&limit=1");
                if(wp_remote_retrieve_response_code($resultsResponseDataAPI) === 200) {
                    $resultsBodyDataAPI = wp_remote_retrieve_body($resultsResponseDataAPI);
                    $resultsArrayDataAPI = json_decode($resultsBodyDataAPI, true);

                    $address = $resultsArrayDataAPI["features"][0];
                    $arrayCleaned = array(
                        "address" => $address["properties"]["label"],                     
                        "postcode" => $address["properties"]["postcode"],
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
                $resultsResponsePlaceAPI = wp_remote_get("https://maps.googleapis.com/maps/api/place/autocomplete/json?input=$query&language=$country&types=geocode&components=country:$country&key=$apiKeyGoogle");
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

                            $adressComponents = $resultsArrayGeocodeAPI["results"][0]["address_components"];
                            foreach($adressComponents as $comp) {
                                if($comp["types"][0] === "postal_code") {
                                    $resultsCleaned["postcode"] = $comp["long_name"];
                                }
                                else if($comp["types"][0] === "locality") {
                                    $resultsCleaned["city"] = $comp["long_name"];
                                }
                            }
                            if(!empty($resultsCleaned)) {
                                array_push($arrayCleaned, $resultsCleaned);
                            }
                        }
                    }               
                }
            break;
        
            case "searchAds": ///We need the postcode or a perimeter in coordinates            
            break;
            
            case "searchAddress": //We need full address cleaned
            break;
            
            case "saveAd": //We need full adress cleaned, coordinates (of the city or of the precise address)n postcode and city name
            break;
        
        
        /*if(!isset($_GET["city"])) {
            $resultsResponse = wp_remote_get("https://maps.googleapis.com/maps/api/place/autocomplete/json?input=$query&language=$country&types=address&components=country:$country&key=$apiKeyGoogle");
        }else{
            $resultsResponse = wp_remote_get("https://maps.googleapis.com/maps/api/place/autocomplete/json?input=$query&language=$country&types=%28regions%29&components=country:$country&key=$apiKeyGoogle");
        }

        if(wp_remote_retrieve_response_code($resultsResponse) === 200) {
            $resultsBody = wp_remote_retrieve_body($resultsResponse);
            $resultsArray = json_decode($resultsBody, true);

            $arrayCleaned = array();

            foreach($resultsArray["predictions"] as $feature) {
                $resultsCleaned = array();
                $idPlace = $feature["place_id"];

                if(isset($_GET["city"])) {
                    $address = $feature["structured_formatting"]["main_text"];
                }else{
                    $address = substr($feature["description"], 0, strrpos($feature["description"], ',')); //Pour avoir le nom de la ville + CP sans le pays
                }

                $resultsCleaned["address"] = $address;

                $resultsCleaned["city"] = $feature["terms"][count($feature["terms"])-2]["value"];

                if(isset($_GET["import"]) || isset($_GET["city"])) { //On a besoin des coords GPS et du CP uniquement quand on cherche une ville ou qu'on importe
                    $resultsDetailsResponse = wp_remote_get("https://maps.googleapis.com/maps/api/place/details/json?fields=geometry%2Caddress_component&place_id=$idPlace&key=$apiKeyGoogle");
                    if(wp_remote_retrieve_response_code($resultsDetailsResponse) === 200) {
                        $resultsDetailsBody = wp_remote_retrieve_body($resultsDetailsResponse);
                        $resultsDetailsArray = json_decode($resultsDetailsBody, true);

                        $coords = array_values($resultsDetailsArray["result"]["geometry"]["location"]);
                        $lat = $coords[0];
                        $lng = $coords[1];

                        $resultsCleaned["coordinates"] = array_reverse($coords);

                        $resultsGeoCodeResponse = wp_remote_get("https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lng&result_type=postal_code&key=$apiKeyGoogle");
                        if(wp_remote_retrieve_response_code($resultsGeoCodeResponse) === 200) {
                            $resultsGeoCodeBody = wp_remote_retrieve_body($resultsGeoCodeResponse);
                            $resultsGeoCodeArray = json_decode($resultsGeoCodeBody, true);
                            $resultsCleaned["postcode"] = $resultsGeoCodeArray["results"][0]["address_components"][0]["long_name"];
                            /*foreach($resultsGeoCodeArray["results"][0]["address_components"] as $addressComp) {
                                if(empty(array_diff($addressComp["types"], array("sublocality_level_1", "neighborhood", "locality", "postal_town", "political")))) {
                                    $resultsCleaned["city"] = $addressComp["long_name"];
                                }
                            } Marche pas si cp contient plusieurs communes*
                        }           
                    }
                }
                array_push($arrayCleaned, $resultsCleaned);                
            }*/
        }
            echo json_encode($arrayCleaned);
        
        
    }

}