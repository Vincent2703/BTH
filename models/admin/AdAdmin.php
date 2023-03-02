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
    public static $agentSaved;
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
    
    
    public static function mainFeatures($id) {
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
        self::$agentSaved = sanitize_text_field(self::getMeta("adAgent"));
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
    
    public static function complementaryFeatures($ad) {
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
    
    private static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
    }
    
}
