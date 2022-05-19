<?php
    require($_SERVER['DOCUMENT_ROOT'] . "/bth/wp-load.php");
    echo json_encode(get_posts(array("post_type" => "agent", "numberposts" => -1)));
?>