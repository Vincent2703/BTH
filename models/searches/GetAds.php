<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
class REALM_GetAds {    
    /*
     * Search an ad in the front end
     */
    public function getAds($query) {
        if(!is_admin() && $query->is_search && isset($_GET["post_type"]) && $_GET["post_type"] === "re-ad") {        
            $query->set("post_type", "re-ad");
            
            $terms = array();
            $metas = array();
            
            if(isset($_GET["typeAd"]) && !empty($_GET["typeAd"])) {
                array_push($terms,
                    array(
                        "taxonomy" => "adTypeAd",
                        "field" => "slug",
                        "terms" => sanitize_text_field($_GET["typeAd"])
                    )
                );            
            }
            if(isset($_GET["typeProperty"]) && !empty($_GET["typeProperty"])) {
                array_push($terms,
                    array(
                        "taxonomy" => "adTypeProperty",
                        "field" => "slug",
                        "terms" => sanitize_text_field($_GET["typeProperty"])
                    )
                );
            }
            if(isset($_GET["minSurface"]) && isset($_GET["maxSurface"]) && intval($_GET["maxSurface"]) !== 0) {
                array_push($metas,
                    array(
                        "key" => "adSurface",
                        "value" => array(intval($_GET["minSurface"]), intval($_GET["maxSurface"])),
                        "compare" => "BETWEEN",
                        "type" => "DECIMAL"
                    )
                );
            }else if(isset($_GET["minSurface"]) && intval($_GET["minSurface"]) !== 0) {
                array_push($metas,
                    array(
                        "key" => "adSurface",
                        "value" => intval($_GET["minSurface"]),
                        "compare" => ">=",
                        "type" => "DECIMAL"
                    )
                );
            }        
            if(isset($_GET["minPrice"]) && isset($_GET["maxPrice"]) && intval($_GET["maxPrice"]) !== 0) {
                array_push($metas,
                    array(
                        "key" => "adPrice",
                        "value" => array(intval($_GET["minPrice"]), intval($_GET["maxPrice"])),
                        "compare" => "BETWEEN",
                        "type" => "DECIMAL"
                    )
                );
            }else if(isset($_GET["minPrice"]) && intval($_GET["minPrice"]) !== 0) {
                array_push($metas,
                    array(
                        "key" => "adPrice",
                        "value" => intval($_GET["minPrice"]),
                        "compare" => ">=",
                        "type" => "DECIMAL"
                    )
                );
            }
            if(isset($_GET["nbRooms"]) && intval($_GET["nbRooms"]) !== 0) {
                array_push($metas,
                    array(
                        "key" => "adNbRooms",
                        "value" => intval($_GET["nbRooms"]),
                        "compare" => ">=",
                        "type" => "NUMERIC"
                    )
                );
            } 
            if(isset($_GET["nbBedrooms"]) && intval($_GET["nbBedrooms"]) !== 0) {
                array_push($metas,
                    array(
                        "key" => "adNbBedrooms",
                        "value" => intval($_GET["nbBedrooms"]),
                        "compare" => ">=",
                        "type" => "NUMERIC"
                    )
                );
            } 
            if(isset($_GET["nbBathrooms"]) && intval($_GET["nbBathrooms"]) !== 0) {
                array_push($metas,
                    array(
                        "key" => "adNbBathWaterRooms",
                        "value" => intval($_GET["nbBathrooms"]),
                        "compare" => ">=",
                        "type" => "NUMERIC"
                    )
                );
            } 
            if(isset($_GET["furnished"]) && $_GET["furnished"] === "on") {
                array_push($metas,
                    array(
                        "key" => "adFurnished",
                        "value" => '1',
                        "type" => "NUMERIC"
                    )
                );
            }
            if(isset($_GET["land"]) && $_GET["land"] === "on") {
                array_push($metas,
                    array(
                        "key" => "adLandSurface",
                        "value" => '0',
                        "compare" => ">",
                        "type" => "NUMERIC"
                    )
                );
            }
            if(isset($_GET["cellar"]) && $_GET["cellar"] === "on") {
                array_push($metas,
                    array(
                        "key" => "adCellar",
                        "value" => '1',
                        "type" => "NUMERIC"
                    )
                );
            }
            if(isset($_GET["terrace"]) && $_GET["terrace"] === "on") {
                array_push($metas,
                    array(
                        "key" => "adTerrace",
                        "value" => '1',
                        "type" => "NUMERIC"
                    )
                );
            }
            if(isset($_GET["elevator"]) && $_GET["elevator"] === "on") {
                array_push($metas,
                    array(
                        "key" => "adElevator",
                        "value" => '1',
                        "type" => "NUMERIC"
                    )
                );
            }
            
            if(isset($_GET["city"]) && !empty($_GET["city"])) {
                $nonce = wp_create_nonce("apiAddress"); //Would be probably MUCH better in a hidden field, TODO ?
                if(isset($_GET["searchBy"]) && $_GET["searchBy"] === "city") {
                    $url = urlencode(get_rest_url(null, PLUGIN_RE_NAME."/v1/address") ."?query=".sanitize_text_field($_GET["city"])."&context=searchAds&searchBy=city&nonce=$nonce");
                    $addressData = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true);
                    
                    if(isset($addressData["city"])) {
                        array_push($metas,
                            array(
                                "key" => "adCity",
                                "value" => $addressData["city"],
                                "compare" => "IN"
                            )    
                        );
                    }else{
                        array_push($metas,
                            array(
                                "key" => "adCity",
                                "value" => $_GET["city"],
                                "compare" => "IN"
                            )    
                        );
                    }
                    
                    if(isset($addressData["postCode"])) {
                        array_push($metas,
                            array(
                                "key" => "adPostCode",
                                "value" => $addressData["postCode"],
                            )
                        );
                    }
                }else if(isset($_GET["radius"]) && isset($_GET["searchBy"]) && $_GET["searchBy"] === "radius"){ 
                    $url = get_rest_url(null, PLUGIN_RE_NAME."/v1/address")."?query=".sanitize_text_field($_GET["city"])."&context=searchAds&searchBy=radius&radius=".intval($_GET["radius"])."&nonce=$nonce";
                    $addressData = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true);
                    array_push($metas,
                        array(
                            "key" => "adLatitude",
                            "value" => array($addressData["minLat"], $addressData["maxLat"]),
                            "compare" => "BETWEEN"
                        )
                    );
                    array_push($metas,
                        array(
                            "key" => "adLongitude",
                            "value" => array($addressData["minLong"], $addressData["maxLong"]),
                            "compare" => "BETWEEN"
                        )
                    );
                }
            }                         
            if(!empty($terms)) {
                $query->set("tax_query", array($terms));
            }
            if(!empty($metas)) {
                $query->set("meta_query", array($metas));
            }
        }
    }
}
