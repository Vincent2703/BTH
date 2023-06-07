<?php
    if(!defined("ABSPATH")) {
        exit; //Exit if accessed directly
    }
    require_once(preg_replace("/wp-content(?!.*wp-content).*/", '', __DIR__ )."wp-load.php");
    
    if(!function_exists("getAgencies")) {
        function getAgencies() {
            if(!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest") {
                return get_posts(array("post_type" => "agency", "numberposts" => 99));
            }
        }
    }
?>