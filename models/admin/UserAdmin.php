<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Get and set custom user meta values for the admin front-end
 * 
 */

class REALM_UserAdmin {
    private static $metas;
    
    //All
    public static $displayName;
    public static $lastName;
    public static $firstName;
    public static $email;
    
    //Customer
    public static $customerPhone;
    public static $customFields;
    public static $alert;
    
    //Agent
    public static $agentPhone;
    public static $agentMobilePhone;
    public static $agentAgency;
            
    //Agency
    public static $agencyPhone;
    public static $agencyAddress;
    public static $agencyDescription;
    
    
    public static function getData($id) {     
        $role = get_user_by("ID", $id)->roles[0];
        self::$metas = get_user_meta($id);
        
        self::$displayName = sanitize_text_field(get_userdata($id)->display_name);
        self::$lastName = sanitize_text_field(self::getMeta("last_name"));
        self::$firstName = sanitize_text_field(self::getMeta("first_name"));
        self::$email = sanitize_email(get_userdata($id)->user_email);
         
        if($role === "customer") {
            self::$customerPhone = sanitize_text_field(self::getMeta("customerPhone"));

            self::$customFields = array();

            $options = get_option(PLUGIN_REP_NAME."Options");
            if($options !== false && isset($options["customFields"])) {
                $customFieldsOption = $options["customFields"];
                if(!empty($customFieldsOption) || $customFieldsOption !== "[]") {
                    $customFieldsOption = json_decode($customFieldsOption, true);
                    foreach($customFieldsOption as $field) {
                        $name = sanitize_text_field($field["name"]);
                        $nameAttr = sanitize_text_field($field["nameAttr"]);
                        $type = sanitize_text_field($field["type"]);
                        $optionnal = boolval($field["optionnal"]);
                        $value = maybe_unserialize(self::getMeta("customerCF".$nameAttr));

                        if($field["type"] === "text") {                        
                            self::$customFields[$name] = array("nameAttr"=>$nameAttr, "type"=>$type, "optionnal"=>$optionnal, "value"=>sanitize_text_field($value));
                        }else if($field["type"] === "file") {
                            $extensions = sanitize_text_field($field["extensions"]);
                            self::$customFields[$name] = array("nameAttr"=>$nameAttr, "type"=>$type, "extensions"=>$extensions, "optionnal"=>$optionnal, "file"=>$value);
                        }
                    }
                }
            }
            self::$alert = self::getMeta("customerAlert");
        }else if($role === "agent") {
            self::$agentPhone = sanitize_text_field(self::getMeta("agentPhone"));
            self::$agentMobilePhone = sanitize_text_field(self::getMeta("agentMobilePhone"));          
            self::$agentAgency = intval(self::getMeta("agentAgency"));
        }else if($role === "agency") {
            self::$agencyPhone = sanitize_text_field(self::getMeta("agencyPhone"));
            self::$agencyAddress = sanitize_text_field(self::getMeta("agencyAddress"));
            self::$agencyDescription = sanitize_textarea_field(self::getMeta("agencyDescription"));
        }
         
    }
     
    public static function setData($idUser) {
        if(isset($_POST["role"]) && !ctype_space($_POST["role"])) {
            $role = $_POST["role"];
        }else{
            $role = get_user_by("ID", $idUser)->roles[0];
        }
        
        if($role === "customer") {
            if(isset($_POST["customerPhone"]) && !ctype_space($_POST["customerPhone"])) {
                update_user_meta($idUser, "customerPhone", sanitize_text_field($_POST["customerPhone"]));
            }   

            $options = get_option(PLUGIN_REP_NAME."Options");
            if($options !== false && isset($options["customFields"])) {
                $customFields = $options["customFields"];
                if(!empty($customFields) || $customFields !== "[]") {
                    $customFields = json_decode($customFields, true);
                    foreach($customFields as $field) {
                        if(isset($_POST["CF".$field["nameAttr"]]) && !ctype_space($_POST["CF".$field["nameAttr"]])) {
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
            }           
        }else if($role === "agent") {
            if(isset($_POST["agentPhone"]) && !ctype_space($_POST["agentPhone"])) {
                update_user_meta($idUser, "agentPhone", sanitize_text_field($_POST["agentPhone"]));
            }
            if(isset($_POST["agentMobilePhone"]) && !ctype_space($_POST["agentMobilePhone"])) {
                update_user_meta($idUser, "agentMobilePhone", sanitize_text_field($_POST["agentMobilePhone"]));
            }
            if(isset($_POST["agentAgency"]) && !ctype_space($_POST["agentAgency"])) {
                update_user_meta($idUser, "agentAgency", intval($_POST["agentAgency"]));
            }
        }else if($role === "agency") {
            if(isset($_POST["agencyPhone"]) && !ctype_space($_POST["agencyPhone"])) {
                update_user_meta($idUser, "agencyPhone", sanitize_text_field($_POST["agencyPhone"]));
            }
            if(isset($_POST["agencyAddress"]) && !ctype_space($_POST["agencyAddress"])) {
                update_user_meta($idUser, "agencyAddress", sanitize_text_field($_POST["agencyAddress"]));
            }
            if(isset($_POST["agencyDescription"]) && !ctype_space($_POST["agencyDescription"])) {
                update_user_meta($idUser, "agencyDescription", wp_kses_post($_POST["agencyDescription"]));
            }
            if(isset($_POST["user_login"]) && !ctype_space($_POST["user_login"])) {
                wp_update_user(array(
                    "ID" => $idUser,
                    "display_name" => sanitize_text_field($_POST["user_login"])
                ));
            }
        }
    }
    
    public static function setAlert($data) {
        $idUser = apply_filters("determine_current_user", false);
        $alert = array(
            "typeAd" => sanitize_text_field($data->get_param("typeAd")),
            "typeProperty" => sanitize_text_field($data->get_param("typeProperty")),
            "minSurface" => intval($data->get_param("minSurface")),
            "maxSurface" => intval($data->get_param("maxSurface")),
            "minPrice" => intval($data->get_param("minPrice")),
            "maxPrice" => intval($data->get_param("maxPrice")),
            "nbRooms" => intval($data->get_param("nbRooms")),
            "nbBedrooms" => intval($data->get_param("nbBedrooms")),
            "nbBathrooms" => intval($data->get_param("nbBathrooms")),
            "furnished" => $data->get_param("furnished") === "on",
            "land" => $data->get_param("land") === "on",
            "cellar" => $data->get_param("cellar") === "on",
            "terrace" => $data->get_param("terrace") === "on",
            "elevator" => $data->get_param("elevator") === "on",
            "city" => sanitize_text_field($data->get_param("city")),
            "searchBy" => sanitize_text_field($data->get_param("searchBy")),
            "radius" => intval($data->get_param("radius"))
        );
        update_user_meta($idUser, "customerAlert", $alert);
    }
    
    public static function getUsersByRole($role) {
        $users = get_users(array("role__in" => array($role)));
        return $users;
    }
    
    private static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
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
            SELF::getData($idUser);
            $agentAgency = SELF::$agentAgency;

            if($agentAgency !== 0) {
                $agency = new SELF;
                $agency->getData($agentAgency);
                //$agencyOutput = '<a target="_blank" href="' . get_edit_user_link($agentAgency) . '">' . $agency::$displayName . '</a>';
                $agencyOutput = $agency::$displayName;
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
}
