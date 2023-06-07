<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Get and set ad meta values for the admin front-end
 * 
 */
class REALM_AdAdmin {
    private static $metas;
    
    public static $refAgency;
    public static $price;
    public static $fees;
    public static $surface;
    public static $landSurface;
    public static $nbRooms;
    public static $nbBedrooms;
    public static $nbBathrooms;
    public static $nbWaterRooms;
    public static $nbWC;
    public static $address;
    public static $showMap;
    public static $images;
    public static $allAgents;
    public static $idAgent;
    public static $showAgent;
    public static $customFieldsMF;
    
    public static $floor;
    public static $nbFloors;
    public static $furnished;
    public static $year;
    public static $typeHeating;
    public static $typeKitchen;
    public static $nbBalconies;
    public static $elevator;
    public static $basement;
    public static $terrace;
    public static $DPE;
    public static $GES;
    public static $customFieldsAF;
    
       
    public static function getMainFeatures($id) {
        self::$metas = get_post_custom($id);
        
        self::$refAgency = sanitize_text_field(self::getMeta("adRefAgency"));
        self::$price = absint(self::getMeta("adPrice"));
        self::$fees = absint(self::getMeta("adFees"));
        self::$surface = sanitize_text_field(self::getMeta("adSurface"));
        self::$landSurface = sanitize_text_field(self::getMeta("adLandSurface"));
        self::$nbRooms = absint(self::getMeta("adNbRooms"));
        self::$nbBedrooms = absint(self::getMeta("adNbBedrooms"));
        self::$nbBathrooms = absint(self::getMeta("adNbBathrooms"));
        self::$nbWaterRooms = absint(self::getMeta("adNbWaterRooms"));
        self::$nbWC = absint(self::getMeta("adNbWC"));
        self::$address = sanitize_text_field(self::getMeta("adAddress"));
        self::$showMap = sanitize_text_field(self::getMeta("adShowMap"));
        self::$images = sanitize_text_field(self::getMeta("adImages"));
        self::$allAgents = get_posts(array("post_type" => "agent", "numberposts" => 99));
        self::$idAgent = absint(self::getMeta("adIdAgent"));
        self::$showAgent = boolval(self::getMeta("adShowAgent"));
        
        //Custom fields
        $optionsGeneral = get_option(PLUGIN_RE_NAME."OptionsGeneral");
        if($optionsGeneral !== false && isset($optionsGeneral["customFields"])) {
            $customFields = $optionsGeneral["customFields"];
            self::$customFieldsMF = array();
            if(!empty($customFields) || $customFields !== "[]") {
                $customFields = json_decode($customFields, true);
                foreach($customFields as $field) {
                    if($field["section"] === "mainFeatures") {
                        self::$customFieldsMF[sanitize_text_field($field["name"])] = array("nameAttr"=>$field["nameAttr"], "value"=>sanitize_text_field(self::getMeta("adCF".$field["nameAttr"])));
                    }
                }
            }
        }
    }
    
    public static function getAdditionalFeatures($id) {
        self::$metas = get_post_custom($id);
        
        self::$floor = absint(self::getMeta("adFloor"));
        self::$nbFloors = absint(self::getMeta("adNbFloors"));
        self::$furnished = boolval(self::getMeta("adFurnished"));
        self::$year = absint(self::getMeta("adYear"));
        self::$typeHeating = sanitize_text_field(self::getMeta("adTypeHeating"));
        self::$typeKitchen = sanitize_text_field(self::getMeta("adTypeKitchen"));
        self::$nbBalconies = absint(self::getMeta("adNbBalconies"));
        self::$elevator = boolval(self::getMeta("adElevator"));
        self::$basement = boolval(self::getMeta("adCellar"));
        self::$terrace = boolval(self::getMeta("adTerrace"));
        self::$DPE = absint(self::getMeta("adDPE"));
        self::$GES = absint(self::getMeta("adGES"));
        
        $optionsGeneral = get_option(PLUGIN_RE_NAME."OptionsGeneral");
        if($optionsGeneral !== false && isset($optionsGeneral["customFields"])) {
            $customFields = $optionsGeneral["customFields"];
            self::$customFieldsAF = array();
            if(!empty($customFields) || $customFields !== "[]") {
                $customFields = json_decode($customFields, true);
                foreach($customFields as $field) {
                    if($field["section"] === "additionalFeatures") {
                        self::$customFieldsAF[sanitize_text_field($field["name"])] = array("nameAttr"=>$field["nameAttr"], "value"=>sanitize_text_field(self::getMeta("adCF".$field["nameAttr"])));
                    }
                }
            }
        }
    }
    
