<?php
    if(!defined("ABSPATH")) {
        exit; //Exit if accessed directly
    }
    if(have_posts()) {
        require_once(PLUGIN_RE_PATH."models/singles/AgencySingle.php");
        require_once(PLUGIN_RE_PATH."models/singles/AdSingle.php");
        $currency = REALM_AgencySingle::getCurrency();
        get_header();                    
        while(have_posts()) {
            the_post();

            $idAgency = get_the_id();
            REALM_AgencySingle::getData($idAgency);            
?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main agency">
                <div id="details">
                    <div id="thumbnail"><?php the_post_thumbnail(array(180, 180)); ?></div>
                    <div id="contact">
                        <span id="title"><?php the_title(); ?></span>
                        <ul>
                            <?php if(REALM_AgencySingle::$phone) { ?>
                            <li>
                                <span><?php _e("Phone", "retxtdom"); ?></span>
                                <span><a href="tel:<?= REALM_AgencySingle::$phone; ?>"><?= REALM_AgencySingle::$phone; ?></a></span>
                            </li>
                            <?php }if(REALM_AgencySingle::$email) { ?>
                            <li>
                                <span><?php _e("Email address", "retxtdom"); ?></span>
                                <span><a href="mailto:<?= REALM_AgencySingle::$email; ?>"><?= REALM_AgencySingle::$email; ?></a></span>
                            </li>
                            <?php }if(REALM_AgencySingle::$address) { ?>
                            <li>
                                <span id="postalAddressLabel"><?php _e("Postal address", "retxtdom"); ?></span>
                                <span><?= REALM_AgencySingle::$address; ?>&nbsp;<a id="linkGMaps" target="_blank" href="https://www.google.fr/maps/place/<?=urlencode(REALM_AgencySingle::$address);?>"><span class="dashicons dashicons-location"></span></a></span>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div id="description">
                    <?php the_content(); ?>
                </div>
                <div id="lastAds">
                    <h3 class="color-accent"><?php _e("Last ads published by the agency", "retxtdom"); ?></h3>
                    <?php foreach(REALM_AgencySingle::$agencyAds as $ad) {
                        $id = $ad->ID;
                        $descriptionAd = get_the_content(null, false, $id);
                        $maxLengthDescriptionAd = 35;
                        if(substr_count($descriptionAd, ' ') > $maxLengthDescriptionAd) {
                            $arrayDescriptionAd = explode(" ", $descriptionAd);
                            $shortDescriptionAd = implode(" ", array_splice($arrayDescriptionAd, 0, $maxLengthDescriptionAd)) . " [...]";
                        }else{
                            $shortDescriptionAd = $descriptionAd;
                        }                 
                        REALM_AdSingle::getData($id);         
                    
                    ?>
                    <div class="rowAd">
                        <div class="inShortAd">
                            <div class="thumbnailAd">
                                <a href="<?= get_post_permalink($id); ?>"><?= get_the_post_thumbnail($id, array(500, 500)); ?></a>
                                <span class="typeAd"><?php _e(REALM_AdSingle::$typeAd, "retxtdom"); ?></span>
                                <span class="typeProperty"><?= REALM_AdSingle::$typeProperty; ?></span>
                            </div>
                            <div class="detailsAd">
                                <span class="titleAd"><a href="<?= get_post_permalink($id); ?>"><?= get_the_title($id); ?></a></span>
                                <span class="address"><?= REALM_AdSingle::$address; ?></span>
                                <span class="shortDescription"><?= $shortDescriptionAd; ?></span>
                                <span class="price"><span class="includingFees"><?= REALM_AdSingle::$price.$currency; ?><?= REALM_AdSingle::$typeAd==="Location"?'/'.__("month", "retxtdom"):'';?></span>&nbsp;<span class="fees"><?= !empty(REALM_AdSingle::$fees)||REALM_AdSingle::$fees!=0?__("including", "retxtdom")."&nbsp;".REALM_AdSingle::$fees.$currency."&nbsp;of fees":'';?></span></span>
                                <span class="iconsDate">
                                    <span class="icons">
                                        <span class="surface"><span class="dashicons dashicons-fullscreen-alt"></span><span><?=intval(REALM_AdSingle::$surface)." m²";?></span></span>
                                        <?php if(!empty(REALM_AdSingle::$nbRooms) || REALM_AdSingle::$nbRooms != 0) { ?>
                                        <span class="nbRooms"><span class="dashicons dashicons-grid-view"></span><span><?=intval(REALM_AdSingle::$nbRooms);?></span></span>
                                        <?php } ?>
                                        <?php if(!empty(REALM_AdSingle::$nbBedrooms) || REALM_AdSingle::$nbBedrooms != 0) { ?>
                                        <span class="nbBedrooms"><span class="bedIcon"></span><span><?=intval(REALM_AdSingle::$nbBedrooms);?></span></span>
                                        <?php } ?>
                                        <?php if(!empty(REALM_AdSingle::$nbWaterRooms) || !empty(REALM_AdSingle::$nbBathrooms) || REALM_AdSingle::$nbWaterRooms != 0 || REALM_AdSingle::$nbBathrooms != 0) { ?>
                                        <span class="nbBathrooms"><span class="bathIcon"></span><span><?=intval(REALM_AdSingle::$nbWaterRooms)+intval(REALM_AdSingle::$nbBathrooms);?></span></span>
                                        <?php } ?>
                                        <?php if(REALM_AdSingle::$typeAd==="Rental" && REALM_AdSingle::$furnished == 1) { ?>
                                        <span class="furnished"><span class="dashicons dashicons-archive"></span><span><?php _e("furnished", "retxtdom");?></span></span>
                                        <?php } ?>
                                    </span>
                                    <span class="date"><?= get_the_date(); ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                </div>
            </main>
        </div>
<?php
        }
    }
    get_footer();
?>