<?php 
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
get_header(); ?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main">
            <?php if(have_posts()) { //Rajouter une limite de posts?>
                <h3><span class="color-accent"><?php _e("Ads list", "retxtdom"); ?></span></h3>
                <?php if(PLUGIN_RE_REP && current_user_can("customer")) { ?>
                <button id="subscribeAlerts">test</button>
                <?php }
                while(have_posts()) {
                    the_post();
                    $idPost = get_the_id();
                    
                    if(wp_get_post_terms($idPost, "adAvailable")[0]->slug === "available") {
                        require_once(PLUGIN_RE_PATH."models/AdModel.php");
                        
                        $ad = REALM_AdModel::getAd($idPost);
                        $currency = REALM_AdModel::getCurrency();
                        ?>
                        <div class="rowAd">
                            <div class="inShortAd">
                                <div class="thumbnailAd">
                                    <a href="<?= get_post_permalink($idPost); ?>"><?= get_the_post_thumbnail($idPost, array(600, 600)); ?></a>
                                    <span class="typeAd"><?php _e($ad["typeAd"], "retxtdom"); ?></span>
                                    <span class="typeProperty"><?= $ad["typeProperty"]; ?></span>
                                    <?php if(!empty($ad["agency"]) && $ad["agency"]->exists()) { ?>
                                    <span class="titleAgency"><?= $ad["agency"]->display_name;?></span>
                                    <?php } ?>
                                </div>
                                <div class="detailsAd">
                                    <span class="titleAd"><a href="<?= get_post_permalink($idPost); ?>"><?= $ad["title"]; ?></a></span>
                                    <span class="address"><?= $ad["city"].' '.$ad["postalCode"]; ?></span>
                                    <span class="shortDescription"><?= $ad["shortDescription"]; ?></span>
                                    <span class="price"><span class="includingFees"><?= $ad["price"].$currency; ?><?= $ad["typeAd"]==="Location"?'/'.__("month", "retxtdom"):'';?></span>&nbsp;<span class="fees"><?= !empty($ad["fees"])||$ad["fees"]!=0?__("including", "retxtdom").' '.$ad["fees"]."$currency of fees":'';?></span></span>
                                    <span class="iconsDate">
                                        <span class="icons">
                                            <span class="surface"><span class="dashicons dashicons-fullscreen-alt"></span><span><?=intval($ad["surface"])." m²";?></span></span>
                                            <?php if(!empty($ad["nbRooms"]) || $ad["nbRooms"] != 0) { ?>
                                            <span class="nbRooms"><span class="dashicons dashicons-grid-view"></span><span><?=intval($ad["nbRooms"]);?></span></span>
                                            <?php } ?>
                                            <?php if(!empty($ad["nbBedrooms"]) || $ad["nbBedrooms"] != 0) { ?>
                                            <span class="nbBedrooms"><span class="bedIcon"></span><span><?=intval($ad["nbBedrooms"]);?></span></span>
                                            <?php } ?>
                                            <?php if(!empty($ad["nbWaterRooms"]) || !empty($ad["nbBathrooms"]) || $ad["nbWaterRooms"] != 0 || $ad["nbBathrooms"] != 0) { ?>
                                            <span class="nbBathrooms"><span class="bathIcon"></span><span><?=intval($ad["nbWaterRooms"])+intval($ad["nbBathrooms"]);?></span></span>
                                            <?php } ?>
                                            <?php if($ad["typeAd"]==="Rental" && $ad["furnished"] == 1) { ?>
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