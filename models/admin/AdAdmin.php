<?php

class AdAdmin {
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
    
    public static $floor;
    public static $nbFloors;
    public static $furnished;
    public static $year;
    public static $typeHeating;
    public static $typeKitchen;
    public static $nbBalconies;
    public static $elevator;
    public static $cellar;
    public static $terrace;
    public static $DPE;
    public static $GES;
    
    
    public static function getMainFeatures($id) {
        self::$metas = get_post_custom($id);
        
        self::$refAgency = sanitize_text_field(self::getMeta("adRefAgency"));
        self::$price = intval(self::getMeta("adPrice"));
        self::$fees = intval(self::getMeta("adFees"));
        self::$surface = sanitize_text_field(self::getMeta("adSurface"));
        self::$landSurface = sanitize_text_field(self::getMeta("adLandSurface"));
        self::$nbRooms = intval(self::getMeta("adNbRooms"));
        self::$nbBedrooms = intval(self::getMeta("adNbBedrooms"));
        self::$nbBathrooms = intval(self::getMeta("adNbBathrooms"));
        self::$nbWaterRooms = intval(self::getMeta("adNbWaterRooms"));
        self::$nbWC = intval(self::getMeta("adNbWC"));
        self::$address = sanitize_text_field(self::getMeta("adAddress"));
        self::$showMap = sanitize_text_field(self::getMeta("adShowMap"));
        self::$images = sanitize_text_field(self::getMeta("adImages"));
        self::$allAgents = get_posts(array("post_type" => "agent"));
        self::$idAgent = sanitize_text_field(self::getMeta("adIdAgent"));
        self::$showAgent = sanitize_text_field(self::getMeta("adShowAgent"));
        
        $optionsDisplayads = get_option(PLUGIN_RE_NAME."OptionsDisplayads");
        if($optionsDisplayads !== false) {
            $customFields = $optionsDisplayads["customFields"];
            self::$customFieldsMF = array();
            if(!empty($customFields) || $customFields !== "[]") {
                foreach(json_decode($customFields, true) as $field) {
                    if($field["section"] === "mainFeatures") {
                        $customFieldsMF[$field["name"]] = sanitize_text_field(self::getMeta("adCF".$field["name"]));
                    }
                }
            }
        }
    }
    
