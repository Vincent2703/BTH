<?php 
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
get_header(); ?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main">
            <?php if(have_posts()) {
                global $wp_query;
                $nbAds = $wp_query->found_posts; ?>
                
                <h3><span class="color-accent"><?= sprintf("%d %s", $nbAds, __("ads found", "retxtdom")); ?></span></h3>
                <?php
                while(have_posts()) {
                    the_post();
                    $idPost = get_the_id();
                    
                    require_once(PLUGIN_RE_PATH."models/AdModel.php");

                    $ad = REALM_AdModel::getAd($idPost);
                    $currency = REALM_AdModel::getCurrency();
                    $areaUnit = REALM_AdModel::getAreaUnit();
                    ?>
                    <div class="rowAd">
                        <div class="inShortAd">
                            <div class="thumbnailAd">
                                <a href="<?= get_post_permalink($idPost); ?>"><?= get_the_post_thumbnail($idPost, array(600, 600)); ?></a>
                                <span class="typeAd"><?php _e($ad["taxonomies"]["typeAd"]["name"], "retxtdom"); ?></span>
                                <span class="typeProperty"><?= $ad["taxonomies"]["typeProperty"]["name"]; ?></span>
                                <?php if(!empty($ad["agency"]) && $ad["agency"]->exists()) { ?>
                                <span class="titleAgency"><?= $ad["agency"]->display_name;?></span>
                                <?php } ?>
                            </div>
                            <div class="detailsAd">
                                <span class="titleAd"><a href="<?= get_post_permalink($idPost); ?>"><?= $ad["title"]; ?></a></span>
                                <span class="address"><?= $ad["city"].' '.$ad["postalCode"]; ?></span>
                                <span class="shortDescription"><?= $ad["shortDescription"]; ?></span>
                                <span class="price"><span class="includingFees"><?= $ad["price"].$currency; ?><?= $ad["taxonomies"]["typeAd"]["slug"]==="rental"?'/'.__("month", "retxtdom"):'';?></span>&nbsp;<span class="fees"><?= !empty($ad["fees"])||$ad["fees"]!=0?__("including", "retxtdom").' '.$ad["fees"]."$currency of fees":'';?></span></span>
                                <span class="iconsDate">
                                    <span class="icons">
                                        <span class="surface"><span class="dashicons dashicons-fullscreen-alt"></span><span><?=$ad["surface"]." $areaUnit";?></span></span>
                                        <?php if(!empty($ad["nbRooms"]) || $ad["nbRooms"] != 0) { ?>
                                        <span class="nbRooms"><span class="dashicons dashicons-grid-view"></span><span><?=$ad["nbRooms"];?></span></span>
                                        <?php } ?>
                                        <?php if(!empty($ad["nbBedrooms"]) || $ad["nbBedrooms"] != 0) { ?>
                                        <span class="nbBedrooms"><span class="bedIcon"></span><span><?=$ad["nbBedrooms"];?></span></span>
                                        <?php } ?>
                                        <?php if(!empty($ad["nbWaterRooms"]) || $ad["nbWaterRooms"] != 0) { ?>
                                        <span class="nbWaterRooms"><span class="showerIcon"></span><span><?=$ad["nbWaterRooms"];?></span></span>
                                        <?php } ?>
                                        <?php if(!empty($ad["nbBathrooms"]) || $ad["nbBathrooms"] != 0) { ?>
                                        <span class="nbBathrooms"><span class="bathIcon"></span><span><?=$ad["nbBathrooms"];?></span></span>
                                        <?php } ?>
                                        <?php if($ad["taxonomies"]["typeAd"]["slug"]==="rental" && $ad["furnished"] == 1) { ?>
                                        <span class="furnished"><span class="dashicons dashicons-archive"></span><span><?php _e("furnished", "retxtdom");?></span></span>
                                        <?php } ?>
                                        <?php if($ad["outdoorSpace"]) { ?>
                                        <span class="outdoorSpace"><span class="exteriorIcon"></span><span><?php _e("Outdoor space", "retxtdom");?></span></span>
                                        <?php } ?>
                                    </span>
                                    <span class="date"><?= get_the_date(); ?></span>
                                </span>

                            </div>
                        </div>
                    </div>
                <?php 
                }
                wp_reset_postdata();

                ?><div class="paginationAds"><?php the_posts_pagination(array("next_text" => '>', "prev_text" => '<')); ?></div>
                
            <?php }else{ ?>
                <?php get_template_part("content", "none" ); ?>
            <?php } ?>

            <?php if(defined("PLUGIN_RE_REP") && PLUGIN_RE_REP && current_user_can("customer")) /*replace by get userdata*/{ ?>
            <span id="btnSubscribeAlert" data-nonce="<?=wp_create_nonce("setAlertNonce");?>">
                <button id="subscribeAlert"><?php _e("Save the search", "reptxtdom"); ?></button>
            </span>
            <?php } ?> 
            </main>
	</div>

<?php get_footer(); ?>