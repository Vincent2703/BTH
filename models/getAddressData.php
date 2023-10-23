<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
require_once preg_replace("/wp-content(?!.*wp-content).*/", "", __DIR__)."wp-load.php";

if(!function_exists("getAddressData")) {
    function getAddressData($data) {
        if($data->get_param("query") !== null && $data->get_param("context") !== null) {
            $apisOptions = get_option(PLUGIN_RE_NAME . "OptionsApis");
            $apiUsed = $apisOptions["apiUsed"];
            $query = urlencode(addslashes(sanitize_text_field(str_replace('+', ' ', $data->get_param("query")))));
            $context = sanitize_text_field($data->get_param("context"));

            if($apiUsed === "govFr") {
                $frAutocompleteAPI = "https://api-adresse.data.gouv.fr/search?";
                $frGeoAPI = "https://geo.api.gouv.fr/communes/";
                $arrayCleaned = [];
                switch($context) {
                    case "searchBar":
                        $params = array(
                            'q' => $query,
                            "type" => "municipality",
                            "limit" => 3
                        );
                        $queryURL = http_build_query($params);
                        $url = $frAutocompleteAPI.$queryURL;

                        $resultsResponseDataAPI = wp_safe_remote_get($url, array("timeout" => 10, "httpversion" => "1.1", "headers" => array("Content-Type" => "application/json; charset=utf-8")));
                        if(wp_remote_retrieve_response_code($resultsResponseDataAPI) === 200) {
                            $resultsBodyDataAPI = wp_remote_retrieve_body($resultsResponseDataAPI);
                            $resultsArrayDataAPI = json_decode($resultsBodyDataAPI, true);

                            foreach($resultsArrayDataAPI["features"] as $city) {
                                if(!preg_match('~[0-9]+~', $city["properties"]["city"])) {
                                    $resultsCleaned = [];
                                    $params = array(
                                        "fields" => "centre,codesPostaux",
                                        "limit" => 1
                                    );
                                    $queryURL = '?'.http_build_query($params);
                                    $url = $frGeoAPI.$city["properties"]["citycode"].$queryURL;
                                    $resultsReponseGeoAPI = wp_safe_remote_get($url, array("timeout" => 10, "httpversion" => "1.1", "headers" => array("Content-Type" => "application/json; charset=utf-8")));
                                    if(wp_remote_retrieve_response_code($resultsReponseGeoAPI) === 200) {
                                        $resultsBodyGeoAPI = wp_remote_retrieve_body($resultsReponseGeoAPI);
                                        $resultsArrayGeoAPI = json_decode($resultsBodyGeoAPI, true);

                                        $codesPostaux = $resultsArrayGeoAPI["codesPostaux"];
                                        preg_match('!\d+\.*\d*!', $query, $matches);
                                        if(!empty($matches)) {
                                            $CPQuery = $matches[0];
                                            $codesPostaux = preg_grep("/^$CPQuery/i", $codesPostaux);
                                        }
                                        $codesPostaux = array_slice(preg_grep("/^$CPQuery/i", $codesPostaux), 0, 5);

                                        foreach($codesPostaux as $CP) {
                                            $resultsCleaned["city"] = $city["properties"]["city"];
                                            $resultsCleaned["postCode"] = $CP;
                                            $resultsCleaned["long"] = $resultsArrayGeoAPI["centre"]["coordinates"][0];
                                            $resultsCleaned["lat"] = $resultsArrayGeoAPI["centre"]["coordinates"][1];

                                            array_push($arrayCleaned, $resultsCleaned);
                                        }

                                    }
                                }
                            }
                        }
                    break;
                    
                    case "searchAddress": //We need full address cleaned
                        $params = array(
                            'q' => $query,
                            "type" => "housenumber",
                            "limit" => 5
                        );
                        $queryURL = http_build_query($params);
                        $url = $frAutocompleteAPI.$queryURL;
                        $resultsResponseDataAPI = wp_safe_remote_get($url, array("timeout" => 10, "httpversion" => "1.1", "headers" => array("Content-Type" => "application/json; charset=utf-8")));
                        if(wp_remote_retrieve_response_code($resultsResponseDataAPI) === 200) {
                            $resultsBodyDataAPI = wp_remote_retrieve_body($resultsResponseDataAPI);
                            $resultsArrayDataAPI = json_decode($resultsBodyDataAPI, true);

                            foreach($resultsArrayDataAPI["features"] as $addresses) {
                                $resultsCleaned = ["address" => $addresses["properties"]["label"], ];
                                array_push($arrayCleaned, $resultsCleaned);
                            }
                        }
                    break;

                    case "saveAd": //We need the full address cleaned, coordinates (of the city or of the precise address), postcode and city name
                        $params = array(
                            'q' => $query,
                            "limit" => 1
                        );
                        $queryURL = http_build_query($params);
                        $url = $frAutocompleteAPI.$queryURL;
                        error_log($url);
                        $resultsResponseDataAPI = wp_safe_remote_get($url, array("timeout" => 10, "httpversion" => "1.1", "headers" => array("Content-Type" => "application/json; charset=utf-8")));
                        if(wp_remote_retrieve_response_code($resultsResponseDataAPI) === 200) {
                            $resultsBodyDataAPI = wp_remote_retrieve_body($resultsResponseDataAPI);
                            $resultsArrayDataAPI = json_decode($resultsBodyDataAPI, true);

                            $address = $resultsArrayDataAPI["features"][0];
                            $arrayCleaned = ["address" => $address["properties"]["label"], "city" => $address["properties"]["city"], ];
                            $cityCode = $address["properties"]["citycode"];

                            if($data->get_param("coordsApprox") !== null) {
                                $params = array(
                                    "fields" => "mairie,codesPostaux",
                                    "limit" => 1
                                );
                                $queryURL = '?'.http_build_query($params);
                                $url = $frGeoAPI.$cityCode.$queryURL;
                                $resultsReponseGeoAPI = wp_safe_remote_get($url, array("timeout" => 10, "httpversion" => "1.1", "headers" => array("Content-Type" => "application/json; charset=utf-8")));

                                if(wp_remote_retrieve_response_code($resultsReponseGeoAPI) === 200) {
                                    $resultsBodyGeoAPI = wp_remote_retrieve_body($resultsReponseGeoAPI);
                                    $resultsArrayGeoAPI = json_decode($resultsBodyGeoAPI, true);
                                    $arrayCleaned["coordinates"] = ["long" => $resultsArrayGeoAPI["mairie"]["coordinates"][0], "lat" => $resultsArrayGeoAPI["mairie"]["coordinates"][1], ];
                                    $arrayCleaned["postCode"] = min($resultsArrayGeoAPI["codesPostaux"]);
                                }
                            }else{
                                $arrayCleaned["coordinates"] = ["long" => $address["geometry"]["coordinates"][0], "lat" => $address["geometry"]["coordinates"][1], ];
                                $arrayCleaned["postCode"] = $address["properties"]["postcode"];
                            }
                        }
                    break;
                }

            }else if($apiUsed === "google") {
                $apiKeyGoogle = $apisOptions["apiKeyGoogle"];
                $googleAutocompleteAPI = "https://maps.googleapis.com/maps/api/place/autocomplete/json?key=$apiKeyGoogle&";
                $googleGeoAPI = "https://maps.googleapis.com/maps/api/geocode/json?key=$apiKeyGoogle&";
                $country = $apisOptions["apiLimitCountry"]??'';
                $language = $apisOptions["apiLanguage"]??'';
                $arrayCleaned = [];

                switch($context) {
                    case "searchBar": //We need city name and post code for results in searchbar
                        $optionsApis = get_option(PLUGIN_RE_NAME . "OptionsApis");
                        $displayAdminLvl1 = $optionsApis["apiAdminAreaLvl1"] == 1;
                        $displayAdminLvl2 = $optionsApis["apiAdminAreaLvl2"] == 1;
                        $params = array(
                            "input" => $query,
                            "types" => "locality|sublocality|postal_code"
                        );
                        $params["language"] = $language;
                        $params["components"] = "country:$country";
                        
                        $queryURL = http_build_query($params);
                        $url = $googleAutocompleteAPI.$queryURL;
                        $resultsResponsePlaceAPI = wp_safe_remote_get($url, array("timeout" => 10, "httpversion" => "1.1", "headers" => array("Content-Type" => "application/json; charset=utf-8")));
                        if(wp_remote_retrieve_response_code($resultsResponsePlaceAPI) === 200) {
                            $resultsBodyPlaceAPI = wp_remote_retrieve_body($resultsResponsePlaceAPI);
                            $resultsArrayPlaceAPI = json_decode($resultsBodyPlaceAPI, true);

                            foreach($resultsArrayPlaceAPI["predictions"] as $city) {
                                $resultsCleaned = [];
                                $query = $city["description"];
                                
                                $params = array(
                                    "address" => $query
                                );
                                $params["language"] = $language;
                                $params["components"] = "country:$country";
                                
                                $queryURL = http_build_query($params);

                                $url = $googleGeoAPI.$queryURL;

                                $resultsResponseGeocodeAPI = wp_safe_remote_get($url, array("timeout" => 10, "httpversion" => "1.1", "headers" => array("Content-Type" => "application/json; charset=utf-8")));
                                if(wp_remote_retrieve_response_code($resultsResponseGeocodeAPI) === 200) {
                                    $resultsBodyGeocodeAPI = wp_remote_retrieve_body($resultsResponseGeocodeAPI);
                                    $resultsArrayGeocodeAPI = json_decode($resultsBodyGeocodeAPI, true);

                                    $addressComponents = $resultsArrayGeocodeAPI["results"][0]["address_components"];
                                    foreach($addressComponents as $comp) {
                                        if(in_array("postal_code", $comp["types"], true)) {
                                            $resultsCleaned["postCode"] = $comp["long_name"];
                                        }else if(in_array("locality", $comp["types"], true)) {
                                            $resultsCleaned["city"] = $comp["long_name"];
                                        }else if($displayAdminLvl1 && in_array("administrative_area_level_1", $comp["types"], true)) {
                                            $resultsCleaned["adminLvl1"] = $comp["long_name"];
                                        }else if($displayAdminLvl2 && in_array("administrative_area_level_2", $comp["types"], true)) {
                                            $resultsCleaned["adminLvl2"] = $comp["long_name"];
                                        }
                                    }
                                    if(isset($resultsArrayGeocodeAPI["results"][0]["geometry"]["location"])) {
                                        $resultsCleaned["lat"] = $resultsArrayGeocodeAPI["results"][0]["geometry"]["location"]["lat"];
                                        $resultsCleaned["long"] = $resultsArrayGeocodeAPI["results"][0]["geometry"]["location"]["lng"];
                                    }
                                        //Don't keep if no city/lat/long
                                    if(isset($resultsCleaned["city"]) && isset($resultsCleaned["lat"]) && isset($resultsCleaned["long"])) {
                                        array_push($arrayCleaned, $resultsCleaned);
                                    }
                                }
                            }
                        }
                    break;

                    case "searchAddress": //We need full address cleaned
                        $params = array(
                            "input" => $query,
                            "types" => "address"
                        );
                        $params["language"] = $country;
                        $params["components"] = "country:$country";
                        
                        $queryURL = http_build_query($params);
                        $url = $googleAutocompleteAPI.$queryURL;
                        
                        $resultsResponsePlaceAPI = wp_safe_remote_get($url, array("timeout" => 10, "httpversion" => "1.1", "headers" => array("Content-Type" => "application/json; charset=utf-8")));
                        if(wp_remote_retrieve_response_code($resultsResponsePlaceAPI) === 200) {
                            $resultsBodyPlaceAPI = wp_remote_retrieve_body($resultsResponsePlaceAPI);
                            $resultsArrayPlaceAPI = json_decode($resultsBodyPlaceAPI, true);

                            foreach($resultsArrayPlaceAPI["predictions"] as $address) {
                                $resultsCleaned = [];

                                $resultsCleaned["address"] = str_replace(",", "", substr($address["description"], 0, strrpos($address["description"], ",")));
                                $resultsCleaned["placeId"] = $address["place_id"];
                                array_push($arrayCleaned, $resultsCleaned);
                            }
                        }
                    break;

                    case "saveAd": //We need the full address cleaned, coordinates (of the city or of the precise address), postcode and city name
                        $placeId = $data->get_param("placeId");
                        $optionsApis = get_option(PLUGIN_RE_NAME . "OptionsApis");
                        $displayAdminLvl1 = $optionsApis["apiAdminAreaLvl1"] == 1;
                        $displayAdminLvl2 = $optionsApis["apiAdminAreaLvl2"] == 1;
                        
                        $params = array(
                            "key" => $apiKeyGoogle,
                        );
                        if($placeId != null) {
                            $params["place_id"] = $placeId;
                        }else{
                            $params["address"] = $query;
                        }
                        $params["language"] = $language;
                        $params["components"] = "country:$country";
                        
                        $queryURL = http_build_query($params);
                        $url = $googleGeoAPI.$queryURL;
                        $resultsResponseGeocodeAPI = wp_safe_remote_get($url, array("timeout" => 10, "httpversion" => "1.1", "headers" => array("Content-Type" => "application/json; charset=utf-8")));
                        if(wp_remote_retrieve_response_code($resultsResponseGeocodeAPI) === 200) {
                            $resultsBodyGeocodeAPI = wp_remote_retrieve_body($resultsResponseGeocodeAPI);
                            $resultsArrayGeocodeAPI = json_decode($resultsBodyGeocodeAPI, true);

                            $addressComponents = $resultsArrayGeocodeAPI["results"][0]["address_components"];

                            $arrayCleaned = ["streetNumber" => "", "route" => "", "city" => "", "postCode" => "", "adminLvl1" => "", "adminLvl2" => ""];
                            foreach ($addressComponents as $comp) {
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
                            $arrayCleaned["address"] = rtrim(implode(" ", $arrayCleaned));

                            if($data->get_param("coordsApprox") !== null) {
                                //To economize the API requests, using randomness instead of looking for the cityhall
                                $minLat = $resultsArrayGeocodeAPI["results"][0]["geometry"]["viewport"]["southwest"]["lat"] - 0.001;
                                $minLong = $resultsArrayGeocodeAPI["results"][0]["geometry"]["viewport"]["southwest"]["lng"] - 0.001;
                                $maxLat = $resultsArrayGeocodeAPI["results"][0]["geometry"]["viewport"]["northeast"]["lat"] + 0.001;
                                $maxLong = $resultsArrayGeocodeAPI["results"][0]["geometry"]["viewport"]["northeast"]["lng"] + 0.001;
                                $mult = 10000000;

                                $randLat = mt_rand($minLat * $mult, $maxLat * $mult) / $mult;
                                $randLong = mt_rand($minLong * $mult, $maxLong * $mult) / $mult;

                                $arrayCleaned["coordinates"] = ["long" => $randLong, "lat" => $randLat];
                            }
                            else {
                                error_log("pas approx");
                                $arrayCleaned["coordinates"] = ["long" => $resultsArrayGeocodeAPI["results"][0]["geometry"]["location"]["lng"], "lat" => $resultsArrayGeocodeAPI["results"][0]["geometry"]["location"]["lat"]];
                            }
                        }
                    break;
                }          
            }
            return $arrayCleaned;
        }/*else{
            if(!$clientAllowed) {
                return new WP_Error("maxNbRequests", "The maximum number of requests to the API has been reach by the visitor", array("status"=>"429"));
            }else{
                return new WP_Error("invalidQuery", "The query's parameters are invalid", array("status"=>"400"));
            }
        }*/
    }
}
