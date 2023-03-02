<?php

class AgentAdmin {
    private static $metas;
    
    private static $phone;
    private static $mobilePhone;
    private static $email;
    
    public static function getData($id) {
        self::$metas = get_post_custom($id);
        
        self::$phone = sanitize_text_field(self::getMeta("adPhone"));
        self::$mobilePhone = sanitize_email(self::getMeta("adMobilePhone"));
        self::$email = sanitize_text_field(self::getMeta("adAddress"));
    }
    
    private static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
    }
}
