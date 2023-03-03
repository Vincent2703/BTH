<?php
    require_once(preg_replace('/wp-content(?!.*wp-content).*/', '', __DIR__ )."wp-load.php");
    
    function getAgents() {
        if(!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest") {
            return get_posts(array("post_type" => "agent", "numberposts" => -1));
        }
    }
?>