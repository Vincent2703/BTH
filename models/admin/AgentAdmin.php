<?php

class AgentAdmin {
    private static $metas;
    
    public static $phone;
    public static $mobilePhone;
    public static $email;
    
    public static function getData($id) {
        self::$metas = get_post_custom($id);
        
        self::$phone = sanitize_text_field(self::getMeta("agentPhone"));
        self::$mobilePhone = sanitize_text_field(self::getMeta("agentMobilePhone"));
        self::$email = sanitize_email(self::getMeta("agentEmail"));
    }
    
    public static function setData($id) {
        if(isset($_POST["phone"]) && !ctype_space($_POST["phone"])) {
            update_post_meta($id, "agentPhone", sanitize_text_field($_POST["phone"]));
        }
        if(isset($_POST["mobilePhone"]) && !ctype_space($_POST["mobilePhone"])) {
            update_post_meta($id, "agentMobilePhone", sanitize_text_field($_POST["mobilePhone"]));
        }
        if(isset($_POST["email"]) && is_email($_POST["email"])) {
            update_post_meta($id, "agentEmail", sanitize_email($_POST["email"]));
        }

        if(isset($_POST["agency"]) && is_numeric($_POST["agency"])) {
            wp_update_post(array(
                "ID" => $id,
                "post_parent" => intval($_POST["agency"])
            ));             
        }
    }
    
    private static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
    }
}
