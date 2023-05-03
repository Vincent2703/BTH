<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Get and set agency meta values for the admin front-end
 * 
 */
class REALM_AgencyAdmin {
    private static $metas;
    
    public static $phone;
    public static $email;
    public static $address;
    
    public static function getData($id) {
        self::$metas = get_post_custom($id);
        
        self::$phone = sanitize_text_field(self::getMeta("agencyPhone"));
        self::$email = sanitize_email(self::getMeta("agencyEmail"));
        self::$address = sanitize_text_field(self::getMeta("agencyAddress"));
    }
    
    public static function setData($id) {
        if(isset($_POST["phone"]) && !ctype_space($_POST["phone"])) {
            update_post_meta($id, "agencyPhone", sanitize_text_field($_POST["phone"]));
        }
        if(isset($_POST["email"]) && is_email($_POST["email"])) {
            update_post_meta($id, "agencyEmail", sanitize_email($_POST["email"]));
        }
        if(isset($_POST["address"]) && !ctype_space($_POST["address"])) {
            update_post_meta($id, "agencyAddress", sanitize_text_field($_POST["address"]));
        }
    }
    
    private static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
    }
}
