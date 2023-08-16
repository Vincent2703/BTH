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
         
        if($role === "customer") {
            $user["customerPhone"] = sanitize_text_field(self::getMeta("customerPhone"));

            $user["customFields"] = array();

            $options = get_option(PLUGIN_REP_NAME."Options");
            if($options !== false && isset($options["customFields"])) {
                $customFieldsOption = $options["customFields"];
                if(!empty($customFieldsOption) || $customFieldsOption !== "[]") {
                    $customFieldsOption = json_decode($customFieldsOption, true);
                    foreach($customFieldsOption as $field) {
                        $name = $field["name"];
                        $nameAttr = $field["nameAttr"];
                        $type = $field["type"];
                        $optionnal = boolval($field["optionnal"]);
                        $category = $field["category"];
                        $value = maybe_unserialize(self::getMeta("customerCF".$nameAttr));

                        if($field["type"] === "text") {     
                            $user["customFields"]["$name"] = array("nameAttr"=>$nameAttr, "type"=>$type, "optionnal"=>$optionnal, "category"=>$category, "value"=>sanitize_text_field($value));
                        }else if($field["type"] === "file") {
                            $extensions = sanitize_text_field($field["extensions"]);
                            $user["customFields"]["$name"] = array("nameAttr"=>$nameAttr, "type"=>$type, "extensions"=>$extensions, "category"=>$category, "optionnal"=>$optionnal, "file"=>$value);
                        }
                    }
                }
            }
            $user["alert"] = maybe_unserialize(self::getMeta("customerAlert"));
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
        
        if($role === "customer") {
            print_r($_POST);
            die();
            /*if(isset($_POST["customerPhone"]) && !empty(trim($_POST["customerPhone"]))) {
                update_user_meta($idUser, "customerPhone", sanitize_text_field($_POST["customerPhone"]));
            }   

            $options = get_option(PLUGIN_REP_NAME."Options");
            if($options !== false && isset($options["customFields"])) {
                $customFields = $options["customFields"];
                if(!empty($customFields) || $customFields !== "[]") {
                    $customFields = json_decode($customFields, true);
                    foreach($customFields as $field) {
                        if(isset($_POST["CF".$field["nameAttr"]]) && !empty(trim($_POST["CF".$field["nameAttr"]]))) {
                            if($field["type"] === "text") {
                                update_user_meta($idUser, "customerCF".$field["nameAttr"], sanitize_text_field($_POST["CF".$field["nameAttr"]]));
                            }                       
                        }else if(isset($_FILES["CF".$field["nameAttr"]]) && file_exists($_FILES["CF".$field["nameAttr"]]["tmp_name"]) && is_uploaded_file($_FILES["CF".$field["nameAttr"]]["tmp_name"])) {
                            if($field["type"] === "file") {
                                $validMimeTypes = array(
                                    "pdf"   => "application/pdf",
                                    "jpg"   => "image/jpeg",
                                    "jpeg"  => "image/jpeg",
                                    "png"   => "image/png",
                                    "bmp"   => "image/bmp",
                                );
                                $upload = wp_handle_upload($_FILES["CF".$field["nameAttr"]], array("test_form" => false, "test_type" => true, "mimes"=>$validMimeTypes));
                                if(!isset($upload["error"])) {
                                    preg_match("/[^\/]+$/", $upload["file"], $matches);        
                                    $fileName = wp_unique_filename(wp_upload_dir()["path"], $matches[0]);
                                    $path = str_replace("\\", '/', $upload["file"]);
                                    update_user_meta($idUser, "customerCF".$field["nameAttr"], array("name"=>$fileName, "type"=>$upload["type"], "url"=>$upload["url"], "path"=>$path));
                                }
                            }
                        }
                    }
                }
            }    */       
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
            $columns["agentAgency"] = __("Agent's agency", "retxtdom");
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
    
    public static function setAlert($apiRequest = null, $params = array()) {        
        if(!is_null($apiRequest)) { //If API call
            $userID = apply_filters("determine_current_user", false);
            wp_set_current_user($userID);
            
            $address = sanitize_text_field($apiRequest->get_param("address"));
            
            $alert = array(
                "typeAd" => sanitize_text_field($apiRequest->get_param("typeAd")),
                "typeProperty" => sanitize_text_field($apiRequest->get_param("typeProperty")),
                "minSurface" => absint($apiRequest->get_param("minSurface")),
                "maxSurface" => absint($apiRequest->get_param("maxSurface")),
                "minPrice" => absint($apiRequest->get_param("minPrice")),
                "maxPrice" => absint($apiRequest->get_param("maxPrice")),
                "nbRooms" => absint($apiRequest->get_param("nbRooms")),
                "nbBedrooms" => absint($apiRequest->get_param("nbBedrooms")),
                "nbBathrooms" => absint($apiRequest->get_param("nbBathrooms")),
                "furnished" => filter_var($apiRequest->get_param("furnished"), FILTER_VALIDATE_BOOLEAN),
                "land" => filter_var($apiRequest->get_param("land"), FILTER_VALIDATE_BOOLEAN),
                "cellar" => filter_var($apiRequest->get_param("cellar"), FILTER_VALIDATE_BOOLEAN),
                "outdoorSpace" => filter_var($apiRequest->get_param("outdoorSpace"), FILTER_VALIDATE_BOOLEAN),
                "elevator" => filter_var($apiRequest->get_param("elevator"), FILTER_VALIDATE_BOOLEAN),
                "searchBy" => sanitize_text_field($apiRequest->get_param("searchBy")),
                "radius" => absint($apiRequest->get_param("radius"))
            );
        }else if(!empty($params)){
            $userID = absint($params["userID"]);
                    
            $address = $params["address"];
            
            $alert = array(
                "typeAd" => sanitize_text_field($params["typeAd"]),
                "typeProperty" => sanitize_text_field($params["typeProperty"]),
                "minSurface" => absint($params["minSurface"]),
                "maxSurface" => absint($params["maxSurface"]),
                "minPrice" => absint($params["minPrice"]),
                "maxPrice" => absint($params["maxPrice"]),
                "nbRooms" => absint($params["nbRooms"]),
                "nbBedrooms" => absint($params["nbBedrooms"]),
                "nbBathrooms" => absint($params["nbBathrooms"]),
                "furnished" => $params["furnished"],
                "land" => $params["land"],
                "cellar" => $params["cellar"],
                "outdoorSpace" => $params["outdoorSpace"],
                "elevator" => $params["elevator"],
                "searchBy" => sanitize_text_field($params["searchBy"]),
                "radius" => absint($params["radius"])
            );
        }else{
            return false;
        }
        
        
        if(!empty(trim($address))) {
            $nonce = wp_create_nonce("apiAddress");
            if($alert["searchBy"] === "city") {
                $url = get_rest_url(null, PLUGIN_RE_NAME."/v1/address") ."?query=".$address."&context=searchAds&searchBy=city&nonce=$nonce";
                $addressData = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true);
                $alert["city"] = sanitize_text_field($addressData["city"]);
                $alert["postCode"] = sanitize_text_field($addressData["postCode"]);               
            }else { 
                $url = get_rest_url(null, PLUGIN_RE_NAME."/v1/address")."?query=".$address."&context=searchAds&searchBy=radius&radius=".$alert["radius"]."&nonce=$nonce";
                $alert["city"] = $address;
                $addressData = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true);
                $alert["latitudes"] = array($addressData["minLat"], $addressData["maxLat"]);
                $alert["longitudes"] = array($addressData["minLong"], $addressData["maxLong"]);
            }
        }  
               
        $prevValue = maybe_unserialize(get_user_meta($userID, "customerAlert", true));
        if($prevValue === $alert) {
            $result = "sameAlert";
        }else{
            $result = update_user_meta($userID, "customerAlert", $alert);
        }
        echo json_encode(array("result" => $result));
    }
     
    public static function checkDataConformity($idUser) {
        $user = self::getUser($idUser);
        $checkCF = true;
        foreach($user["customFields"] as $CF) {
            if($CF["type"] === "text") {
                if(empty(trim($CF["nameAttr"])) || !is_bool($CF["optionnal"]) || empty(trim($CF["value"]))) {
                   $checkCF = false;
                }
            }else if($CF["type"] === "file") {
                if(empty(trim($CF["nameAttr"])) || !preg_match("/^(\.\w+)(,\s*\.\w+)*$/", $CF["extensions"]) || !is_bool($CF["optionnal"]) || !is_array($CF["file"]) || empty(trim($CF["file"]["url"]))) {
                    $checkCF = false;
                }
            }else{
                $checkCF = false;
            }
        }
        if(
            !empty(trim($user["lastName"])) &&
            !empty(trim($user["firstName"])) &&
            !empty(trim($user["customerPhone"])) &&
            !empty(trim($user["email"])) &&
            $checkCF
        ) {
            return true;
        }else{
            return false;
        }
    }
    
    private static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
    }
}
