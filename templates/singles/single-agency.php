<?php
    if(have_posts()) {
        get_header();                    
        while(have_posts()) { //?
            the_post();

            $idAgency = get_the_id();
            
            $agentsAgency = get_posts(array(
                "post_type" => "agent",
                "numberposts" => -1,
                "post_parent" => $idAgency
            ));
           
            
            
            $agencyAds = array();
            foreach($agentsAgency as $agent) {
                array_push($agencyAds, 
                    get_posts(array(
                        "post_type" => "re-ad",
                        "numberposts" => -1,
                        "meta_query" => array(
                            array(
                                "key" => "adIdAgent",
                                "value" => $agent->ID
                            ),
                            array(
                                "key" => "_thumbnail_id"
                            )
                        ),
                        "tax_query" => array(
                            array(
                                "taxonomy" => "adAvailable",
                                "field" => "slug",
                                "terms" => "available"
                            )
                        )
                    ))
                );
            }            
?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main agency">
                <div id="details">
                    <div id="thumbnail"><?php the_post_thumbnail("thumbnail"); ?></div>
                    <div id="contact">
                        <span id="title"><?php the_title(); ?></span>
                        <ul>
                            <?php if($phone = get_post_meta($idAgency, "agencyPhone", true)) { ?>
                            <li>
                                <span>Téléphone</span>
                                <span><a href="tel:<?= $phone; ?>"><?= $phone; ?></a></span>
                            </li>
                            <?php }if($email = get_post_meta($idAgency, "agencyEmail", true)) { ?>
                            <li>
                                <span>Adresse mail</span>
                                <span><a href="mailto:<?= $email; ?>"><?= $email; ?></a></span>
                            </li>
                            <?php }if($address = get_post_meta($idAgency, "agencyAddress", true)) { ?>
                            <li>
                                <span>Adresse postale</span>
                                <span><i><?= $address; ?></i></span>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div id="description">
                    <?php the_content(); ?>
                </div>
                <div id="lastAds">
                    <?php foreach($agencyAds as $ad) {
                        $id = $ad[0]->ID;
                        $descriptionAd = get_the_content($id);
                        $maxLengthDescriptionAd = 20;
                        if(substr_count($descriptionAd, ' ') > $maxLengthDescriptionAd) {
                            $arrayDescriptionAd = explode(" ", $descriptionAd);
                            $shortDescriptionAd = implode(" ", array_splice($arrayDescriptionAd, 0, $maxLengthDescriptionAd)) . " [...]";
                        }else{
                            $shortDescriptionAd = $descriptionAd;
                        }                 
                        $typeAd = get_the_terms($id, "adTypeAd")[0]->name;
                        $typeProperty = get_the_terms($id, "adTypeProperty")[0]->name;
                        $city = get_post_meta($id, "adCity", true);
                        $postalCode = get_post_meta($id, "adPostCode", true);
                        $price = get_post_meta($id, "adPrice", true);
                        $fees = get_post_meta($id, "adFees", true);
                        $currency = get_option(PLUGIN_RE_NAME."OptionsDisplayads")["currency"];
                        $surface = get_post_meta($id, "adSurface", true);
                        $nbRooms = get_post_meta($id, "adNbRooms", true);
                        $nbBedrooms = get_post_meta($id, "adNbBedrooms", true);
                        $nbWaterRooms = get_post_meta($id, "adNbWaterRooms", true);
                        $nbBathrooms = get_post_meta($id, "adNbBathrooms", true);
                        $furnished = get_post_meta($id, "adFurnished", true);
                    }
                    ?>
                    <div class="rowAd">
                        <div class="inShortAd">
                            <div class="thumbnailAd">
                                <a href="<?= get_post_permalink($idPost); ?>"><?= get_the_post_thumbnail($id, array(600, 600)); ?></a>
                                <span class="typeAd"><?php _e($typeAd, "retxtdom"); ?></span>
                                <span class="typeProperty"><?= $typeProperty; ?></span>
                            </div>
                            <div class="detailsAd">
                                <span class="titleAd"><a href="<?= get_post_permalink($idPost); ?>"><?php the_title($id); ?></a></span>
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
                </div>
            </main>
        </div>
<?php
        }
    }
    get_footer();
?>