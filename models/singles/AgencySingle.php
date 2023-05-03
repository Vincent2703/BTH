<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
class REALM_AgencySingle {
    private static $metas;
    
    public static $agents;
    public static $agencyAds;
    
    public static $phone;
    public static $email;
    public static $address;

    public static function getCurrency() {
        return get_option(PLUGIN_RE_NAME."OptionsGeneral")["currency"];
    }
    
    public static function getData($id) {
        self::$metas = get_post_custom($id);
        
        self::$phone = sanitize_text_field(self::getMeta("agencyPhone"));
        self::$email = sanitize_email(self::getMeta("agencyEmail"));
        self::$address = sanitize_text_field(self::getMeta("agencyAddress"));
        
        self::$agents = get_posts(array(
            "post_type" => "agent",
            "numberposts" => -1,
            "post_parent" => $id
        ));
        $agentsIDs = wp_list_pluck(self::$agents, "ID");
        
        self::$agencyAds = 
            get_posts(array(
                "post_type" => "re-ad",
                "numberposts" => 5,
                "meta_query" => array(
                    array(
                        "key" => "adIdAgent",
                        "value" => $agentsIDs,
                        "compare" => "IN"
                    ),
                    array(
                        "key" => "_thumbnail_id"
                    )
                ),
                "tax_query" => array(
                    array(
                        "taxonomy" => "adAvailable",
                        "field" => "slug",
                        "terms" => "available"
                    )
                )
            ));
    }
    
    private static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
    }
}


    