<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Create an Agent post from an Agent user
 * 
 */
class REALM_AgentAdmin {
    
    public static function createPost($idUser) {
        require_once(PLUGIN_RE_PATH."models/admin/UserAdmin.php");
        REALM_UserAdmin::getData($idUser);
        $postArgs = array(
            "post_author"   => $idUser,
            "post_type"     => "agent",
            "post_status"   => "publish",
            "post_title"    => REALM_UserAdmin::$firstName .' '. REALM_UserAdmin::$lastName
        );
        wp_insert_post($postArgs);
    }
}
