<?php
    if(!defined("ABSPATH")) {
        exit; //Exit if accessed directly
    }
    if(have_posts()) {
        //Check if we have the premium complement for the plugin.
        $activatedPluginsList = get_option("active_plugins");
        
        require_once(PLUGIN_RE_PATH."models/AdModel.php");
        $currency = REALM_AdModel::getCurrency();
        $areaUnit = REALM_AdModel::getAreaUnit();
        $feesURL = REALM_AdModel::getFeesURL();
        while(have_posts()) {
            if(is_active_sidebar("before_content-side-bar")) {
               dynamic_sidebar("before_content-side-bar");
            }
            the_post();

            $idPost = get_the_id();
            $ad = REALM_AdModel::getAd($idPost);

            if($ad["taxonomies"]["availability"]["slug"] === "unavailable") {
                wp_redirect(get_home_url(), "302");
                exit();
            }
            
            $userIsCustomer = current_user_can("customer");
            
            if(PLUGIN_RE_REP && $userIsCustomer) {               
                require_once(PLUGIN_REP_PATH."models/UserModel.php");
                $idUser = get_current_user_id();
                $user = REALMP_UserModel::getUser($idUser);
                $alreadyHF = get_posts(array(
                    "author"        => $idUser,
                    "post_type"     => "submission",
                    "post_status"   => array("accepted", "decisionwaiting", "revisionwaiting"),
                    "post_parent"   => $idPost,
                    "numberposts"   => 1
                ));
                $checkExistingSubmission = !empty($alreadyHF);            
              
                $submissionsLimitReached = REALMP_UserModel::checkSubmissionsLimitReached($idUser);
                
                if($ad["allowSubmission"]) {
                    $userHasHousingFile = REALMP_UserModel::checkCustomerHasHousingFile($idUser, $ad["needGuarantors"]);
                }
            }   
        
            
            get_header();  
            
            if(
                PLUGIN_RE_REP && //Plugin premium
                isset($_POST["apply"]) &&  //Form submitted
                isset($_POST["nonceSecurity"]) && //Nonce exists
                is_numeric(wp_verify_nonce($_POST["nonceSecurity"], "formApply")) && //Nonce checked
                $userIsCustomer &&  //The current user is a customer
                !$checkExistingSubmission && //There is not already a housing file submission for the customer
                !$submissionsLimitReached //It didn't reached the limit
            ) {
                require_once(PLUGIN_REP_PATH."models/SubmissionModel.php");
                $HFID = REALMP_SubmissionModel::createPost($idPost, $idUser); 
            }else if(isset($_POST["contact"]) && isset($_POST["nonceSecurity"]) && is_numeric(wp_verify_nonce($_POST["nonceSecurity"], "formContact"))) {
                if(isset($_POST["name"]) && isset($_POST["phone"]) && isset($_POST["email"]) && isset($_POST["message"]) && !empty(trim($_POST["names"])) && !empty(trim($_POST["phone"])) && !empty(trim($_POST["email"])) && !empty(trim($_POST["message"]))) {
                    $adRef = $ad["refAd"];
                    $contactNames = sanitize_text_field($_POST["names"]);
                    $contactPhone = sanitize_text_field($_POST["phone"]);
                    $contactEmail = sanitize_email($_POST["email"]);
                    $messageInput = sanitize_textarea_field($_POST["message"]);

                    $subject = __("Message about the ad", "retxtdom").' '.$ad["refAd"];
                    $message = __("Message from", "retxtdom")." : $contactNames<br />"
                            . __("Phone", "retxtdom")." : $contactPhone - ".__("Email address", "retxtdom")." : $contactEmail<br />"
                            . __("About", "retxtdom")." \"" . get_the_title() . "\"<br /><br />"
                            . __("Message", "retxtdom")." :<br />"
                            . $messageInput;
                    $headers = array("Content-Type: text/html; charset=UTF-8");

                    if(wp_mail($ad["email"], $subject, $message, $headers)) {
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
                <div class="subtitleAd"><?=$ad["taxonomies"]["typeProperty"]["name"].' - '.$ad["taxonomies"]["typeAd"]["name"] . " (".$ad["price"].$ad["afterPrice"].")";?></div>
                <?php if(!empty($ad["imagesIds"][0])) { ?>
                <div class="sliders">
                    <div id="miniSlider">
                        <span class="controlNext">></span>
                        <span class="controlPrev"><</span>
                        <span class="pagingImg"></span>
                        <ul>
                        <?php foreach ($ad["imagesIds"] as $id) {
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
                        <span class="contentDescription"><?php the_content(); ?></span>
                    </div>
                    <div class="mainFeatures">
                        <h4><?php _e("Main features", "retxtdom"); ?></h4>
                        <div class="listFeatures">
                            <div>
                                <span class="nameFeature"><?php _e("Reference", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= $ad["refAd"]; ?></span>
                            </div>
                            <div>
                                <span class="nameFeature"><?php _e("Price", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= $ad["price"].$currency; ?></span>
                            </div>
                            <div>
                                <span class="nameFeature"><span style="white-space: nowrap"><?php _e("Fees", "retxtdom"); ?></span><?php !empty($feesURL)?printf('&nbsp;<a class="feesSchedule" target="_blank" href="%1$s">(%2$s)</a>', $feesURL, __("Fees schedule", "retxtdom")):''; ?></span>
                                <span class="valueFeature"><?= $ad["fees"].$currency; ?></span>
                            </div>
                            <div>
                                <span class="nameFeature"><?php _e("Address", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= $ad["address"]; ?>&nbsp;<a id="linkGMaps" target="_blank" href="https://www.google.fr/maps/place/<?=urlencode($ad["address"]);?>"><span class="dashicons dashicons-location"></span></a></span>
                            </div>
                            <div>
                                <span class="nameFeature"><?php _e("Living space", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= $ad["surface"]; ?>m²</span>
                            </div>
                            <?php if(intval($ad["landSurface"]) > 0) { ?> 
                            <div>
                                <span class="nameFeature"><?php _e("Land area", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= $ad["landSurface"]; ?>m²</span>
                            </div>
                            <?php } ?>
                            <div>
                                <span class="nameFeature"><?php _e("Number of rooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= $ad["nbRooms"]; ?></span>
                            </div>
                            <?php if(intval($ad["nbBedrooms"]) > 0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Number of bedrooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= $ad["nbBedrooms"]; ?></span>
                            </div>
                            <?php } ?>
                            <?php if(intval($ad["nbWC"]) > 0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Number of toilets", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= $ad["nbWC"]; ?></span>
                            </div>
                            <?php } ?>
                            <?php if(intval($ad["nbBathrooms"]) > 0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Number of bathrooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= $ad["nbBathrooms"]; ?></span>
                            </div>
                            <?php } ?>
                            <?php if(intval($ad["nbWaterRooms"]) > 0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Number of shower rooms", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= $ad["nbWaterRooms"]; ?></span>
                            </div>
                            <?php } ?>
                            <?php if(!empty($ad["customMainFields"])) {
                                foreach($ad["customMainFields"] as $fieldName) {
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
                            <?php if(intval($ad["floor"]) > 0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Floor", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php printf("%d %s",
                                    $ad["floor"],
                                    $ad["nbFloors"] >= $ad["floor"] ? ' ('.__("out of", "retxtdom").' '.$ad["nbFloors"].')' : ''); ?>
                                </span>
                            </div>
                            <?php } ?>
                            <?php if($ad["furnished"] == '1' ) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Furnished", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <?php if($ad["elevator"] == '1' ) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Elevator", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <?php if($ad["basement"] == '1' ) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Basement", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <?php if($ad["outdoorSpace"] == '1' ) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Outdoor space", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e("Yes", "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <?php if($ad["garage"] == '1' || $ad["parking"] == '1') { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Garage/Parking", "retxtdom"); ?></span>
                                <span class="valueFeature">
                                    <?php if($ad["garage"] == '1') {
                                        _e("Garage", "retxtdom");
                                    }else if($ad["parking"] == '1') {
                                        _e("Parking", "retxtdom");
                                    } ?>
                                </span>
                            </div>
                            <?php } ?>
                            <?php if(is_numeric($ad["year"]) && intval($ad["year"])>0) { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Construction year", "retxtdom"); ?></span>
                                <span class="valueFeature"><?= $ad["year"]; ?></span>
                            </div>
                            <?php } ?>
                            <?php if($ad["typeHeating"] !== "Unknown") { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Type of heating", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e($ad["typeHeatingTranslated"], "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <?php if($ad["typeKitchen"] !== "Unknown") { ?>
                            <div>
                                <span class="nameFeature"><?php _e("Type of kitchen", "retxtdom"); ?></span>
                                <span class="valueFeature"><?php _e($ad["typeKitchenTranslated"], "retxtdom"); ?></span>
                            </div>
                            <?php } ?>
                            <div>
                                <span id="DPEName" class="nameFeature"><?php _e("EPD", "retxtdom"); ?>&nbsp;<abbr data-title="<?php _e("In kWhPE/m²/year", "retxtdom"); ?>"><sup>?</sup></abbr></span>
                                <span id="DPEValue" class="valueFeature"><span><?= $ad["DPE"];?>&nbsp;</span> 
                                    <div>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">A</span>', $ad["DPE"]>=0&&$ad["DPE"]<=50?'class="dpe"':'', "black", "rgb(49, 152, 52)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">B</span>', $ad["DPE"]>=51&&$ad["DPE"]<=90?'class="dpe"':'',"black", "rgb(51, 204, 49)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">C</span>', $ad["DPE"]>=91&&$ad["DPE"]<=150?'class="dpe"':'',"black", "rgb(203, 252, 52)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">D</span>', $ad["DPE"]>=151&&$ad["DPE"]<=230?'class="dpe"':'',"black", "rgb(251, 254, 6)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">E</span>', $ad["DPE"]>=231&&$ad["DPE"]<=330?'class="dpe"':'',"black", "rgb(251, 204, 5)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">F</span>', $ad["DPE"]>=331&&$ad["DPE"]<=450?'class="dpe"':'',"black", "rgb(252, 153, 53)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">G</span>', $ad["DPE"]>=451&&$ad["DPE"]<=500?'class="dpe"':'',"white", "rgb(252, 2, 5)"); ?>
                                    </div>
                                </span>
                            </div>
                            <div>
                                <span id="GESName" class="nameFeature"><?php _e("Greenhouse gas", "retxtdom"); ?>&nbsp;<abbr data-title="<?php _e("In kg eqCO2/m²/year", "retxtdom"); ?>"><sup>?</sup></abbr></span>
                                <span id="GESValue" class="valueFeature"><span><?= $ad["GES"]; ?>&nbsp;</span>
                                    <div>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">A</span>', $ad["GES"]>=0&&$ad["GES"]<=5?'class="ges"':'', "black", "rgb(242, 239, 244)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">B</span>', $ad["GES"]>=6&&$ad["GES"]<=10?'class="ges"':'',"black", "rgb(233, 193, 247)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">C</span>', $ad["GES"]>=11&&$ad["GES"]<=20?'class="ges"':'',"black", "rgb(214, 170, 244)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">D</span>', $ad["GES"]>=21&&$ad["GES"]<=35?'class="ges"':'',"black", "rgb(204, 147, 244)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">E</span>', $ad["GES"]>=36&&$ad["GES"]<=55?'class="ges"':'',"white", "rgb(187, 114, 243)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">F</span>', $ad["GES"]>=56&&$ad["GES"]<=80?'class="ges"':'',"white", "rgb(169, 76, 238)"); ?>
                                        <?php printf('<span %s style="color: %s; background-color: %s;">G</span>', $ad["GES"]>=81&&$ad["GES"]<=100?'class="ges"':'',"white", "rgb(139, 26, 225)"); ?>
                                    </div>
                                </span>
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
                    <?php
                    if(PLUGIN_RE_REP && $userIsCustomer && $ad["allowSubmission"]) { 
                        if($checkExistingSubmission || isset($HFID)) { ?>
                            <a href="<?=admin_url();?>"><button><?php _e("View my housing file submission", "retxtdom");?></button></a>
                        <?php }else if($submissionsLimitReached) { ?>
                            <span><?php _e("You have exceeded the maximum number of housing file submissions that you can submit", "retxtdom");?>.</span>
                            <a href="<?=admin_url("edit.php?post_type=submission");?>"><button><?php _e("View my housing file submission", "retxtdom");?></button></a>
                        <?php }else if(!$userHasHousingFile) { ?>
                            <span><?php _e("You must fill your housing file before be able to apply for the property", "retxtdom");?>.</span>
                            <?php if($ad["needGuarantors"] && intval($user["nbGuarantors"]) === 0) { ?>
                                <span><?php _e("Please, do not forget to add at least a guarantor", "retxtdom");?>.</span>
                            <?php } 
                            ?>
                            <a href="<?= admin_url("profile.php?ad=$idPost"); ?>"><button><?php _e("Fill my housing file", "retxtdom"); ?></button></a>
                        <?php }else { ?>
                        <form method="post" action="" id="applyForm">
                            <?php wp_nonce_field("formApply", "nonceSecurity"); ?>
                            <input type="submit" name="apply" id="applyBtn" value="<?php _e("Apply for the property", "retxtdom");?>">
                        </form>
                        <?php }                        
                        }
                    ?>
                </div>
                <div class="contentRightAd">
                    <div class="mapContainer">
                    <?php if($ad["getCoords"]) {  
                        if($ad["showMap"] === "onlyPC") { ?>
                            <span id="addressApprox"><?php _e("The location of the property is approximate", "retxtdom"); ?>.</span>
                        <?php } ?>
                        <div id="map" class="map" data-coords="<?= implode(',', $ad["coords"]); ?>"></div>
                    <?php } ?>
                    </div>
                    <div class="contact">
                        <div class="headerContact">
                            <?php if($ad["getContact"]) { ?>
                            <div class="headerContactLeft">
                            <?php if(!empty($ad["thumbnailContact"])) {
                                if(isset($ad["linkAgency"]) && $ad["linkAgency"]!==false) { ?>
                                <a href="<?=$ad["linkAgency"];?>">
                                    <img src="<?= $ad["thumbnailContact"]; ?>" alt="<?php _e("Contact's thumbnail", "retxtdom"); ?>" id="thumbnailContact">
                                </a>
                                <?php }else{ ?>
                                    <img src="<?= $ad["thumbnailContact"]; ?>" alt="<?php _e("Contact's thumbnail", "retxtdom"); ?>" id="thumbnailContact">
                                <?php }
                            }?>
                            </div>
                            <div class="headerContactRight">
                                <span id="nameContact"><?= $ad["nameContact"]; ?></span>
                                <?php if(isset($ad["phone"]) && $ad["phone"]!==false || isset($ad["mobilePhone"]) && $ad["mobilePhone"]!==false) { ?>
                                <table id="phoneContact">
                                    <tbody>
                                        <tr>
                                            <td id="phoneIcon" rowspan="2"><span class="material-symbols-outlined">call</span></td>
                                            <?php if(isset($ad["phone"])) { ?><td class="phoneContact"><a href="tel:<?= $ad["phone"]; ?>"><?= implode(' ', str_split($ad["phone"], 2)); ?></a></td><?php } ?>
                                        </tr>
                                        <tr>
                                            <?php if(isset($ad["mobilePhone"])) { ?><td><span class="mobilePhoneContact"><a href="tel:<?= $ad["mobilePhone"]; ?>"><?= implode(' ', str_split($ad["mobilePhone"], 2)); ?></a></span></td><?php } ?>
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
                            <?php wp_nonce_field("formContact", "nonceSecurity"); 
                            $prefillForm = PLUGIN_RE_REP&&$userIsCustomer;
                            ?>
                            <label for="names"><?php _e("First name and last name", "retxtdom"); ?></label><input type="text" name="names" class="formContactInput" value="<?= $prefillForm?$user["firstName"].' '.$user["lastName"]:''?>" required>
                            <label for="phone"><?php _e("Phone", "retxtdom"); ?></label><input type="tel" name="phone" class="formContactInput" required>
                            <label for="email"><?php _e("Email address", "retxtdom"); ?></label><input type="text" name="email" class="formContactInput" value="<?= $prefillForm?$ad["email"]:'';?>"required>
                            <label for="message"><?php _e("Message", "retxtdom"); ?></label><textarea name="message" class="formContactInput" rows="3" required></textarea>
                            <?php if(isset($emailStatus)) { ?>
                                <span id="emailStatus"><?=$emailStatus;?>.</span><br />
                            <?php } ?>
                            <input type="submit" name="contact" id="formContactSubmit" value="<?php _e("Send", "retxtdom"); ?>">
                        </form>
                    </div>
                </div>
                <?php if(!empty($ad["morePosts"])) { ?>
                <div class="more">
                    <span id="moreTitle"><?php _e("Similar ads", "retxtdom"); ?></span>
                    <div class="similarAdsContainer">
                        <div class="similarAdsWrapper">
                        <?php foreach($ad["morePosts"] as $similarAd) {
                            $ad = REALM_AdModel::getAd($similarAd->ID);
                            printf(
                                '<div class="similarAd">
                                    <div class="similarAdThumbnail">
                                        <a href="%1$s">%2$s</a>
                                    </div>
                                    <div class="similarAdDetails">
                                        <span class="similarAdTitle"><a href="%1$s">%3$s</a></span>
                                        <span class="similarAdPrice">%4$s</span>
                                        <span class="similarAdSurfaceRooms">
                                            <span>%5$s</span>
                                            <span>%6$s</span>
                                        </span>
                                    </div>
                                </div>',
                                $ad["permalink"],
                                $ad["thumbnails"]["small"],
                                $ad["title"],
                                $ad["price"].REALM_AdModel::getCurrency(),
                                $ad["surface"].REALM_AdModel::getAreaUnit(),
                                ($ad["nbRooms"] > 0)?$ad["nbRooms"].' '.__("rooms", "retxtdom"):''
                            );
                        } ?>
                        </div>
                        <span class="prevMorePosts dashicons dashicons-arrow-left" style="display: none;"></span>
                        <span class="nextMorePosts dashicons dashicons-arrow-right"></span>
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