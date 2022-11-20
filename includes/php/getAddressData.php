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
                    array_push($arrayCleaned, array(
                            "coordinates"=>$feature["geometry"]["coordinates"],
                            "address"=>$feature["properties"]["label"],
                            "postcode"=>$feature["properties"]["postcode"] //Faire comme il a dit le monsieur dans le mail
                            )
                    );
                }

                echo json_encode($arrayCleaned);
            }
        break;
        case "google":
            $apiKeyGoogle = $apisOptions["apiKeyGoogle"];
            $country = $apisOptions["apiLimitCountry"];
            if(!isset($_GET["city"])) {
                $resultsResponse = wp_remote_get("https://maps.googleapis.com/maps/api/place/autocomplete/json?input=$query&types=address&components=country:$country&key=$apiKeyGoogle");
            }else{
                $resultsResponse = wp_remote_get("https://maps.googleapis.com/maps/api/place/autocomplete/json?input=$query&types=%28cities%29&components=country:$country&key=$apiKeyGoogle");
            }
            
            if(wp_remote_retrieve_response_code($resultsResponse) === 200) {
                $resultsBody = wp_remote_retrieve_body($resultsResponse);
                $resultsArray = json_decode($resultsBody, true);

                $arrayCleaned = array();

                foreach($resultsArray["predictions"] as $feature) {
                    $idPlace = $feature["place_id"];
                    $resultsDetailsResponse = wp_remote_get("https://maps.googleapis.com/maps/api/place/details/json?fields=geometry%2Caddress_component&place_id=$idPlace&key=$apiKeyGoogle");
                    if(wp_remote_retrieve_response_code($resultsDetailsResponse) === 200) {
                        $resultsDetailsBody = wp_remote_retrieve_body($resultsDetailsResponse);
                        $resultsDetailsArray = json_decode($resultsDetailsBody, true);

                        array_push($arrayCleaned, array(
                                "coordinates"=>array_reverse(array_values($resultsDetailsArray["result"]["geometry"]["location"])),
                                "address"=>$feature["structured_formatting"]["main_text"],
                                "postcode"=>end($resultsDetailsArray["result"]["address_components"])["long_name"] //IL FAUT A PARTIR DES COORDS, TROUVER LE CODE POSTAL ex : https://maps.googleapis.com/maps/api/geocode/json?latlng=47.394144,0.68484&result_type=postal_code&key=AIzaSyCygYiQC-FnF4Tln3BT-ZUTb8GS0MLnMEU
                                )
                        );
                    }
                }
            
                echo json_encode($arrayCleaned);
            }
        break;
    }

}