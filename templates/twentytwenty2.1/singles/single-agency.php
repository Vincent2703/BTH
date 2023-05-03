<?php
    if(!defined("ABSPATH")) {
        exit; //Exit if accessed directly
    }
    if(have_posts()) {
        require_once(PLUGIN_RE_PATH."models/singles/AgencySingle.php");
        require_once(PLUGIN_RE_PATH."models/singles/AdSingle.php");
        $currency = AgencySingle::getCurrency();
        get_header();                    
        while(have_posts()) {
            the_post();

            $idAgency = get_the_id();
            AgencySingle::getData($idAgency);            
?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main agency">
                <div id="details">
                    <div id="thumbnail"><?php the_post_thumbnail(array(180, 180)); ?></div>
                    <div id="contact">
                        <span id="title"><?php the_title(); ?></span>
                        <ul>
                            <?php if(AgencySingle::$phone) { ?>
                            <li>
                                <span>Téléphone</span>
                                <span><a href="tel:<?= AgencySingle::$phone; ?>"><?= AgencySingle::$phone; ?></a></span>
                            </li>
                            <?php }if(AgencySingle::$email) { ?>
                            <li>
                                <span>Adresse mail</span>
                                <span><a href="mailto:<?= AgencySingle::$email; ?>"><?= AgencySingle::$email; ?></a></span>
                            </li>
                            <?php }if(AgencySingle::$address) { ?>
                            <li>
                                <span id="postalAddressLabel">Adresse postale</span>
                                <span><?= AgencySingle::$address; ?>&nbsp;<a id="linkGMaps" target="_blank" href="https://www.google.fr/maps/place/<?=urlencode(AgencySingle::$address);?>"><span class="dashicons dashicons-location"></span></a></span>
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
                    <?php foreach(AgencySingle::$agencyAds as $ad) {
                        $id = $ad->ID;
                        $descriptionAd = get_the_content(null, false, $id);
                        $maxLengthDescriptionAd = 35;
                        if(substr_count($descriptionAd, ' ') > $maxLengthDescriptionAd) {
                            $arrayDescriptionAd = explode(" ", $descriptionAd);
                            $shortDescriptionAd = implode(" ", array_splice($arrayDescriptionAd, 0, $maxLengthDescriptionAd)) . " [...]";
                        }else{
                            $shortDescriptionAd = $descriptionAd;
                        }                 
                        AdSingle::getData($id);         
                    
                    ?>
                    <div class="rowAd">
                        <div class="inShortAd">
                            <div class="thumbnailAd">
                                <a href="<?= get_post_permalink($id); ?>"><?= get_the_post_thumbnail($id, array(500, 500)); ?></a>
                                <span class="typeAd"><?php _e(AdSingle::$typeAd, "retxtdom"); ?></span>
                                <span class="typeProperty"><?= AdSingle::$typeProperty; ?></span>
                            </div>
                            <div class="detailsAd">
                                <span class="titleAd"><a href="<?= get_post_permalink($id); ?>"><?= get_the_title($id); ?></a></span>
                                <span class="address"><?= AdSingle::$address; ?></span>
                                <span class="shortDescription"><?= $shortDescriptionAd; ?></span>
                                <span class="price"><span class="includingFees"><?= AdSingle::$price.$currency; ?><?= AdSingle::$typeAd==="Location"?'/'.__("month", "retxtdom"):'';?></span>&nbsp;<span class="fees"><?= !empty(AdSingle::$fees)||AdSingle::$fees!=0?__("including", "retxtdom")."&nbsp;".AdSingle::$fees.$currency."&nbsp;of fees":'';?></span></span>
                                <span class="iconsDate">
                                    <span class="icons">
                                        <span class="surface"><span class="dashicons dashicons-fullscreen-alt"></span><span><?=intval(AdSingle::$surface)." m²";?></span></span>
                                        <?php if(!empty(AdSingle::$nbRooms) || AdSingle::$nbRooms != 0) { ?>
                                        <span class="nbRooms"><span class="dashicons dashicons-grid-view"></span><span><?=intval(AdSingle::$nbRooms);?></span></span>
                                        <?php } ?>
                                        <?php if(!empty(AdSingle::$nbBedrooms) || AdSingle::$nbBedrooms != 0) { ?>
                                        <span class="nbBedrooms"><span class="bedIcon"></span><span><?=intval(AdSingle::$nbBedrooms);?></span></span>
                                        <?php } ?>
                                        <?php if(!empty(AdSingle::$nbWaterRooms) || !empty(AdSingle::$nbBathrooms) || AdSingle::$nbWaterRooms != 0 || AdSingle::$nbBathrooms != 0) { ?>
                                        <span class="nbBathrooms"><span class="bathIcon"></span><span><?=intval(AdSingle::$nbWaterRooms)+intval(AdSingle::$nbBathrooms);?></span></span>
                                        <?php } ?>
                                        <?php if(AdSingle::$typeAd==="Rental" && AdSingle::$furnished == 1) { ?>
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