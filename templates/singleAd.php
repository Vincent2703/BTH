<?php

get_header();
?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">
                <?php
                $id = get_the_id();
                $price = get_post_meta($id, "adPrice", true);
                $beforePrice = get_post_meta($id, "adBeforePrice", true);
                $afterPrice = get_post_meta($id, "adAfterPrice", true);
                $images = get_post_meta($id, "adImages", true);

                while(have_posts()) {
                    the_post(); //Stop the iterator on the post
                    
                    //get_the_post_thumbnail(); //Display the thumbnail
                                       
                    the_title(); //The title
                    the_content(); //The description
                    
                    
                    echo 
                        'Prix : '. $beforePrice .' '. $price . $afterPrice .'<br />'
                       .''
                        
                    ;
                    
                    echo "Localisation du bien : ";
                    $showMap = get_post_meta($id, "adShowMap", true);
                    if($showMap !== "no" && $showMap) {
                        if($showMap === "onlyPC") {
                            $address = get_post_meta($id, "adPC", true).", ".get_post_meta($id, "adCity", true);
                        }else if($showMap === "all"){
                            $address = get_post_meta($id, "adAddress", true);
                        }
                        echo $address;
                        echo'<div id="map" data-coord="'.get_post_meta($id, "adDataMap", true).'"></div>';
                    }
                    
                    
                    /*if(!is_null($images)) {
                        $ids = explode(';', $images);
                        foreach ($ids as $id) {
                            echo wp_get_attachment_image($id, "thumbnail");
                        }
                    }*/
                    
                    if(!is_null($images)) {
                        $ids = explode(';', $images);
                    ?>               
                        <div id="slider">
                            <span class="control_next">></span>
                            <span class="control_prev"><</span>
                            <ul>
                            <?php foreach ($ids as $id) {
                                echo "<li>".wp_get_attachment_image($id, array("500", "500"))."</li>";
                            } ?>
                            </ul>
                        </div>  
                        <div id="fullscreen" onclick="this.style.display='none';"></div>

                    <?php
                    }
                } 
                
                ?>
        </main>
    </div>

<?php
get_footer();