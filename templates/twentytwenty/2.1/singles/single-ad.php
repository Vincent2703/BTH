<?php
    if(!defined("ABSPATH")) {
        exit; //Exit if accessed directly
    }
    if(have_posts()) {
        require_once(PLUGIN_RE_PATH."models/singles/AdSingle.php");
        $currency = REALM_AdSingle::getCurrency();
        $feesURL = REALM_AdSingle::getFeesURL();
        while(have_posts()) {
            if (is_active_sidebar("before_content-side-bar") ) {
               dynamic_sidebar("before_content-side-bar");
            }
            the_post();

            $idPost = get_the_id();
            REALM_AdSingle::getData($idPost);

            if(wp_get_post_terms($idPost, "adAvailable")[0]->slug === "unavailable" /*&& !get_option(PLUGIN_RE_NAME."OptionsAds")["displayAdsUnavailable"]*/) {
                wp_redirect(get_home_url(), "302");
                exit();
            }
            get_header();  
                                  
            if(isset($_POST["submit"]) && isset($_POST["nonceSecurity"]) && wp_verify_nonce($_POST["nonceSecurity"], "formContact")) {
                if(isset($_POST["name"]) && isset($_POST["phone"]) && isset($_POST["email"]) && isset($_POST["message"]) && !ctype_space($_POST["names"]) && !ctype_space($_POST["phone"]) && !ctype_space($_POST["email"]) && !ctype_space($_POST["message"])) {
                    $adRef = REALM_AdSingle::$refAd;
                    $contactNames = sanitize_text_field($_POST["names"]);
                    $contactPhone = sanitize_text_field($_POST["phone"]);
                    $contactEmail = sanitize_email($_POST["email"]);
                    $messageInput = sanitize_textarea_field($_POST["message"]);

                    $subject = __("Message about the ad", "retxtdom").' '.REALM_AdSingle::$refAd;
                    $message = __("Message from", "retxtdom")." : $contactNames<br />"
                            . __("Phone", "retxtdom")." : $contactPhone - ".__("Email address", "retxtdom")." : $contactEmail<br />"
                            . __("About", "retxtdom")." \"" . get_the_title() . "\"<br /><br />"
                            . __("Message", "retxtdom")." :<br />"
                            . $messageInput;
                    $headers = array("Content-Type: text/html; charset=UTF-8");

                    if(wp_mail(REALM_AdSingle::$email, $subject, $message, $headers)) {
                        $emailStatus = __("The email has been sent successfully", "retxtdom");
                    }else{
                        $emailStatus = __("The email could not be sent", "retxtdom");
                    }
                }else{
                    $emailStatus = __("All fields are required", "retxtdom");
                }
            }

    ?>


        <div id="primary" class="content-area contentAd">
            <main id="main" class="site-main">
                <span class="titleAd"><h1><?php the_title(); ?></h1></span>
                <span class="subtitleAd"><?= ucfirst(REALM_AdSingle::$city)." - ".REALM_AdSingle::$price.REALM_AdSingle::$afterPrice; ?></span>
                <?php if(!empty(REALM_AdSingle::$imagesIds[0])) { ?>
                <div class="sliders">
                    <div id="miniSlider">
                        <span class="controlNext">></span>
                        <span class="controlPrev"><</span>
                        <span class="pagingImg"></span>
                        <ul>
                        <?php foreach (REALM_AdSingle::$imagesIds as $id) {
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
                <?php }else{ //TODO : CSS?>
                <br /><br /> 
                <?php } ?>
                <div class="contentLeftAd">
                    <div class="description">
                        <h4>Description</h4>
                        <?php the_content(); ?>
                    </div>
                    <div class="mainFeatures">
                        <h4><?php _e("Main features", "retxtdom"); ?></h4>
                        <div class="listFeatures">
                            <div>
                                <span class="nameFeature"><?php _e("Reference", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$refAd; ?></span>
                            </div>
                            <div>
                                <span class="nameFeature"><?php _e("Price", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$price.$currency; ?></span>
                            </div>
                            <div>
                                <span class="nameFeature"><?php _e("Fees", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$fees.$currency; ?></span>
                            </div>
                            <div>
                                <span class="nameFeature"><?php _e("Address", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$address; ?>&nbsp;<a id="linkGMaps" target="_blank" href="https://www.google.fr/maps/place/<?=urlencode(REALM_AdSingle::$address);?>"><span class="dashicons dashicons-location"></span></a></span>
                            </div>
                            <div>
                                <span class="nameFeature"><?php _e("Living space", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$surface; ?>m²</span>
                            </div>
                            <?php if(intval(REALM_AdSingle::$landSurface) > 0) { ?> 
                            <div>
                                <span class="nameFeature"><?php _e("Land area", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$landSurface; ?>m²</span>
                            </div>
                            <?php } ?>
                            <div>
                                <span class="nameFeature"><?php _e("Number of rooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$nbRooms; ?></span>
                            </div>
                            <?php if(intval(REALM_AdSingle::$nbBedrooms) > 0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Number of bedrooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$nbBedrooms; ?></span>
                            </div>
                            <?php } ?>
                            <?php if(intval(REALM_AdSingle::$nbWC) > 0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Number of toilets", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$nbWC; ?></span>
                            </div>
                            <?php } ?>
                            <?php if(intval(REALM_AdSingle::$nbBathrooms) > 0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Number of bathrooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$nbBathrooms; ?></span>
                            </div>
                            <?php } ?>
                            <?php if(intval(REALM_AdSingle::$nbWaterRooms) > 0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Number of shower rooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$nbWaterRooms ?></span>
                            </div>
                            <?php } ?>
                            <?php if(!empty(REALM_AdSingle::$customMainFields)) {
                                foreach(REALM_AdSingle::$customMainFields as $fieldName) {
                                    if(!empty(get_post_meta($idPost, "adCF".$fieldName, true))) { ?>
                                        <div>
                                            <span class="nameFeature"><?= $fieldName; ?></span>
                                            <span class="valueFeature"><?= get_post_meta($idPost, "adCF".$fieldName, true); ?></span>
                                        </div>
                                    <?php }
                                }
                            } ?>
                        </div>
                    </div>
                    <div class="complementaryFeatures">
                        <h4><?php _e("Additional features", "retxtdom"); ?></h4>
                        <div class="listFeatures">
                            <?php if(intval(REALM_AdSingle::$floor) > 0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Floor", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$floor; ?> (sur <?=REALM_AdSingle::$nbFloors;?>)</span>
                            </div>
                            <?php } ?>
                            <?php if(REALM_AdSingle::$furnished == '1' ) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Furnished", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <?php if(REALM_AdSingle::$elevator == '1' ) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Elevator", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <?php if(REALM_AdSingle::$basement == '1' ) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Basement", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <?php if(REALM_AdSingle::$terrace == '1' ) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Terrace", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <?php if(is_numeric(REALM_AdSingle::$year) && intval(REALM_AdSingle::$year)>0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Construction year", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= REALM_AdSingle::$year; ?></span>
                            </div>
                            <?php } ?>
                            <?php if(REALM_AdSingle::$typeHeating !== "Unknown") { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Type of heating", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e(REALM_AdSingle::$typeHeatingTranslated, "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <?php if(REALM_AdSingle::$typeKitchen !== "Unknown") { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Type of kitchen", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e(REALM_AdSingle::$typeKitchenTranslated, "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <div>
                                <span id="DPEName" class="nameFeature"><?php _e("EPD", "retxtdom"); ?>&nbsp;<abbr data-title="<?php _e("In kWhPE/m²/year", "retxtdom"); ?>"><sup>?</sup></abbr></span>
                                <span id="DPEValue" class="valueFeature"><?= REALM_AdSingle::$DPE; ?>&nbsp;</span>
                            </div>
                            <div>
                                <span id="GESName" class="nameFeature"><?php _e("Greenhouse gas", "retxtdom"); ?>&nbsp;<abbr data-title="<?php _e("In kg eqCO2/m²/year", "retxtdom"); ?>"><sup>?</sup></abbr></span>
                                <span id="GESValue" class="valueFeature"><?= REALM_AdSingle::$GES; ?>&nbsp;</span>
                            </div>
                            <?php if(!empty($customComplementaryFields)) {
                                foreach($customComplementaryFields as $fieldName) {
                                    if(!empty(get_post_meta($idPost, "adCF".$fieldName, true))) { ?>
                                        <div>
                                            <span class="nameFeature"><?= $fieldName; ?></span>
                                            <span class="valueFeature"><?= get_post_meta($idPost, "adCF".$fieldName, true); ?></span>
                                        </div>
                                    <?php }
                                }
                            } ?>
                        </div>
                    </div>
                    <?php if($feesURL !== false) { // If there is a fees schedule specified in the options ?> 
                        <span id="feesSchedule"><a target="_blank" href="<?=$feesURL;?>"><?php _e("Fees schedule", "retxtdom") ;?></a></span>
                    <?php } ?>
                </div>
                <div class="contentRightAd">
                    <?php if(REALM_AdSingle::$getCoords) {  
                        if(REALM_AdSingle::$showMap == "onlyPC") { ?>
                            <span id="addressApprox"><?php _e("The location of the property is approximate", "retxtdom"); ?>.</span>
                        <?php } ?>
                        <div id="map" class="map" data-coords="<?= implode(',', REALM_AdSingle::$coords); ?>"></div>
                    <?php } ?>
                    <div class="contact">
                        <div class="headerContact">
                            <?php if(REALM_AdSingle::$getContact) { ?>
                            <div class="headerContactLeft">
                            <?php if(!empty(REALM_AdSingle::$thumbnailContact)) {
                                if(isset(REALM_AdSingle::$linkAgency)&&REALM_AdSingle::$linkAgency!==false) { ?>
                                <a href="<?=REALM_AdSingle::$linkAgency;?>">
                                    <img src="<?= REALM_AdSingle::$thumbnailContact; ?>" alt="<?php _e("Contact's thumbnail", "retxtdom"); ?>" id="thumbnailContact">
                                </a>
                                <?php }else{ ?>
                                    <img src="<?= REALM_AdSingle::$thumbnailContact; ?>" alt="<?php _e("Contact's thumbnail", "retxtdom"); ?>" id="thumbnailContact">
                                <?php }
                            }?>
                            </div>
                            <div class="headerContactRight">
                                <span id="nameContact"><?= REALM_AdSingle::$nameContact; ?></span>
                                <?php if(isset(REALM_AdSingle::$phone) && REALM_AdSingle::$phone!==false || isset(REALM_AdSingle::$mobilePhone) && REALM_AdSingle::$mobilePhone!==false) { ?>
                                <table id="phoneContact">
                                    <tbody>
                                        <tr>
                                            <td id="phoneIcon" rowspan="2"><span class="material-symbols-outlined">call</span></td>
                                            <?php if(isset(REALM_AdSingle::$phone)) { ?><td id="phoneContact"><a href="tel:<?= REALM_AdSingle::$phone; ?>"><?= implode(' ', str_split(REALM_AdSingle::$phone, 2)); ?></a></td><?php } ?>
                                        </tr>
                                        <tr>
                                            <?php if(isset(REALM_AdSingle::$mobilePhone)) { ?><td><span id="mobilePhoneContact"><a href="tel:<?= REALM_AdSingle::$mobilePhone; ?>"><?= implode(' ', str_split(REALM_AdSingle::$mobilePhone, 2)); ?></a></span></td><?php } ?>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php } ?>
                            </div>
                            <?php }else{ ?>
                            <span id="noContactTitle"><?php _e("Contact form", "retxtdom"); ?></span>
                            <?php } ?>
                        </div>
                        <form action="" method="post" class="formContact">
                            <?php wp_nonce_field("formContact", "nonceSecurity"); ?>
                            <label for="names"><?php _e("First name and last name", "retxtdom"); ?></label><input type="text" name="names" class="formContactInput" required>
                            <label for="phone"><?php _e("Phone", "retxtdom"); ?></label><input type="tel" name="phone" class="formContactInput" required>
                            <label for="email"><?php _e("Email address", "retxtdom"); ?></label><input type="text" name="email" class="formContactInput" required>
                            <label for="message"><?php _e("Message", "retxtdom"); ?></label><textarea name="message" class="formContactInput" cols="22" required></textarea>
                            <?php if(isset($emailStatus)) { ?>
                                <span id="emailStatus"><?=$emailStatus;?>.</span><br />
                            <?php } ?>
                            <input type="submit" name="submit" id="formContactSubmit" value="<?php _e("Send", "retxtdom"); ?>">
                        </form>
                    </div>
                </div>
                <?php if(!empty(REALM_AdSingle::$morePosts)) { ?>
                <div class="more">
                    <span id="moreTitle"><?php _e("Other", "retxtdom"); ?> <?= lcfirst(REALM_AdSingle::$typeAd); ?>s <?= _e("in", "retxtdom"); ?> <?= ucfirst(REALM_AdSingle::$city); ?></span><br />
                    <div class="morePosts">
                        <?php 
                            $nbPosts = count(REALM_AdSingle::$morePosts);
                            $adByPanel = 5;
                            $nbPanels = ceil($nbPosts/$adByPanel);
                            for($i=0; $i<$nbPanels; $i++) { ?>
                                <div class="morePostsPanel" <?= $i>0 ? 'style="display: none;"':'';?>>
                                    <span class="prevMorePosts" <?= $nbPanels<$adByPanel ? 'style="display: none;"':'';?>><</span>
                                    <?php for($y=0; $y<$adByPanel; $y++) {
                                        $currentNbPost = $i*5+$y;
                                        if(isset(REALM_AdSingle::$morePosts[$currentNbPost]) && get_the_post_thumbnail_url(REALM_AdSingle::$morePosts[$currentNbPost]) !== false) { 
                                            $morePost = REALM_AdSingle::$morePosts[$currentNbPost];?>
                                            <div class="moreAd">
                                                <div class="moreThumbnailAd">
                                                    <?= '<a href="'.get_post_permalink($morePost).'">'.get_the_post_thumbnail($morePost, "thumbnail").'</a>' ;?>
                                                </div>
                                                <span class="moreTitleAd"><?= '<a href="'.get_post_permalink($morePost).'">'.get_the_title($morePost).'</a>' ;?></span>
                                            </div>
                                        <?php }
                                    } ?>
                                    <span class="nextMorePosts" <?= $nbPanels<$adByPanel ? 'style="display: none;"':'';?>>></span>
                                </div>
                            <?php }
                        ?>
                    </div>
                </div>
        <?php 
            }
        }       
    }    
    ?>
            </main>
        </div>

<?php
get_footer();