    public static function getComplementaryFeatures($id) {
        self::$metas = get_post_custom($id);
        
        self::$floor = intval(self::getMeta("adFloor"));
        self::$nbFloors = intval(self::getMeta("adNbFloors"));
        self::$furnished = sanitize_text_field(self::getMeta("adFurnished"));
        self::$year = intval(self::getMeta("adYear"));
        self::$typeHeating = sanitize_text_field(self::getMeta("adTypeHeating"));
        self::$typeKitchen = sanitize_text_field(self::getMeta("adTypeKitchen"));
        self::$nbBalconies = intval(self::getMeta("adNbBalconies"));
        self::$elevator = sanitize_text_field(self::getMeta("adElevator"));
        self::$cellar = sanitize_text_field(self::getMeta("adCellar"));
        self::$terrace = sanitize_text_field(self::getMeta("adTerrace"));
        self::$DPE = intval(self::getMeta("adDPE"));
        self::$GES = intval(self::getMeta("adGES"));
        
        $optionsDisplayads = get_option(PLUGIN_RE_NAME."OptionsDisplayads");
        if($optionsDisplayads !== false) {
            $customFields = $optionsDisplayads["customFields"];
            self::$customFieldsCF = array();
            if(!empty($customFields) || $customFields !== "[]") {
                foreach(json_decode($customFields, true) as $field) {
                    if($field["section"] === "complementaryFeatures") {
                        $customFieldsCF[$field["name"]] = sanitize_text_field(self::getMeta("adCF".$field["name"]));
                    }
                }
            }
        }
    }
    
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
            update_post_meta($adId, "adPrice", intval($_POST["price"]));
        }
        if(isset($_POST["fees"]) && is_numeric($_POST["fees"])) {
            update_post_meta($adId, "adFees", intval($_POST["fees"]));
        }        
        if(isset($_POST["surface"]) && is_numeric($_POST["surface"])) {
            update_post_meta($adId, "adSurface", intval($_POST["surface"]));
        }
        if(isset($_POST["landSurface"]) && is_numeric($_POST["landSurface"])) {
            update_post_meta($adId, "adLandSurface", intval($_POST["landSurface"]));
        }
        if(isset($_POST["nbRooms"]) && is_numeric($_POST["nbRooms"])) {
            update_post_meta($adId, "adNbRooms", intval($_POST["nbRooms"]));
        }
        if(isset($_POST["nbBedrooms"]) && is_numeric($_POST["nbBedrooms"])) {
            update_post_meta($adId, "adNbBedrooms", intval($_POST["nbBedrooms"]));
        }
        $nbBathWaterRooms = 0;
        if(isset($_POST["nbBathrooms"]) && is_numeric($_POST["nbBathrooms"])) {
            update_post_meta($adId, "adNbBathrooms", intval($_POST["nbBathrooms"]));
            $nbBathWaterRooms += intval($_POST["nbBathrooms"]);
        }
        if(isset($_POST["nbWaterRooms"]) && is_numeric($_POST["nbWaterRooms"])) {
            update_post_meta($adId, "adNbWaterRooms", intval($_POST["nbWaterRooms"]));
            $nbBathWaterRooms += intval($_POST["nbWaterRooms"]);
        }
        update_post_meta($adId, "adNbBathWaterRooms", $nbBathWaterRooms);
        if(isset($_POST["nbWC"]) && is_numeric($_POST["nbWC"])) {
            update_post_meta($adId, "adNbWC", intval($_POST["nbWC"]));
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
            update_post_meta($adId, "adIdAgent", intval($_POST["agent"]));
        }
        if(isset($_POST["showAgent"])) {
            update_post_meta($adId, "adShowAgent", '1');
        }else{
            update_post_meta($adId, "adShowAgent", '0');
        }


        if(isset($_POST["labels"]) && !ctype_space($_POST["labels"])) {
            update_post_meta($adId, "adLabels", sanitize_text_field($_POST["labels"]));
        }


        if(isset($_POST["floor"]) && is_numeric($_POST["floor"])) {
            update_post_meta($adId, "adFloor", intval($_POST["floor"]));
        }
        if(isset($_POST["nbFloors"]) && is_numeric($_POST["nbFloors"])) {
            update_post_meta($adId, "adNbFloors", intval($_POST["nbFloors"]));
        }
        if(isset($_POST["furnished"]) && !ctype_space($_POST["furnished"])) {
            update_post_meta($adId, "adFurnished", '1');
        }else{
            update_post_meta($adId, "adFurnished", '0');
        }
        if(isset($_POST["year"]) && is_numeric($_POST["year"])) {
            update_post_meta($adId, "adYear", intval($_POST["year"]));
        }
        if(isset($_POST["typeHeating"]) && !ctype_space($_POST["typeHeating"])) {
            update_post_meta($adId, "adTypeHeating", $_POST["typeHeating"]);
        }
        if(isset($_POST["typeKitchen"]) && !ctype_space($_POST["typeKitchen"])) {
            update_post_meta($adId, "adTypeKitchen", $_POST["typeKitchen"]);
        }
        if(isset($_POST["nbBalconies"]) && !ctype_space($_POST["nbBalconies"])) {
            update_post_meta($adId, "adNbBalconies", intval($_POST["nbBalconies"]));
        }
        if(isset($_POST["elevator"]) && !ctype_space($_POST["elevator"])) {
            update_post_meta($adId, "adElevator", '1');
        }else{
            update_post_meta($adId, "adElevator", '0');
        }
        if(isset($_POST["cellar"]) && !ctype_space($_POST["cellar"])) {
            update_post_meta($adId, "adCellar", '1');
        }else{
            update_post_meta($adId, "adCellar", '0');                
        }
        if(isset($_POST["terrace"]) && !ctype_space($_POST["terrace"])) {
            update_post_meta($adId, "adTerrace", '1');
        }else{
            update_post_meta($adId, "adTerrace", '0');                
        }
        if(isset($_POST["DPE"]) && is_numeric($_POST["DPE"])) {
            update_post_meta($adId, "adDPE", intval($_POST["DPE"]));
        }
        if(isset($_POST["GES"]) && is_numeric($_POST["GES"])) {
            update_post_meta($adId, "adGES", intval($_POST["GES"]));
        }

        $optionsDisplayads = get_option(PLUGIN_RE_NAME."OptionsDisplayads");
        if($optionsDisplayads !== false) {
        $customFields = $optionsDisplayads["customFields"];
            if(!empty($customFields) || $customFields !== "[]") {
                foreach(json_decode($optionsDisplayads, true) as $field) {
                    if(isset($_POST["CF".$field["name"]]) && !ctype_space($_POST["CF".$field["name"]])) {
                        update_post_meta($adId, "adCF".$field["name"], sanitize_text_field($_POST["CF".$field["name"]]));
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
