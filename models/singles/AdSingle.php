<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
class REALM_AdSingle {
    private static $metas;
    
    public static $refAd;
    public static $price;
    public static $fees;
    public static $surface;
    public static $landSurface;
    public static $nbRooms;
    public static $nbBedrooms;
    public static $nbBathrooms;
    public static $nbWaterRooms;
    public static $nbWC;
        
    public static $floor;
    public static $nbFloors;
    public static $furnished;
    public static $year;
    public static $typeHeating;
    public static $typeHeatingTranslated;
    public static $typeKitchen;
    public static $typeKitchenTranslated;
    public static $nbBalconies;
    public static $elevator;
    public static $basement;
    public static $terrace;
    public static $DPE;
    public static $GES;
    
    public static $typeAd;
    public static $typeAdSlug;
    public static $typeProperty;
    public static $afterPrice;
    public static $imagesIds;
    public static $showMap;
    public static $address;
    public static $coords;
    public static $getCoords;
    public static $city;
    public static $idContact;
    public static $getContact;
    public static $email;
    public static $phone;
    public static $mobilePhone;
    public static $linkAgency;
    public static $thumbnailContact;
    public static $nameContact;
    public static $morePosts;
    public static $customMainFields;
    public static $customAdditionalFields;
    
     
    public static function getCurrency() {
        return get_option(PLUGIN_RE_NAME."OptionsGeneral")["currency"];
    }
    
    public static function getFeesURL() {
        if(isset(get_option(PLUGIN_RE_NAME."OptionsFees")["feesUrl"])) {
            return get_option(PLUGIN_RE_NAME."OptionsFees")["feesUrl"];
        }
        return false;
    }
    
