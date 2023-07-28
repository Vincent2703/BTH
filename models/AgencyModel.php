<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
class REALM_AgencyModel {           
    /*
     * Create an Agency post from an Agency user
     */
    public static function createPost($idUser) {
        require_once(PLUGIN_RE_PATH."models/UserModel.php");
        $user = REALM_UserModel::getUser($idUser);
        $postArgs = array(
            "post_author"   => $idUser,
            "post_type"     => "agency",
            "post_status"   => "publish",
            "post_title"    => $user["displayName"]
        );
        wp_insert_post($postArgs);
    }
    
    public static function getAgency($id) {
        require_once(PLUGIN_RE_PATH."models/UserModel.php");
        $userAgency = REALM_UserModel::getUser(get_the_author_meta("ID", $id));
        
        $agency = array();   
        
        $agency["phone"] = $userAgency["phone"];
        $agency["email"] = $userAgency["email"];
        $agency["address"] = $userAgency["agencyAddress"];
        
        $agency["agents"] = get_posts(array(
            "post_type" => "agent",
            "numberposts" => 99,
            "post_parent" => $id
        ));
        $agentsIDs = wp_list_pluck($agency["agents"], "ID");
        
        $agency["agencyAds"] = 
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
        return $agency;
    }
    
}
