<?php

get_header();
?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main">
                <?php
                $id = get_the_id();
                $price = get_post_meta($id, "adPrice", true);
                $images = get_post_meta($id, "adImages", true);
                $typeAd = get_the_terms($id, "adTypeAd")[0]->name;
                $afterPrice = '€';
                if($typeAd === "Location") {
                    $afterPrice .= "/mois";
                }

                while(have_posts()) {
                    the_post(); //Stop the iterator on the post
                    
                    if(!is_null($images)) {
                        $imagesIds = explode(';', $images);
                    }
                    
                    $showMap = get_post_meta($id, "adShowMap", true);
                    if($showMap !== "no" && $showMap) {
                        if($showMap === "onlyPC") {
                            $address = get_post_meta($id, "adPC", true).", ".get_post_meta($id, "adCity", true);
                        }else if($showMap === "all"){
                            $address = get_post_meta($id, "adAddress", true);
                        }
                        $coords = get_post_meta($id, "adDataMap", true);
                    }
                    ?>
                    
            <span class="titleAd"><h1><?= the_title(); ?></h1></span>
            <span class="subtitleAd"><?= "$address - $price$afterPrice"; ?></span>
            <div class="sliders">
                <div id="miniSlider">
                    <span class="controlNext">></span>
                    <span class="controlPrev"><</span>
                    <span class="pagingImg"></span>
                    <ul>
                    <?php foreach ($imagesIds as $id) {
                        echo "<li>".wp_get_attachment_image($id, "large")."</li>";
                    } ?>
                    </ul>
                </div>  
                <div id="fullscreenSlider">
                    <div class="displayFullscreen"></div>
                    <span class="controlClose">&times;</span>
                    <span class="controlNext">></span>
                    <span class="controlPrev"><</span>
                    <span class="pagingImg"></span>
                </div>
            </div>
            <div class="left">
                <div class="description">
                  <?= the_content(); ?>
                </div>
                <div class="princ">
                  Caractéristiques principales
                </div>
                <div class="comp">
                  Caractéristiques complémentaires
                </div>
            </div>
            <div class="right">
                <div id="map" data-coords="<?= implode(',', $coords); ?>"></div>
                <div class="contact">
                  Contact
                </div>
            </div>
            <div class="more">
              Autres annonces
            </div>

                <?php
                    
                    
                    
                    /*
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
                        $coords = get_post_meta($id, "adDataMap", true);
                        echo "<div id='map' data-coords='".implode(',', $coords)."'></div>";
                    }*/
                    
                    
                    /*if(!is_null($images)) {
                        $ids = explode(';', $images);
                        foreach ($ids as $id) {
                            echo wp_get_attachment_image($id, "thumbnail");
                        }
                    }*/
                    
                    

                    
                    
                } 
                
                ?>
        </main>
    </div>

<?php
get_footer();