    //Save data in BDD
    public static function setData($adId, $ad) {
        $ad->post_title = substr(sanitize_text_field($ad->postTitle), 0, 64);

        if(isset($_POST["adTypeProperty"]) && !ctype_space($_POST["adTypeProperty"])) {
            self::saveTaxonomy($adId, "adTypeProperty");
        }
        if(isset($_POST["adTypeAd"]) && !ctype_space($_POST["adTypeAd"])) {
            self::saveTaxonomy($adId, "adTypeAd");
        }
        if(isset($_POST["adAvailable"]) && $_POST["adAvailable"] === "available") {
            self::saveTaxonomyAdAvailable($adId, "available");
        }else{
            self::saveTaxonomyAdAvailable($adId, "unavailable");
        }            

        if(isset($_POST["refAgency"]) && !ctype_space($_POST["refAgency"])) {
            update_post_meta($adId, "adRefAgency", sanitize_text_field($_POST["refAgency"]));
        }
        if(isset($_POST["price"]) && is_numeric($_POST["price"])) {
            update_post_meta($adId, "adPrice", absint($_POST["price"]));
        }
        if(isset($_POST["fees"]) && is_numeric($_POST["fees"])) {
            update_post_meta($adId, "adFees", absint($_POST["fees"]));
        }        
        if(isset($_POST["surface"]) && is_numeric($_POST["surface"])) {
            update_post_meta($adId, "adSurface", absint($_POST["surface"]));
        }
        if(isset($_POST["landSurface"]) && is_numeric($_POST["landSurface"])) {
            update_post_meta($adId, "adLandSurface", absint($_POST["landSurface"]));
        }
        if(isset($_POST["nbRooms"]) && is_numeric($_POST["nbRooms"])) {
            update_post_meta($adId, "adNbRooms", absint($_POST["nbRooms"]));
        }
        if(isset($_POST["nbBedrooms"]) && is_numeric($_POST["nbBedrooms"])) {
            update_post_meta($adId, "adNbBedrooms", absint($_POST["nbBedrooms"]));
        }
        $nbBathWaterRooms = 0;
        if(isset($_POST["nbBathrooms"]) && is_numeric($_POST["nbBathrooms"])) {
            update_post_meta($adId, "adNbBathrooms", intval($_POST["nbBathrooms"]));
            $nbBathWaterRooms += absint($_POST["nbBathrooms"]);
        }
        if(isset($_POST["nbWaterRooms"]) && is_numeric($_POST["nbWaterRooms"])) {
            update_post_meta($adId, "adNbWaterRooms", intval($_POST["nbWaterRooms"]));
            $nbBathWaterRooms += absint($_POST["nbWaterRooms"]);
        }
        update_post_meta($adId, "adNbBathWaterRooms", $nbBathWaterRooms);
        if(isset($_POST["nbWC"]) && is_numeric($_POST["nbWC"])) {
            update_post_meta($adId, "adNbWC", absint($_POST["nbWC"]));
        }            

        if(isset($_POST["showMap"]) && !ctype_space($_POST["showMap"])) {
            update_post_meta($adId, "adShowMap", sanitize_text_field($_POST["showMap"]));
            if(isset($_POST["address"]) && !ctype_space($_POST["address"])) {                   
                $query = urlencode(addslashes(htmlentities(sanitize_text_field($_POST["address"]))));
                $nonce = wp_create_nonce("apiAddress");
                if($_POST["showMap"] !== "all") { 
                    $zoom = 14;
                    $radiusCircle = 0;
                    $url = get_rest_url(null, PLUGIN_RE_NAME."/v1/address")."?query=$query&context=saveAd&coordsApprox&nonce=$nonce";
                    $addressData = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true);
                }else{
                    $zoom = 16;
                    $radiusCircle = 0;
                    $url = get_rest_url(null, PLUGIN_RE_NAME."/v1/address")."?query=$query&context=saveAd&nonce=$nonce";
                    $addressData = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true);
                }
                $coordinates = $addressData["coordinates"];
                update_post_meta($adId, "adDataMap", array("lat" => $coordinates["lat"], "long" => $coordinates["long"], "zoom" => $zoom, "circ" => $radiusCircle));
                update_post_meta($adId, "adLatitude", $coordinates["lat"]);
                update_post_meta($adId, "adLongitude", $coordinates["long"]);

                $address = $addressData["address"];
                update_post_meta($adId, "adAddress", $address);

                $postCode = $addressData["postCode"];
                update_post_meta($adId, "adPostCode", $postCode);

                if(isset($addressData["adminLvl1"])) {
                    update_post_meta($adId, "adAdminLvl1", $addressData["adminLvl1"]);
                }
                if(isset($addressData["adminLvl2"])) {
                    update_post_meta($adId, "adAdminLvl2", $addressData["adminLvl2"]);
                }

                $city = $addressData["city"];
                update_post_meta($adId, "adCity", $city);
            }
        }
        if(isset($_POST["images"]) && !ctype_space($_POST["images"])) {
            update_post_meta($adId, "adImages", sanitize_text_field($_POST["images"]));
        }
        if(isset($_POST["agent"]) && !ctype_space($_POST["agent"])) {
            update_post_meta($adId, "adIdAgent", absint($_POST["agent"]));
        }
        
        update_post_meta($adId, "adShowAgent", isset($_POST["showAgent"]));
        

        if(isset($_POST["labels"]) && !ctype_space($_POST["labels"])) {
            update_post_meta($adId, "adLabels", sanitize_text_field($_POST["labels"]));
        }


        if(isset($_POST["floor"]) && is_numeric($_POST["floor"])) {
            update_post_meta($adId, "adFloor", absint($_POST["floor"]));
        }
        if(isset($_POST["nbFloors"]) && is_numeric($_POST["nbFloors"])) {
            update_post_meta($adId, "adNbFloors", absint($_POST["nbFloors"]));
        }
        
        update_post_meta($adId, "adFurnished", isset($_POST["furnished"]));

        if(isset($_POST["year"]) && is_numeric($_POST["year"])) {
            update_post_meta($adId, "adYear", absint($_POST["year"]));
        }
        if(isset($_POST["typeHeating"]) && !ctype_space($_POST["typeHeating"])) {
            update_post_meta($adId, "adTypeHeating", sanitize_text_field($_POST["typeHeating"]));
        }
        if(isset($_POST["typeKitchen"]) && !ctype_space($_POST["typeKitchen"])) {
            update_post_meta($adId, "adTypeKitchen", sanitize_text_field($_POST["typeKitchen"]));
        }
        if(isset($_POST["nbBalconies"]) && !ctype_space($_POST["nbBalconies"])) {
            update_post_meta($adId, "adNbBalconies", absint($_POST["nbBalconies"]));
        }
        
        update_post_meta($adId, "adElevator", isset($_POST["elevator"]));

        update_post_meta($adId, "adCellar", isset($_POST["basement"]));

        update_post_meta($adId, "adTerrace", isset($_POST["terrace"]));

        if(isset($_POST["DPE"]) && is_numeric($_POST["DPE"])) {
            update_post_meta($adId, "adDPE", absint($_POST["DPE"]));
        }
        if(isset($_POST["GES"]) && is_numeric($_POST["GES"])) {
            update_post_meta($adId, "adGES", absint($_POST["GES"]));
        }

        //Custom fields
        $optionsGeneral = get_option(PLUGIN_RE_NAME."OptionsGeneral");
        if($optionsGeneral !== false && isset($optionsGeneral["customFields"])) {
            $customFields = $optionsGeneral["customFields"];
            if(!empty($customFields) || $customFields !== "[]") {
                $customFields = json_decode($customFields, true);
                foreach($customFields as $field) {
                    if(isset($_POST["CF".$field["nameAttr"]]) && !ctype_space($_POST["CF".$field["nameAttr"]])) {
                        update_post_meta($adId, "adCF".$field["nameAttr"], sanitize_text_field($_POST["CF".$field["nameAttr"]]));
                    }
                }
            }
        }
    }
    
    
    
    private static function saveTaxonomy($postId, $taxonomyName) {
        $taxonomy = sanitize_text_field($_POST[$taxonomyName]);

        if(!empty($taxonomy)) {
            $term = get_term_by("name", $taxonomy, $taxonomyName);
            if(!empty($term) && !is_wp_error($term)) {
                wp_set_object_terms($postId, $term->term_id, $taxonomyName, false);
            }
        }
    }
    
    private static function saveTaxonomyAdAvailable($postId, $state) {
        $taxonomyName = "adAvailable";
        if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
            return;
        }
        
        if($term = get_term_by("slug", $state, $taxonomyName)) {
            wp_set_object_terms($postId, $term->term_id, $taxonomyName, false);
        }
        
    }
    
    private static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
    }
    
}
