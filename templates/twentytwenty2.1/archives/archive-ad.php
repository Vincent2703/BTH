<?php get_header(); ?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main">
            <?php if(have_posts()) { //Rajouter une limite de posts?>
                <h3>Liste des <span class="color-accent"><?php _e("Ads", "retxtdom"); ?></span></h3>
                <?php
                while(have_posts()) {
                    the_post();
                    $idPost = get_the_id();
                    
                    if(wp_get_post_terms($idPost, "adAvailable")[0]->slug === "available") {
   
                        $descriptionAd = get_the_content();
                        $maxLengthDescriptionAd = 35;
                        if(substr_count($descriptionAd, ' ') > $maxLengthDescriptionAd) {
                            $arrayDescriptionAd = explode(" ", $descriptionAd);
                            $shortDescriptionAd = implode(" ", array_splice($arrayDescriptionAd, 0, $maxLengthDescriptionAd)) . " [...]";
                        }else{
                            $shortDescriptionAd = $descriptionAd;
                        }                 
                        $typeAd = get_the_terms($idPost, "adTypeAd")[0]->name;
                        $typeProperty = get_the_terms($idPost, "adTypeProperty")[0]->name;
                        $city = get_post_meta($idPost, "adCity", true);
                        $postalCode = get_post_meta($idPost, "adPostCode", true);
                        $price = get_post_meta($idPost, "adPrice", true);
                        $fees = get_post_meta($idPost, "adFees", true);
                        $currency = get_option(PLUGIN_RE_NAME."OptionsLanguage")["currency"];
                        $surface = get_post_meta($idPost, "adSurface", true);
                        $nbRooms = get_post_meta($idPost, "adNbRooms", true);
                        $nbBedrooms = get_post_meta($idPost, "adNbBedrooms", true);
                        $nbWaterRooms = get_post_meta($idPost, "adNbWaterRooms", true);
                        $nbBathrooms = get_post_meta($idPost, "adNbBathrooms", true);
                        $furnished = get_post_meta($idPost, "adFurnished", true);
                        ?>
                        <div class="rowAd">
                            <div class="inShortAd">
                                <div class="thumbnailAd">
                                    <a href="<?= get_post_permalink($idPost); ?>"><?= get_the_post_thumbnail($idPost, array(600, 600)); ?></a>
                                    <span class="typeAd"><?php _e($typeAd, "retxtdom"); ?></span>
                                    <span class="typeProperty"><?= $typeProperty; ?></span>
                                </div>
                                <div class="detailsAd">
                                    <span class="titleAd"><a href="<?= get_post_permalink($idPost); ?>"><?php the_title(); ?></a></span>
                                    <span class="address"><?= "$city $postalCode"; ?></span>
                                    <span class="shortDescription"><?= $shortDescriptionAd; ?></span>
                                    <span class="price"><span class="includingFees"><?= $price.$currency; ?><?= $typeAd==="Location"?'/'.__("month", "retxtdom"):'';?></span>&nbsp;<span class="fees"><?= !empty($fees)||$fees!=0?__("including", "retxtdom")." $fees$currency of fees":'';?></span></span>
                                    <span class="iconsDate">
                                        <span class="icons">
                                            <span class="surface"><span class="dashicons dashicons-fullscreen-alt"></span><span><?=intval($surface)." m²";?></span></span>
                                            <?php if(!empty($nbRooms) || $nbRooms != 0) { ?>
                                            <span class="nbRooms"><span class="dashicons dashicons-grid-view"></span><span><?=intval($nbRooms);?></span></span>
                                            <?php } ?>
                                            <?php if(!empty($nbBedrooms) || $nbBedrooms != 0) { ?>
                                            <span class="nbBedrooms"><span class="bedIcon"></span><span><?=intval($nbBedrooms);?></span></span>
                                            <?php } ?>
                                            <?php if(!empty($nbWaterRooms) || !empty($nbBathrooms) || $nbWaterRooms != 0 || $nbBathrooms != 0) { ?>
                                            <span class="nbBathrooms"><span class="bathIcon"></span><span><?=intval($nbWaterRooms)+intval($nbBathrooms);?></span></span>
                                            <?php } ?>
                                            <?php if($typeAd==="Rental" && $furnished == 1) { ?>
                                            <span class="furnished"><span class="dashicons dashicons-archive"></span><span><?php _e("furnished", "retxtdom");?></span></span>
                                            <?php } ?>
                                        </span>
                                        <span class="date"><?= get_the_date(); ?></span>
                                    </span>

                                </div>
                            </div>
                        </div>
                    <?php }
                } 
            }else{ ?>
                <?php get_template_part("content", "none" ); ?>
            <?php } ?>

            </main>
	</div>

<?php get_footer(); ?>