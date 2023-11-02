<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Get and set custom user meta values for the admin front-end
 * 
 */

class REALM_UserModel {
    private static $metas;
 
    public static function getUser($id) {
        $userData = get_userdata($id);
        $role = $userData->roles[0];
        self::$metas = get_user_meta($id);
        $user = array();
        
        $user["displayName"] = $userData->display_name;
        $user["lastName"] = $userData->last_name;
        $user["firstName"] = $userData->first_name;
        $user["email"] = $userData->user_email;
         
        if($role === "customer" && PLUGIN_RE_REP) {
            require_once(PLUGIN_REP_PATH."models/UserModel.php");
            $user = array_merge($user, REALMP_UserModel::getCustomerMetas());
        }else if($role === "agent") {
            $user["agentPhone"] = sanitize_text_field(self::getMeta("agentPhone"));
            $user["agentMobilePhone"] = sanitize_text_field(self::getMeta("agentMobilePhone"));          
            $user["agentAgency"] = intval(self::getMeta("agentAgency"));
        }else if($role === "agency") {
            $user["agencyPhone"] = sanitize_text_field(self::getMeta("agencyPhone"));
            $user["agencyAddress"] = sanitize_text_field(self::getMeta("agencyAddress"));
            $user["agencyDescription"] = sanitize_textarea_field(self::getMeta("agencyDescription"));
        }
        return $user;
    }
     
    public static function updateUser($idUser) {
        if(isset($_POST["role"]) && !empty(trim($_POST["role"]))) {
            $role = $_POST["role"];
        }else{
            $role = get_user_by("ID", $idUser)->roles[0];
        }
        
        if($role === "customer" && PLUGIN_RE_REP) {
            require_once(PLUGIN_REP_PATH."models/UserModel.php");
            REALMP_UserModel::updateCustomer($idUser);
        }else if($role === "agent") {
            if(isset($_POST["agentPhone"]) && !empty(trim($_POST["agentPhone"]))) {
                update_user_meta($idUser, "agentPhone", sanitize_text_field($_POST["agentPhone"]));
            }
            if(isset($_POST["agentMobilePhone"]) && !empty(trim($_POST["agentMobilePhone"]))) {
                update_user_meta($idUser, "agentMobilePhone", sanitize_text_field($_POST["agentMobilePhone"]));
            }
            if(isset($_POST["agentAgency"]) && !empty(trim($_POST["agentAgency"]))) {
                update_user_meta($idUser, "agentAgency", intval($_POST["agentAgency"]));
            }
        }else if($role === "agency") {
            if(isset($_POST["agencyPhone"]) && !empty(trim($_POST["agencyPhone"]))) {
                update_user_meta($idUser, "agencyPhone", sanitize_text_field($_POST["agencyPhone"]));
            }
            if(isset($_POST["agencyAddress"]) && !empty(trim($_POST["agencyAddress"]))) {
                update_user_meta($idUser, "agencyAddress", sanitize_text_field($_POST["agencyAddress"]));
            }
            if(isset($_POST["agencyDescription"]) && !empty(trim($_POST["agencyDescription"]))) {
                update_user_meta($idUser, "agencyDescription", wp_kses_post($_POST["agencyDescription"]));
            }
            if(isset($_POST["user_login"]) && !empty(trim($_POST["user_login"]))) {
                wp_update_user(array(
                    "ID" => $idUser,
                    "display_name" => sanitize_text_field($_POST["user_login"])
                ));
            }
        }
    }
        
    public static function getUsersByRole($role, $json=false) {
        $users = get_users(array("role" => $role, "fields" => array("ID", "display_name", "email")));    
        if($json) {
            echo json_encode($users);
        }else{
            return $users;
        }
    }

    public static function getAgentsAgency($agencyID) {
        $users = get_users(array(
            "role" => "agent", 
            "meta_query" => array( 
                array(
                    "key" => "agentAgency",
                    "value" => $agencyID,
                    "compare" => '='
                )
            ),
            "fields" => array("ID", "display_name", "email")
        ));
        return $users;
    }
    
    public static function agentAgencyHeaderColumn($columns) {
        if(isset($_GET["role"]) && $_GET["role"] === "agent") {
            $role = "agent";
            $orderby = "agentAgency";
            $order = isset($_GET["order"]) && strtolower($_GET['order']) === "asc"?"desc":"asc";
            //$columns["agentAgency"] = '<a href="?role='.$role.'&orderby='.$orderby.'&order='.$order.'">' . __("Agent's agency", "retxtdom") . '</a>';
            $columns["agentAgency"] = __("Agency of the agent", "retxtdom");
            unset($columns["posts"]);
            unset($columns["role"]);
        }
        return $columns;
    }
    
    public static function agentAgencyDataColumn($value, $columnName, $idUser) {
        if(isset($_GET["role"]) && $_GET["role"] === "agent" && $columnName === "agentAgency" && intval($idUser) !== 0) {
            $agent = SELF::getUser($idUser);
            $agentAgency = $agent["agentAgency"];

            if($agentAgency > 0) {
                $agency = SELF::getUser($agentAgency);
                //$agencyOutput = '<a target="_blank" href="' . get_edit_user_link($agentAgency) . '">' . $agency::$displayName . '</a>';
                $agencyOutput = $agency["displayName"];
                return $agencyOutput;
            }
        }
        return $value;
    }
    
    public static function agentAgencySortableColumn($columns) {
        if(isset($_GET["role"]) && $_GET["role"] === "agent") {
            $columns["agentAgency"] = "agentAgency";
        }
        return $columns;
    }    

    protected static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
    }
}
