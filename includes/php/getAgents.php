<?php
    require(implode("/", (explode("/", __DIR__, -5)))."/wp-load.php");
    echo json_encode(get_posts(array("post_type" => "agent", "numberposts" => -1)));
?>