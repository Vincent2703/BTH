<?php
    if(have_posts()) {
        $currency = get_option(PLUGIN_RE_NAME."OptionsDisplayads")["currency"];
        while(have_posts()) {
            if (is_active_sidebar("before_content-side-bar") ) {
               dynamic_sidebar("before_content-side-bar");
            }
            the_post();

            $idPost = get_the_id();

            if(wp_get_post_terms($idPost, "adAvailable")[0]->slug === "unavailable" /*&& !get_option(PLUGIN_RE_NAME."OptionsAds")["displayAdsUnavailable"]*/) {
                wp_redirect(get_home_url(), "302");
                exit();
            }
            get_header();  
            
            $metas = get_post_custom();
           

            $price = getMeta("adPrice");
            $images = getMeta("adImages");
            $typeAd = get_the_terms($idPost, "adTypeAd")[0]->name;
            $typeAdSlug = get_the_terms($idPost, "adTypeAd")[0]->slug;
            $afterPrice = $currency;
            if($typeAdSlug === "rental") {
                $afterPrice .= '/'.__("month", "retxtdom");
            }

            if(!is_null($images)) {
                $imagesIds = explode(';', $images);
            }

            $showMap = getMeta("adShowMap");
            if($showMap !== "no" && $showMap) {
                if($showMap === "onlyPC") {
                    $address = getMeta("adCity").' '.getMeta("adPostCode");
                    $optionsApis = get_option(PLUGIN_RE_NAME."OptionsApis");
                    $displayAdminLvl1 = $optionsApis["apiAdminAreaLvl1"] == 1;
                    $displayAdminLvl2 = $optionsApis["apiAdminAreaLvl2"] == 1;
                    if($displayAdminLvl2 && !empty(getMeta("adAdminLvl2"))) {
                        $address .= ' '.getMeta("adAdminLvl2");
                    }
                    if($displayAdminLvl1 && !empty(getMeta("adAdminLvl1"))) {
                        $address .= ' '.getMeta("adAdminLvl1");
                    }
                }else if($showMap === "all"){
                    $address = getMeta("adAddress");
                }
                $coords = unserialize(getMeta("adDataMap"));
            }
            if(isset($coords) && !empty($coords) && is_array($coords)) {
                $getCoords = true;
            }else{
                $getCoords = false;
            }
            $city = getMeta("adCity");

            if(!empty($idContact = getMeta("adIdAgent"))) {
                $getContact = true;
                if(getMeta("adShowAgent") == '1') {
                    $emailToContact = get_post_meta($idContact, "agentEmail", true);
                    $phone = get_post_meta($idContact, "agentPhone", true);
                    $mobilePhone = get_post_meta($idContact, "agentMobilePhone", true);
                }else{
                    $idContact = wp_get_post_parent_id($idContact);
                    $emailToContact = get_post_meta($idContact, "agencyEmail", true);
                    $phone = get_post_meta($idContact, "agencyPhone", true);
                }
                $thumbnailContact = get_the_post_thumbnail_url($idContact, "thumbnail");
                $nameContact = get_the_title($idContact);
            }else{
                $emailToContact = get_option(PLUGIN_RE_NAME."OptionsEmail")["emailAd"];
                $getContact = false;
            }

            if(isset($_POST["submit"])) {         
                $adRef = getMeta("adRefAgency");
                $names = sanitize_text_field($_POST["names"]);
                $phone = sanitize_text_field($_POST["phone"]);
                $email = sanitize_email($_POST["email"]);
                $messageInput = sanitize_textarea_field($_POST["message"]);

                $subject = __("Message about the ad", "retxtdom")." $adRef";
                $message = __("Message from", "retxtdom")." : $names<br />"
                        . __("Phone", "retxtdom")." : $phone - ".__("Email address", "retxtdom")." : $email<br />"
                        . __("About", "retxtdom")." \"" . get_the_title() . "\"<br /><br />"
                        . __("Message", "retxtdom")." :<br />"
                        . $messageInput;
                $headers = array("Content-Type: text/html; charset=UTF-8");

                if(wp_mail($emailToContact, $subject, $message, $headers)) {
                    _e("The email has been sent successfully", "retxtdom").'.';
                }else{
                    _e("The email could not be sent", "retxtdom").'.';
                }
            }

            $morePosts = get_posts(array(
                "post_type" => "re-ad",
                "numberposts" => 15,
                "exclude" => $idPost,
                "meta_query" => array(
                    array(
                        "key" => "adCity",
                        "value" => $city
                    ),
                    array(
                        "key" => "_thumbnail_id"
                    )
                ),
                "tax_query" => array(
                    array(
                        "taxonomy" => "adTypeAd",
                        "field" => "name",
                        "terms" => $typeAd
                    ),
                    array(
                        "taxonomy" => "adAvailable",
                        "field" => "slug",
                        "terms" => "available"
                    )
                )
            ));
            
            $optionsDisplayads = get_option(PLUGIN_RE_NAME."OptionsDisplayads")["customFields"];
            $customMainFields = array();
            $customComplementaryFields = array();
            if(!empty($optionsDisplayads) || $optionsDisplayads !== "[]") {
               foreach(json_decode($optionsDisplayads, true) as $field) {
                   if($field["section"] === "mainFeatures") {
                       array_push($customMainFields, $field["name"]);
                   }else if($field["section"] === "complementaryFeatures") {
                       array_push($customComplementaryFields, $field["name"]);
                   }
               }
            }

    ?>


        <div id="primary" class="content-area contentAd">
            <main id="main" class="site-main">
                <span class="titleAd"><h1><?php the_title(); ?></h1></span>
                <span class="subtitleAd"><?= ucfirst($city)." - $price$afterPrice"; ?></span>
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
                <div class="contentLeftAd">
                    <div class="description">
                        <h4>Description</h4>
                        <?php the_content(); ?>
                    </div>
                    <div class="mainFeatures">
                        <h4><?php _e("Main characteristics", "retxtdom"); ?></h4>
                        <ul>
                            <li>
                                <span class="nameFeature"><?php _e("Reference", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adRefAgency"); ?></span>
                            </li>
                            <li>
                                <span class="nameFeature"><?php _e("Price", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adPrice"); ?>€</span>
                            </li>
                            <li>
                                <span class="nameFeature"><?php _e("Fees", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adFees"); ?>€</span>
                            </li>
                            <li>
                                <span class="nameFeature"><?php _e("Address", "retxtdom"); ?></span>
                                <span class="valueFeature"><a target="_blank" href="https://www.google.fr/maps/place/<?=$address;?>"><?= $address; ?></a></span>
                            </li>
                            <li>
                                <span class="nameFeature"><?php _e("Living space", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adSurface"); ?>m²</span>
                            </li>
                            <?php if(intval(getMeta("adLandSurface")) > 0) { ?> 
                            <li>
                                <span class="nameFeature"><?php _e("Land area", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adLandSurface"); ?>m²</span>
                            </li>
                            <?php } ?>
                            <li>
                                <span class="nameFeature"><?php _e("Number rooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adNbRooms"); ?></span>
                            </li>
                            <?php if(intval(getMeta("adNbBedrooms")) > 0) { ?>
                            <li>
                                <span class="nameFeature"><?php _e("Number bedrooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adNbBedrooms"); ?></span>
                            </li>
                            <?php } ?>
                            <?php if(intval(getMeta("adNbWC")) > 0) { ?>
                            <li>
                                <span class="nameFeature"><?php _e("Number toilets", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adNbWC"); ?></span>
                            </li>
                            <?php } ?>
                            <?php if(intval(getMeta("adNbBathrooms")) >0) { ?>
                            <li>
                                <span class="nameFeature"><?php _e("Number bathrooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adNbBathrooms"); ?></span>
                            </li>
                            <?php } ?>
                            <?php if(intval(getMeta("adNbWaterRooms")) > 0) { ?>
                            <li>
                                <span class="nameFeature"><?php _e("Number shower rooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adNbWaterRooms"); ?></span>
                            </li>
                            <?php } ?>
                            <?php if(!empty($customMainFields)) {
                                foreach($customMainFields as $fieldName) {
                                    if(!empty(getMeta("adCF".$fieldName))) { ?>
                                        <li>
                                            <span class="nameFeature"><?= $fieldName; ?></span>
                                            <span class="valueFeature"><?= getMeta("adCF".$fieldName); ?></span>
                                        </li>
                                    <?php }
                                }
                            } ?>
                        </ul>
                    </div>
                    <div class="complementaryFeatures">
                        <h4><?php _e("Complementary characteristics", "retxtdom"); ?></h4>
                        <ul>
                            <?php if(intval(getMeta("adFloor")) > 0) { ?>
                            <li>
                                <span class="nameFeature"><?php _e("Floor", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adFloor"); ?> (sur <?=getMeta("adNbFloors");?>)</span>
                            </li>
                            <?php } ?>
                            <?php if(getMeta("adFurnished") == '1' ) { ?>
                            <li>
                                <span class="nameFeature"><?php _e("Furnished", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </li>
                            <?php } ?>
                            <?php if(getMeta("adElevator") == '1' ) { ?>
                            <li>
                                <span class="nameFeature"><?php _e("Elevator", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </li>
                            <?php } ?>
                            <?php if(getMeta("adCellar") == '1' ) { ?>
                            <li>
                                <span class="nameFeature"><?php _e("Cellar", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </li>
                            <?php } ?>
                            <?php if(getMeta("adTerrace") == '1' ) { ?>
                            <li>
                                <span class="nameFeature"><?php _e("Terrace", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </li>
                            <?php } ?>
                            <?php if(is_numeric(getMeta("adYear")) && intval(getMeta("adYear"))>0) { ?>
                            <li>
                                <span class="nameFeature"><?php _e("Construction year", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adYear"); ?></span>
                            </li>
                            <?php } ?>
                            <li>
                                <span class="nameFeature"><?php _e("Type heating", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adTypeHeating"); ?></span>
                            </li>
                            <li>
                                <span class="nameFeature"><?php _e("Type kitchen", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= getMeta("adTypeKitchen"); ?></span>
                            </li>
                            <li>
                                <span id="DPEName" class="nameFeature"><?php _e("EPD in kWhPE/m²/year", "retxtdom"); ?></span>
                                <span id="DPEValue" class="valueFeature"><?= getMeta("adDPE"); ?></span>
                            </li>
                            <li>
                                <span id="GESName" class="nameFeature"><?php _e("Greenhouse gas in kg eqCO2/m²/year", "retxtdom"); ?></span>
                                <span id="GESValue" class="valueFeature"><?= getMeta("adGES"); ?></span>
                            </li>
                            <?php if(!empty($customComplementaryFields)) {
                                foreach($customComplementaryFields as $fieldName) {
                                    if(!empty(getMeta("adCF".$fieldName))) { ?>
                                        <li>
                                            <span class="nameFeature"><?= $fieldName; ?></span>
                                            <span class="valueFeature"><?= getMeta("adCF".$fieldName); ?></span>
                                        </li>
                                    <?php }
                                }
                            } ?>
                        </ul>
                    </div>
                </div>
                <div class="contentRightAd">
                    <?php if($getCoords) {  
                        if($showMap == "onlyPC") { ?>
                            <span id="addressApprox"><?php _e("The location of the property is approximate", "retxtdom"); ?>.</span>
                        <?php } ?>
                        <div id="map" class="map" data-coords="<?= implode(',', $coords); ?>"></div>
                    <?php } ?>
                    <div class="contact">
                        <div class="headerContact">
                            <?php if($getContact) { ?>
                            <div class="headerContactLeft">
                                <img src="<?= $thumbnailContact; ?>" alt="<?php _e("Contact thumbnail", "retxtdom"); ?>" id="thumbnailContact">
                            </div>
                            <div class="headerContactRight">
                                <span id="nameContact"><?= $nameContact; ?></span>
                                <?php if(isset($phone) || isset($mobilePhone)) { ?>
                                <table id="phoneContact">
                                    <tbody>
                                        <tr>
                                            <td id="phoneIcon" rowspan="2"><span class="material-symbols-outlined">call</span></td>
                                            <?php if(isset($phone)) { ?><td id="phoneContact"><a href="tel:<?= $phone; ?>"><?= $phone ?></a></td><?php } ?>
                                        </tr>
                                        <tr>
                                            <?php if(isset($mobilePhone)) { ?><td><span id="mobilePhoneContact"><a href="tel:<?= $mobilePhone; ?>"><?= $mobilePhone; ?></a></span></td><?php } ?>
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
                            <label for="names"><?php _e("First name and surname", "retxtdom"); ?></label><input type="text" name="names" class="formContactInput" required>
                            <label for="phone"><?php _e("Phone", "retxtdom"); ?></label><input type="tel" name="phone" class="formContactInput" required>
                            <label for="email"><?php _e("Email address", "retxtdom"); ?></label><input type="text" name="email" class="formContactInput" required>
                            <label for="message"><?php _e("Message", "retxtdom"); ?></label><textarea name="message" class="formContactInput" cols="22" required></textarea>
                            <input type="submit" name="submit" id="formContactSubmit" value="<?php _e("Send", "retxtdom"); ?>">
                        </form>
                    </div>
                </div>
                <?php if(!empty($morePosts)) { ?>
                <div class="more">
                    <span id="moreTitle"><?php _e("Other", "retxtdom"); ?> <?= lcfirst($typeAd); ?>s <?= _e("in", "retxtdom"); ?> <?= ucfirst($city); ?></span><br />
                    <div class="morePosts">
                        <?php 
                            $nbPosts = count($morePosts);
                            $adByPanel = 5;
                            $nbPanels = ceil($nbPosts/$adByPanel);
                            for($i=0; $i<$nbPanels; $i++) { ?>
                                <div class="morePostsPanel" <?= $i>0 ? 'style="display: none;"':'';?>>
                                    <span class="prevMorePosts" <?= $nbPanels<$adByPanel ? 'style="display: none;"':'';?>><</span>
                                    <?php for($y=0; $y<$adByPanel; $y++) {
                                        $currentNbPost = $i*5+$y;
                                        if(isset($morePosts[$currentNbPost]) && get_the_post_thumbnail_url($morePosts[$currentNbPost]) !== false) { 
                                            $morePost = $morePosts[$currentNbPost];?>
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

function getMeta($metaName) {
    global $metas;
    return isset($metas[$metaName])?implode($metas[$metaName]):'';
}