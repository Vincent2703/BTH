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
    public static $lastName;
    public static $firstName;
    public static $email;
    
    //Customer
    public static $customerPhone;
    public static $customFields;
    
    //Agent
    public static $agentPhone;
    public static $agentMobilePhone;
            
    //Agency
    public static $agencyPhone;
    public static $agencyAddress;
    public static $agencyDescription;
    
    
    public static function getData($id) {
        $role = get_user_by("ID", $id)->roles[0];
        self::$metas = get_user_meta($id);
        
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
        }else if($role === "agent") {
            self::$agentPhone = sanitize_text_field(self::getMeta("agentPhone"));
            self::$agentMobilePhone = sanitize_text_field(self::getMeta("agentMobilePhone"));           
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
                                    preg_match("/(?<=\/)[a-zA-Z-0-9]+\.[a-zA-Z-0-9]+$/", $upload["file"], $matches);
                                    $fileName = sanitize_text_field($matches[0]);
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
        }
    }
    
    private static function getMeta($metaName) {
        return isset(self::$metas[$metaName])?implode(self::$metas[$metaName]):'';
    }
}
