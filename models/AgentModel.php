<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
class REALM_AgentModel {
    public static function createPost($idUser) {
        require_once(PLUGIN_RE_PATH."models/UserModel.php");
        $user = REALM_UserAdmin::getUser($idUser);
        $postArgs = array(
            "post_author"   => $idUser,
            "post_type"     => "agent",
            "post_status"   => "publish",
            "post_title"    => $user["firstName"] .' '. $user["lastName"]
        );
        wp_insert_post($postArgs);
    }
}
