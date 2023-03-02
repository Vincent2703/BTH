<?php
    require_once(preg_replace('/wp-content(?!.*wp-content).*/', '', __DIR__ )."wp-load.php");
    echo json_encode(get_posts(array("post_type" => "agency", "numberposts" => -1)));
?>