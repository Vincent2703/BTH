<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
class REALM_AdModel {
    private static $metas;

    public static function getAd($id) {
        require_once(PLUGIN_RE_PATH."models/UserModel.php");

        self::$metas = get_post_custom($id);
        $ad = array();
         
        $ad["taxonomies"] = self::getTaxonomies($id);
        
        $ad["title"] = get_the_title($id);
        $ad["description"] = get_the_content();
        $maxLengthDescriptionAd = 35;
        if(substr_count($ad["description"], ' ') > $maxLengthDescriptionAd) {
            $arrayDescriptionAd = explode(" ", $ad["description"]);
            $ad["shortDescription"] = implode(" ", array_splice($arrayDescriptionAd, 0, $maxLengthDescriptionAd)) . " [...]";
        }else{
            $ad["shortDescription"] = $ad["description"];
        }
        
        $ad["thumbnails"] = array(
            "small" => get_the_post_thumbnail($id, "thumbnail"),
            "medium" => get_the_post_thumbnail($id, "medium"),      
            "large" => get_the_post_thumbnail($id, "large"),
            "full" => get_the_post_thumbnail($id, "full")
        );
        
        $ad["permalink"] = get_permalink($id);

        $ad["refAd"] = sanitize_text_field(self::getMeta("adRefAgency"));
        $ad["price"] = intval(self::getMeta("adPrice"));
        $ad["fees"] = intval(self::getMeta("adFees"));
        $ad["surface"] = intval(self::getMeta("adSurface"));
        $ad["landSurface"] = intval(self::getMeta("adLandSurface"));
        $ad["nbRooms"] = intval(self::getMeta("adNbRooms"));
        $ad["nbBedrooms"] = intval(self::getMeta("adNbBedrooms"));
        $ad["nbBathrooms"] = intval(self::getMeta("adNbBathrooms"));
        $ad["nbWaterRooms"] = intval(self::getMeta("adNbWaterRooms"));
        $ad["nbBathWaterRooms"] = intval(self::getMeta("adNbBathWaterRooms"));
        $ad["nbWC"] = intval(self::getMeta("adNbWC"));

        $ad["floor"] = intval(self::getMeta("adFloor"));
        $ad["nbFloors"] = intval(self::getMeta("adNbFloors"));
        $ad["furnished"] = self::getMeta("adFurnished");
        $ad["year"] = intval(self::getMeta("adYear"));
        $ad["nbBalconies"] = sanitize_text_field(self::getMeta("adNbBalconies"));
        $ad["elevator"] = sanitize_text_field(self::getMeta("adElevator"));
        $ad["basement"] = sanitize_text_field(self::getMeta("adCellar"));
        $ad["outdoorSpace"] = sanitize_text_field(self::getMeta("adOutdoorSpace"));
        $ad["garage"] = sanitize_text_field(self::getMeta("adGarage"));
        $ad["parking"] = sanitize_text_field(self::getMeta("adParking"));
        $ad["DPE"] = intval(self::getMeta("adDPE"));
        $ad["GES"] = intval(self::getMeta("adGES"));

        $ad["typeHeating"] = sanitize_text_field(self::getMeta("adTypeHeating"));
        switch($ad["typeHeating"]) {
            case "Individual gas":
                $ad["typeHeatingTranslated"] = __("Individual gas", "retxtdom");
                break;
            case "CollectiveGas":
                $ad["typeHeatingTranslated"] = __("Collective gas", "retxtdom");
                break;
            case "Individual fuel":
                $ad["typeHeatingTranslated"] = __("Individual fuel", "retxtdom");
                break;
            case "Collective fuel":
                $ad["typeHeatingTranslated"] = __("Collective fuel", "retxtdom");
                break;
            case "Individual electric":
                $ad["typeHeatingTranslated"] = __("Individual electric", "retxtdom");
                break;
            case "Collective electric":
                $ad["typeHeatingTranslated"] = __("Collective electric", "retxtdom");
                break;
            default:
                $ad["typeHeatingTranslated"] = __("Unknown", "retxtdom");
                break;
        }

        $ad["typeKitchen"] = sanitize_text_field(self::getMeta("adTypeKitchen"));
        switch($ad["typeKitchen"]) {
            case "Not equipped":
                $ad["typeKitchenTranslated"] = __("Not equipped", "retxtdom");
                break;
            case "Kitchenette":
                $ad["typeKitchenTranslated"] = __("Kitchenette", "retxtdom");
                break;
            case "Standard":
                $ad["typeKitchenTranslated"] = __("Standard", "retxtdom");
                break;
            case "Industrial":
                $ad["typeKitchenTranslated"] = __("Industrial", "retxtdom");
                break;        
            default:
                $ad["typeKitchenTranslated"] = __("Unknown", "retxtdom");
        }          

        $images = self::getMeta("adImages");

        $ad["afterPrice"] = self::getCurrency();
        if($ad["taxonomies"]["typeAd"]["slug"] === "rental") {
            $ad["afterPrice"] .= '/'.__("month", "retxtdom");
        }

        if(!empty($images) && $images !== false) {
            $ad["imagesIds"] = explode(';', $images);
        }else{
            $ad["imagesIds"] = null;
        }

        $ad["showMap"] = self::getMeta("adShowMap");
        $ad["fullAddress"] = self::getMeta("adAddress");
        if($ad["showMap"] === "onlyPC") {
            $ad["address"] = self::getMeta("adCity").' '.self::getMeta("adPostCode");
            $optionsApis = get_option(PLUGIN_RE_NAME."OptionsApis");
            $displayAdminLvl1 = $optionsApis["apiAdminAreaLvl1"] == 1;
            $displayAdminLvl2 = $optionsApis["apiAdminAreaLvl2"] == 1;
            if($displayAdminLvl2 && !empty(self::getMeta("adAdminLvl2"))) {
                $ad["address"] .= ' '.self::getMeta("adAdminLvl2");
            }
            if($displayAdminLvl1 && !empty(self::getMeta("adAdminLvl1"))) {
                $ad["address"] .= ' '.self::getMeta("adAdminLvl1");
            }
        }else if($ad["showMap"] === "all"){
            $ad["address"] = $ad["fullAddress"];
        }
        $ad["coords"] = unserialize(self::getMeta("adDataMap"));
        $ad["postalCode"] = self::getMeta("adPostCode");

        if(isset($ad["coords"]) && !empty($ad["coords"]) && is_array($ad["coords"])) {
            $ad["getCoords"] = true;
        }else{
            $ad["getCoords"] = false;
        }
        $ad["city"] = self::getMeta("adCity");

        $ad["agent"] = self::getMeta("adIdAgent");
        $ad["showAgent"] = self::getMeta("adShowAgent") == 1;
        $ad["agency"] = get_user_by("ID", get_user_meta($ad["agent"], "agentAgency", true));       
       
        $ad["idContact"] = $ad["agent"];
        if(!empty($ad["idContact"])) {
            if(get_user_by("id", $ad["idContact"]) !== false) {
                require_once(PLUGIN_RE_PATH."models/UserModel.php");
                $contact = REALM_UserModel::getUser($ad["idContact"]);
                if($ad["showAgent"]) {
                    $ad["phone"] = $contact["agentPhone"];
                    $ad["mobilePhone"] = $contact["agentMobilePhone"];
                    $ad["nameContact"] = $contact["firstName"] .' '. $contact["lastName"];
                }else {
                    if(get_user_by("id", $ad["agency"]->ID) !== false) {
                        $ad["idContact"] = $ad["agency"]->ID; //Agency
                        $contact = REALM_UserModel::getUser($ad["idContact"]);
                        $ad["phone"] = $contact["agencyPhone"];
                        $ad["linkAgency"] = get_post_permalink($ad["idContact"]); //TO REPLACE
                        $ad["nameContact"] = $contact["displayName"];
                    }
                }
                if(is_numeric($ad["idContact"])) {
                    $ad["email"] = $contact["email"];
                    $ad["thumbnailContact"] = get_avatar_url($ad["idContact"], "thumbnail"); //Move to UserModel ?
                }
                $ad["getContact"] = true;
            }
        }

        if(!is_numeric($ad["idContact"])) {
            $ad["email"] = get_option(PLUGIN_RE_NAME . "OptionsEmail")["emailAd"];
            $ad["getContact"] = false;
        }
        
        $ad["allowSubmission"] = boolval(self::getMeta("adSubmissionsAllowed"));
        $ad["needGuarantors"] = boolval(self::getMeta("adNeedGuarantors"));

        $ad["morePosts"] = get_posts(array(
            "post_type" => "re-ad",
            "numberposts" => 15,
            "exclude" => $id,
            "meta_query" => array(
                array(
                    "key" => "adCity",
                    "value" => $ad["city"]
                ),
                array(
                    "key" => "_thumbnail_id"
                )
            ),
            "tax_query" => array(
                array(
                    "taxonomy" => "adTypeAd",
                    "field" => "slug",
                    "terms" => $ad["taxonomies"]["typeAd"]["slug"]
                ),
                array(
                    "taxonomy" => "adAvailable",
                    "field" => "slug",
                    "terms" => "available"
                )
            )
        ));

        $ad["customMainFields"] = array();
        $ad["customAdditionalFields"] = array();
        $optionsGeneral = get_option(PLUGIN_RE_NAME."OptionsDisplayads");
        if($optionsGeneral !== false ) {
            $customFields = $optionsGeneral["customFields"];
            if(!empty($customFields) || $customFields !== "[]") {
               foreach(json_decode($customFields, true) as $field) {
                   if($field["section"] === "mainFeatures") {
                       array_push($ad["customMainFields"], $field["name"]);
                   }else if($field["section"] === "complementaryFeatures") {
                       array_push($ad["customAdditionalFields"], $field["name"]);
                   }
               }
            }
        }
        return $ad;
    }
    
