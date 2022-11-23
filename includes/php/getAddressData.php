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
                    
                    //On a besoin du code postal uniquement quand on importe
                    if(wp_remote_retrieve_response_code($resultsPCReponse) === 200) {
                        $resultsPCBody = wp_remote_retrieve_body($resultsPCReponse);
                        $resultsPCArray = json_decode($resultsPCBody, true);
                        array_push($arrayCleaned, array(
                                "coordinates"=>$feature["geometry"]["coordinates"],
                                "address"=>$feature["properties"]["label"],
                                //"postcode"=>$feature["properties"]["postcode"] Donne code postal erroné si la ville en a plusieurs (ne donne pas le principal)
                                "postcode"=>min($resultsPCArray["codesPostaux"])
                                )
                        );
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
                    $idPlace = $feature["place_id"];
                    //On a besoin des coords GPS et du CP uniquement quand on cherche une ville ou qu'on importe
                    $resultsDetailsResponse = wp_remote_get("https://maps.googleapis.com/maps/api/place/details/json?fields=geometry%2Caddress_component&place_id=$idPlace&key=$apiKeyGoogle");
                    if(wp_remote_retrieve_response_code($resultsDetailsResponse) === 200) {
                        $resultsDetailsBody = wp_remote_retrieve_body($resultsDetailsResponse);
                        $resultsDetailsArray = json_decode($resultsDetailsBody, true);
                        
                        $coords = array_values($resultsDetailsArray["result"]["geometry"]["location"]);
                        $lat = $coords[0];
                        $lng = $coords[1];
                        
                        //On a besoin du code postal uniquement quand on importe
                        $resultsGeoCodeResponse = wp_remote_get("https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lng&result_type=postal_code&key=$apiKeyGoogle");
                        if(wp_remote_retrieve_response_code($resultsGeoCodeResponse) === 200) {
                            $resultsGeoCodeBody = wp_remote_retrieve_body($resultsGeoCodeResponse);
                            $resultsGeoCodeArray = json_decode($resultsGeoCodeBody, true);
                            
                            if(isset($_GET["city"])) {
                                $address = $feature["structured_formatting"]["main_text"];
                            }else{
                                $address = substr($feature["description"], 0, strrpos($feature["description"], ','));
                            }

                            array_push($arrayCleaned, array(
                                    "coordinates"=>array_reverse($coords),
                                    "address"=>$address,
                                    "postcode"=>$resultsGeoCodeArray["results"][0]["address_components"][0]["long_name"] 
                                    )
                            );
                        }
                        
                    }
                }
            
                echo json_encode($arrayCleaned);
            }
        break;
    }

}