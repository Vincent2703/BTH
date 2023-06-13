<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Create an Agency post from an Agency user
 * 
 */
class REALM_AgencyAdmin {
    
    public static function createPost($idUser) {
        require_once(PLUGIN_RE_PATH."models/admin/UserAdmin.php");
        REALM_UserAdmin::getData($idUser);
        $postArgs = array(
            "post_author"   => $idUser,
            "post_type"     => "agency",
            "post_status"   => "publish",
            "post_title"    => REALM_UserAdmin::$displayName
        );
        wp_insert_post($postArgs);
    }
}
