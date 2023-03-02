<?php

class AgencyAdmin {
    private static $metas;
    
    private static $phone;
    private static $email;
    private static $address;
    
    public static function getData($id) {
        self::$metas = get_post_custom($id);
        
        self::$phone = sanitize_text_field(self::getMeta("adPhone"));
        self::$email = sanitize_email(self::getMeta("adEmail"));
        self::$address = sanitize_text_field(self::getMeta("adAddress"));
    }
    
    private static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
    }
}