    public static function updateAd($adId, $ad) {       
        /*if(isset($_POST["postStatus"]) && in_array($_POST["postStatus"], array("publish", "pending", "draft", "future"))) {
            wp_update_post(array(
                "ID" => $adId, 
                "post_status" => $_POST["postStatus"])
            );
        }*/
        
        /*wp_update_post(array(
            "ID" => $adId, 
            "post_status" => "publish"
        ));*/
        
        $ad->post_title = substr(sanitize_text_field($ad->postTitle), 0, 64);

        if(isset($_POST["adTypeProperty"]) && !empty(trim($_POST["adTypeProperty"]))) {
            self::saveTaxonomy($adId, "adTypeProperty");
        }
        if(isset($_POST["adTypeAd"]) && !empty(trim($_POST["adTypeAd"]))) {
            self::saveTaxonomy($adId, "adTypeAd");
        }
        if(isset($_POST["adAvailable"]) && $_POST["adAvailable"] === "available") {
            self::saveTaxonomyAdAvailable($adId, "available");
        }else{
            self::saveTaxonomyAdAvailable($adId, "unavailable");
        }            

        if(isset($_POST["refAgency"]) && !empty(trim($_POST["refAgency"]))) {
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

        if(isset($_POST["showMap"]) && !empty(trim($_POST["showMap"]))) {
            update_post_meta($adId, "adShowMap", sanitize_text_field($_POST["showMap"]));
            if(isset($_POST["address"]) && !empty(trim($_POST["address"]))) {                   
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
        if(isset($_POST["images"]) && !empty(trim($_POST["images"]))) {
            update_post_meta($adId, "adImages", sanitize_text_field($_POST["images"]));
        }
        if(isset($_POST["agent"]) && is_numeric($_POST["agent"])) {
            update_post_meta($adId, "adIdAgent", absint($_POST["agent"]));
        }
        
        update_post_meta($adId, "adShowAgent", isset($_POST["showAgent"]));
        

        if(isset($_POST["labels"]) && !empty(trim($_POST["labels"]))) {
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
        if(isset($_POST["typeHeating"]) && !empty(trim($_POST["typeHeating"]))) {
            update_post_meta($adId, "adTypeHeating", sanitize_text_field($_POST["typeHeating"]));
        }
        if(isset($_POST["typeKitchen"]) && !empty(trim($_POST["typeKitchen"]))) {
            update_post_meta($adId, "adTypeKitchen", sanitize_text_field($_POST["typeKitchen"]));
        }
        if(isset($_POST["nbBalconies"]) && !empty(trim($_POST["nbBalconies"]))) {
            update_post_meta($adId, "adNbBalconies", absint($_POST["nbBalconies"]));
        }
        
        update_post_meta($adId, "adElevator", isset($_POST["elevator"]));

        update_post_meta($adId, "adCellar", isset($_POST["basement"]));

        update_post_meta($adId, "adOutdoorSpace", isset($_POST["outdoorSpace"]));
        
        update_post_meta($adId, "adGarage", isset($_POST["garage"]));
        
        update_post_meta($adId, "adParking", isset($_POST["parking"]));

        if(isset($_POST["DPE"]) && is_numeric($_POST["DPE"])) {
            update_post_meta($adId, "adDPE", absint($_POST["DPE"]));
        }
        if(isset($_POST["GES"]) && is_numeric($_POST["GES"])) {
            update_post_meta($adId, "adGES", absint($_POST["GES"]));
        }
        
        update_post_meta($adId, "adSubmissionsAllowed", isset($_POST["allowSubmission"]));
        update_post_meta($adId, "adNeedGuarantors", isset($_POST["needGuarantors"]));

        //Custom fields
        $optionsGeneral = get_option(PLUGIN_RE_NAME."OptionsGeneral");
        if($optionsGeneral !== false && isset($optionsGeneral["customFields"])) {
            $customFields = $optionsGeneral["customFields"];
            if(!empty($customFields) || $customFields !== "[]") {
                $customFields = json_decode($customFields, true);
                foreach($customFields as $field) {
                    if(isset($_POST["CF".$field["nameAttr"]]) && !empty(trim($_POST["CF".$field["nameAttr"]]))) {
                        update_post_meta($adId, "adCF".$field["nameAttr"], sanitize_text_field($_POST["CF".$field["nameAttr"]]));
                    }
                }
            }
        }
    }
    
    private static function getTaxonomies($id) {
        $typeAdTerm = get_the_terms($id, "adTypeAd");
        $typePropertyTerm = get_the_terms($id, "adTypeProperty");
        $availabilityTerm = get_the_terms($id, "adAvailable");

        $typeAd = $typeAdTerm ? array("name" => $typeAdTerm[0]->name, "slug" => $typeAdTerm[0]->slug) : array("name" => null, "slug" => null);
        $typeProperty = $typePropertyTerm ? array("name" => $typePropertyTerm[0]->name, "slug" => $typePropertyTerm[0]->slug) : array("name" => null, "slug" => null);
        $availability = $availabilityTerm ? array("name" => $availabilityTerm[0]->name, "slug" => $availabilityTerm[0]->slug) : array("name" => null, "slug" => null);

        return array(
            "typeAd"        => $typeAd,
            "typeProperty"  => $typeProperty,
            "availability"  => $availability
        );        
    }
    
    public static function getAdsBySearch($search) {
        $taxonomies = 
            array(
                array(
                    "taxonomy" => "adTypeAd",
                    "field" => "slug",
                    "terms" => $search["typeAd"]
                ),
                array(
                    "taxonomy" => "adTypeProperty",
                    "field" => "slug",
                    "terms" => $search["typeProperty"]
                ),
                array(
                    "taxonomy" => "adAvailable",
                    "field" => "slug",
                    "terms" => "available"
                )
            );
        
        $metas = 
            array(
                array(
                    "key" => "adNbRooms",
                    "value" => $search["nbRooms"],
                    "compare" => ">=",
                    "type" => "NUMERIC"
                ),
                array(
                    "key" => "adNbBedrooms",
                    "value" => $search["nbBedrooms"],
                    "compare" => ">=",
                    "type" => "NUMERIC"
                ),
                array(
                    "key" => "adNbBathWaterRooms",
                    "value" => $search["nbBathrooms"],
                    "compare" => ">=",
                    "type" => "NUMERIC"
                ),
            );
        
            if(isset($search["furnished"]) && $search["furnished"] === true) {
                array_push($metas,
                    array(
                        "key" => "adFurnished",
                        "value" => '1',
                        "type" => "NUMERIC"
                    )
                );
            }
            if(isset($search["land"]) && $search["land"] === true) {
                array_push($metas,
                    array(
                        "key" => "adLandSurface",
                        "value" => '0',
                        "compare" => ">",
                        "type" => "NUMERIC"
                    )
                );
            }
            if(isset($search["cellar"]) && $search["cellar"] === true) {
                array_push($metas,
                    array(
                        "key" => "adCellar",
                        "value" => '1',
                        "type" => "NUMERIC"
                    )
                );
            }
            if(isset($search["outdoorSpace"]) && $search["outdoorSpace"] === true) {
                array_push($metas,
                    array(
                        "key" => "adOutdoorSpace",
                        "value" => '1',
                        "type" => "NUMERIC"
                    )
                );
            }
            if(isset($search["elevator"]) && $search["elevator"] === true) {
                array_push($metas,
                    array(
                        "key" => "adElevator",
                        "value" => '1',
                        "type" => "NUMERIC"
                    )
                );
            }
            if(isset($search["garage"]) && $search["garage"] === true) {
                array_push($metas,
                    array(
                        "key" => "adGarage",
                        "value" => '1',
                        "type" => "NUMERIC"
                    )
                );
            }
            if(isset($search["parking"]) && $search["parking"] === true) {
                array_push($metas,
                    array(
                        "key" => "adParking",
                        "value" => '1',
                        "type" => "NUMERIC"
                    )
                );
            }
        
        if(isset($search["minSurface"]) && isset($search["maxSurface"]) && $search["maxSurface"] !== 0) {
            array_push($metas,
                array(
                    "key" => "adSurface",
                    "value" => array(intval($search["minSurface"]), $search["maxSurface"]),
                    "compare" => "BETWEEN",
                    "type" => "DECIMAL"
                )
            );
        }else if(isset($search["minSurface"]) && $search["minSurface"] !== 0) {
            array_push($metas,
                array(
                    "key" => "adSurface",
                    "value" => intval($search["minSurface"]),
                    "compare" => ">=",
                    "type" => "DECIMAL"
                )
            );
        }
        if(isset($search["minPrice"]) && isset($search["maxPrice"]) && $search["maxPrice"] !== 0) {
            array_push($metas,
                array(
                    "key" => "adPrice",
                    "value" => array(intval($search["minPrice"]), $search["maxPrice"]),
                    "compare" => "BETWEEN",
                    "type" => "DECIMAL"
                )
            );
        }else if(isset($search["minPrice"]) && $search["minPrice"] !== 0) {
            array_push($metas,
                array(
                    "key" => "adPrice",
                    "value" => $search["minPrice"],
                    "compare" => ">=",
                    "type" => "DECIMAL"
                )
            );
        }

        if(isset($search["searchBy"]) && $search["searchBy"] === "city") {
            array_push($metas,
                array(
                    "key" => "adCity",
                    "value" => $search["city"]
                )
            );
        }else if(isset($search["searchBy"]) && isset($search["radius"]) && $search["searchBy"] === "radius") {
            array_push($metas,
                array(
                    "key" => "adLatitude",
                    "value" => array($search["latitudes"][0], $search["latitudes"][1]),
                    "compare" => "BETWEEN"
                )
            );
            array_push($metas,
                array(
                    "key" => "adLongitude",
                    "value" => array($search["longitudes"][0], $search["longitudes"][1]),
                    "compare" => "BETWEEN"
                )
            );
        }
        
        $args = array(
            "post_type" => "re-ad",
            "numberposts" => 99,
            "fields" => "ids",
            "tax_query" => $taxonomies,
            "meta_query" => $metas
        );
        
        $postsIds = get_posts($args);

        return $postsIds;
    }
    
    public function setQueryAds($query) {
        if(!is_admin() && $query->is_main_query() && is_post_type_archive("re-ad")) {       
            $query->set("post_type", "re-ad");
            $adsPerPage = 4;
            $page = get_query_var("paged")>0?absint(get_query_var("paged")):1;
            $offset = ($page-1)*$adsPerPage;
            
            $query->set("posts_per_page", $adsPerPage);
            $query->set("offset", $offset);
            
            $terms = array(
                array(
                    "taxonomy" => "adAvailable",
                    "field" => "slug",
                    "terms" => array("available"),
                )
            );
            $metas = array();
            
            if(isset($_GET["typeAd"]) && !empty(trim($_GET["typeAd"]))) {
                array_push($terms,
                    array(
                        "taxonomy" => "adTypeAd",
                        "field" => "slug",
                        "terms" => sanitize_text_field($_GET["typeAd"])
                    )
                );            
            }
            if(isset($_GET["typeProperty"]) && !empty(trim($_GET["typeProperty"]))) {
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
            if(isset($_GET["outdoorSpace"]) && $_GET["outdoorSpace"] === "on") {
                array_push($metas,
                    array(
                        "key" => "adOutdoorSpace",
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
            if(isset($_GET["garageparking"]) && $_GET["garageparking"] === "on") {
                array_push($metas,
                    array(
                        "relation" => "OR",
                        array(
                            "key" => "adGarage",
                            "value" => '1',
                            "type" => "NUMERIC"
                        ),
                        array(
                            "key" => "adParking",
                            "value" => '1',
                            "type" => "NUMERIC"
                        )
                    )
                );
            }
            
            if(isset($_GET["city"]) && !empty(trim($_GET["city"]))) {
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
                    $url = urlencode(get_rest_url(null, PLUGIN_RE_NAME."/v1/address")."?query=".sanitize_text_field($_GET["city"])."&context=searchAds&searchBy=radius&radius=".intval($_GET["radius"])."&nonce=$nonce");
                    $addressData = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true);
                    if(isset($addressData["minLat"]) && isset($addressData["maxLat"]) && isset($addressData["minLong"]) && isset($addressData["maxLong"])) {
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
            }                         
            $query->set("tax_query", array($terms));
            
            if(!empty($metas)) {
                $query->set("meta_query", array($metas));
            }          
        }
    }
    
    public static function getAdsByAgency($agencyID, $status=array("publish", "draft")) {
        require_once(PLUGIN_RE_PATH."models/UserModel.php");
        $postStatusSQL = is_array($status)?"'".implode("','", $status)."'":"'{$status}'";
        
        $agentsAgency = REALM_UserModel::getAgentsAgency($agencyID);
        $agentsAgencyIds = array_column($agentsAgency, "ID");
        $metaQueryValue = !empty($agentsAgencyIds) ? implode(',', $agentsAgencyIds) : 0;

        $SQLRequest =
            "SELECT p.ID
            FROM wp_posts p
            JOIN wp_postmeta pm ON p.ID = pm.post_id
            WHERE p.post_type = 're-ad'
            AND p.post_status IN ($postStatusSQL)
            AND pm.meta_key = 'adIdAgent'
            AND pm.meta_value IN ($metaQueryValue)";

        global $wpdb;

        return $wpdb->get_results($SQLRequest);
    }
    
    public static function getNbAdsByAgency($agencyID, $status=array("publish", "draft")) {
        return count(self::getAdsByAgency($agencyID, $status));
    }
    
    public static function getNbAdsWithoutAgency() {
        $SQLRequest = 
            "SELECT COUNT(p.ID) as count
            FROM wp_posts p
            LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = 'adIdAgent'
            WHERE p.post_type = 're-ad'
            AND p.post_status IN ('publish', 'draft')
            AND pm.meta_key IS NULL";
        
        global $wpdb;
        
        return $wpdb->get_results($SQLRequest)[0]->count;
    }
    
    public static function getAdsByAgent($agentID, $status=array("publish", "draft")) {
        $postStatusSQL = is_array($status)?"'".implode("','", $status)."'":"'{$status}'";
        $SQLRequest = 
            "SELECT p.ID
            FROM wp_posts p
            JOIN wp_postmeta pm ON p.ID = pm.post_id
            WHERE p.post_type = 're-ad'
            AND p.post_status IN ($postStatusSQL)
            AND pm.meta_key = 'adIdAgent'
            AND pm.meta_value = $agentID";

        global $wpdb;

        return $wpdb->get_results($SQLRequest);
    }
    
    public static function getNbAdsByAgent($agentID, $status=array("publish", "draft")) {
        return count(self::getAdsByAgent($agentID, $status));
    }    
    
    public static function getCurrency() {
        return get_option(PLUGIN_RE_NAME."OptionsGeneral")["currency"];
    }
    
    public static function getAreaUnit() {
        return get_option(PLUGIN_RE_NAME."OptionsGeneral")["areaUnit"];
    }
    
    public static function getFeesURL() {
        if(isset(get_option(PLUGIN_RE_NAME."OptionsFees")["feesUrl"])) {
            return get_option(PLUGIN_RE_NAME."OptionsFees")["feesUrl"];
        }
        return false;
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