    public static function getData($id) {
            require_once(PLUGIN_RE_PATH."models/admin/UserAdmin.php");
            
            self::$metas = get_post_custom($id);

            self::$refAd = sanitize_text_field(self::getMeta("adRefAgency"));
            self::$price = intval(self::getMeta("adPrice"));
            self::$fees = intval(self::getMeta("adFees"));
            self::$surface = intval(self::getMeta("adSurface"));
            self::$landSurface = intval(self::getMeta("adLandSurface"));
            self::$nbRooms = intval(self::getMeta("adNbRooms"));
            self::$nbBedrooms = intval(self::getMeta("adNbBedrooms"));
            self::$nbBathrooms = intval(self::getMeta("adNbBathrooms"));
            self::$nbWaterRooms = intval(self::getMeta("adNbWaterRooms"));
            self::$nbWC = intval(self::getMeta("adNbWC"));
            
            self::$floor = intval(self::getMeta("adFloor"));
            self::$nbFloors = intval(self::getMeta("adNbFloors"));
            self::$furnished = self::getMeta("adFurnished");
            self::$year = intval(self::getMeta("adYear"));
            self::$nbBalconies = sanitize_text_field(self::getMeta("adNbBalconies"));
            self::$elevator = sanitize_text_field(self::getMeta("adElevator"));
            self::$basement = sanitize_text_field(self::getMeta("adCellar"));
            self::$terrace = sanitize_text_field(self::getMeta("adTerrace"));
            self::$DPE = intval(self::getMeta("adDPE"));
            self::$GES = intval(self::getMeta("adGES"));
            
            self::$typeHeating = sanitize_text_field(self::getMeta("adTypeHeating"));
            switch(self::$typeHeating) {
                case "Individual gas":
                    self::$typeHeatingTranslated = __("Individual gas", "retxtdom");
                    break;
                case "CollectiveGas":
                    self::$typeHeatingTranslated = __("Collective gas", "retxtdom");
                    break;
                case "Individual fuel":
                    self::$typeHeatingTranslated = __("Individual fuel", "retxtdom");
                    break;
                case "Collective fuel":
                    self::$typeHeatingTranslated = __("Collective fuel", "retxtdom");
                    break;
                case "Individual electric":
                    self::$typeHeatingTranslated = __("Individual electric", "retxtdom");
                    break;
                case "Collective electric":
                    self::$typeHeatingTranslated = __("Collective electric", "retxtdom");
                    break;
                default:
                    self::$typeHeatingTranslated = __("Unknown", "retxtdom");
                    break;
            }
            
            self::$typeKitchen = sanitize_text_field(self::getMeta("adTypeKitchen"));
            switch(self::$typeKitchen) {
                case "Not equipped":
                    self::$typeKitchenTranslated = __("Not equipped", "retxtdom");
                    break;
                case "Kitchenette":
                    self::$typeKitchenTranslated = __("Kitchenette", "retxtdom");
                    break;
                case "Standard":
                    self::$typeKitchenTranslated = __("Standard", "retxtdom");
                    break;
                case "Industrial":
                    self::$typeKitchenTranslated = __("Industrial", "retxtdom");
                    break;        
                default:
                    self::$typeKitchenTranslated = __("Unknown", "retxtdom");
            }          
            
            $images = self::getMeta("adImages");
            self::$typeAd = get_the_terms($id, "adTypeAd")[0]->name;
            self::$typeAdSlug = get_the_terms($id, "adTypeAd")[0]->slug;
            self::$typeProperty = get_the_terms($id, "adTypeProperty")[0]->name;
            self::$afterPrice = self::getCurrency();
            if(self::$typeAdSlug === "rental") {
                self::$afterPrice .= '/'.__("month", "retxtdom");
            }

            if(!is_null($images)) {
                self::$imagesIds = explode(';', $images);
            }

            self::$showMap = self::getMeta("adShowMap");
            if(self::$showMap === "onlyPC") {
                self::$address = self::getMeta("adCity").' '.self::getMeta("adPostCode");
                $optionsApis = get_option(PLUGIN_RE_NAME."OptionsApis");
                $displayAdminLvl1 = $optionsApis["apiAdminAreaLvl1"] == 1;
                $displayAdminLvl2 = $optionsApis["apiAdminAreaLvl2"] == 1;
                if($displayAdminLvl2 && !empty(self::getMeta("adAdminLvl2"))) {
                    self::$address .= ' '.self::getMeta("adAdminLvl2");
                }
                if($displayAdminLvl1 && !empty(self::getMeta("adAdminLvl1"))) {
                    self::$address .= ' '.self::getMeta("adAdminLvl1");
                }
            }else if(self::$showMap === "all"){
                self::$address = self::getMeta("adAddress");
            }
            self::$coords = unserialize(self::getMeta("adDataMap"));
            
            if(isset(self::$coords) && !empty(self::$coords) && is_array(self::$coords)) {
                self::$getCoords = true;
            }else{
                self::$getCoords = false;
            }
            self::$city = self::getMeta("adCity");
            
            if(!empty($idContact = self::getMeta("adIdAgent"))) {
                self::$getContact = true;
                if(intval(self::getMeta("adShowAgent")) === 1) {
                    REALM_UserAdmin::getData($idContact);
                    self::$idContact = $idContact;
                    self::$phone = REALM_UserAdmin::$agentPhone;
                    self::$mobilePhone = REALM_UserAdmin::$agentMobilePhone;
                    self::$nameContact = REALM_UserAdmin::$firstName .' '. REALM_UserAdmin::$lastName;
                }else{
                    self::$idContact = get_user_meta($idContact);
                    REALM_UserAdmin::getData(self::$idContact);
                    self::$phone = REALM_UserAdmin::$agencyPhone;
                    self::$linkAgency = get_post_permalink(self::$idContact); //TO REMPLACE
                    self::$nameContact = REALM_UserAdmin::$displayName;
                }
                self::$email = REALM_UserAdmin::$email;
                self::$thumbnailContact = get_avatar_url($idContact, "thumbnail");
            }else{
                self::$email = get_option(PLUGIN_RE_NAME."OptionsEmail")["emailAd"];
                self::$getContact = false;
            }


            self::$morePosts = get_posts(array(
                "post_type" => "re-ad",
                "numberposts" => 15,
                "exclude" => $id,
                "meta_query" => array(
                    array(
                        "key" => "adCity",
                        "value" => self::$city
                    ),
                    array(
                        "key" => "_thumbnail_id"
                    )
                ),
                "tax_query" => array(
                    array(
                        "taxonomy" => "adTypeAd",
                        "field" => "name",
                        "terms" => self::$typeAd
                    ),
                    array(
                        "taxonomy" => "adAvailable",
                        "field" => "slug",
                        "terms" => "available"
                    )
                )
            ));
            
            self::$customMainFields = array();
            self::$customAdditionalFields = array();
            $optionsGeneral = get_option(PLUGIN_RE_NAME."OptionsDisplayads");
            if($optionsGeneral !== false ) {
                $customFields = $optionsGeneral["customFields"];
                if(!empty($customFields) || $customFields !== "[]") {
                   foreach(json_decode($customFields, true) as $field) {
                       if($field["section"] === "mainFeatures") {
                           array_push(self::$customMainFields, $field["name"]);
                       }else if($field["section"] === "complementaryFeatures") {
                           array_push(self::$customAdditionalFields, $field["name"]);
                       }
                   }
                }
            }
    }
    
    private static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
    }
    
}
