<?php
require_once(preg_replace('/wp-content(?!.*wp-content).*/', '', __DIR__ )."wp-load.php");

if(isset($_GET["query"])) {
    $apisOptions = get_option(PLUGIN_RE_NAME."OptionsApis");
    $apiUsed = $apisOptions["apiUsed"];

    $query = urlencode(addslashes(sanitize_text_field($_GET["query"])));
    
    
    switch($apiUsed) {
        case "govFr":
            if(!isset($_GET["city"])) {
                $resultsResponse = wp_remote_get("https://api-adresse.data.gouv.fr/search/?q=$query&limit=5");
            }else{
                $resultsResponse = wp_remote_get("https://api-adresse.data.gouv.fr/search/?q=$query&type=municipality&limit=5");
            }
            
            if(wp_remote_retrieve_response_code($resultsResponse) === 200) {
                $resultsBody = wp_remote_retrieve_body($resultsResponse);
                $resultsArray = json_decode($resultsBody, true);

                $arrayCleaned = array();
                

                foreach($resultsArray["features"] as $feature) {
                    
                    $resultsPCReponse = wp_remote_get("https://geo.api.gouv.fr/communes/".$feature["properties"]["citycode"]."?fields=codesPostaux&limit=1");
                    
                    if(wp_remote_retrieve_response_code($resultsPCReponse) === 200) {
                        $resultsPCBody = wp_remote_retrieve_body($resultsPCReponse);
                        $resultsPCArray = json_decode($resultsPCBody, true);
                        
                        $resultsCleaned = array();
                        if(isset($_GET["import"]) || isset($_GET["city"])) { //On a besoin du code postal et/ou du nom de la ville uniquement quand on importe ou qu'on cherche une ville
                            $resultsCleaned["postcode"] = min($resultsPCArray["codesPostaux"]);
                            $resultsCleaned["city"] = $feature["properties"]["city"];
                        }
                        $resultsCleaned["address"] = $feature["properties"]["label"];
                        $resultsCleaned["coordinates"] = $feature["geometry"]["coordinates"]; //OK pour les coordonnées vu que ça ne coûte rien en plus
                        array_push($arrayCleaned, $resultsCleaned);
                    }
                }

                echo json_encode($arrayCleaned);
            }
        break;
        case "google":
            $apiKeyGoogle = $apisOptions["apiKeyGoogle"];
            $country = $apisOptions["apiLimitCountry"];
            if(!isset($_GET["city"])) {
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
                                } Marche pas si cp contient plusieurs communes*/ 
                            }           
                        }
                    }
                    array_push($arrayCleaned, $resultsCleaned);                
                }
            
                echo json_encode($arrayCleaned);
            }
        break;
    }

}