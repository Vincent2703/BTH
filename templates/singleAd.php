<?php
    while(have_posts()) {
        the_post();
       
        $idPost = get_the_id();
        
        if(wp_get_post_terms($idPost, "adAvailable")[0]->name === "Indisponible" && !get_option(PLUGIN_RE_NAME."OptionsAds")["displayAdsUnavailable"]) {
            wp_redirect(get_home_url(), "302");
            exit();
        }

        $price = get_post_meta($idPost, "adPrice", true);
        $images = get_post_meta($idPost, "adImages", true);
        $typeAd = get_the_terms($idPost, "adTypeAd")[0]->name;
        $afterPrice = '€';
        if($typeAd === "Location") {
            $afterPrice .= "/mois";
        }                  

        if(!is_null($images)) {
            $imagesIds = explode(';', $images);
        }

        $showMap = get_post_meta($idPost, "adShowMap", true);
        if($showMap !== "no" && $showMap) {
            if($showMap === "onlyPC") {
                $address = get_post_meta($idPost, "adPC", true).", ".get_post_meta($idPost, "adCity", true);
            }else if($showMap === "all"){
                $address = get_post_meta($idPost, "adAddress", true);
            }
            $coords = get_post_meta($idPost, "adDataMap", true);
        }
        if(isset($coords) && !empty($coords) && is_array($coords)) {
            $getCoords = true;
        }else{
            $getCoords = false;
        }
        $city = get_post_meta($idPost, "adCity", true);

        if(!empty($idContact = get_post_meta($idPost, "adIdAgent", true))) {
            $getContact = true;
            if(get_post_meta($idPost, "adShowAgent", true) === "OUI") {
                $emailToContact = get_post_meta($idContact, "agentEmail", true);
                $phone = get_post_meta($idContact, "agentPhone", true);
                $mobilePhone = get_post_meta($idContact, "agentMobilePhone", true);
            }else{
                $idContact = get_the_terms($idContact, "agentAgency")[0]->name;
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
            $adRef = get_post_meta($idPost, "adRefAgency", true);
            $names = sanitize_text_field($_POST["names"]);
            $phone = sanitize_text_field($_POST["phone"]);
            $email = sanitize_email($_POST["email"]);
            $messageInput = sanitize_textarea_field($_POST["message"]);
            
            $subject = "Contact au sujet de l'annonce $adRef";
            $message = "Message de la part de : $names<br />"
                    . "Téléphone : $phone - Adresse mail : $email<br />"
                    . "A propos de \"" . get_the_title() . "\"<br /><br />"
                    . "Message :<br />"
                    . $messageInput;
            $headers = array("Content-Type: text/html; charset=UTF-8");
                    
            if(wp_mail($emailToContact, $subject, $message, $headers)) {
                echo "Le mail a bien été envoyé.";
            }else{
                echo "Le mail n'a pas pu être envoyé.";
            }
        }
        
        $mapping = get_option(PLUGIN_RE_NAME."OptionsMapping");
        $mainFeatures = array();
        $complementaryFeatures = array();
        foreach($mapping as $field) {
            if($field["section"] === "mainFeatures") {
                array_push($mainFeatures, $field);
            }else if($field["section"] === "complementaryFeatures") {
                array_push($complementaryFeatures, $field);
            }
        }
        
        $morePosts = get_posts(array(
            "post_type" => "ad",
            "exclude" => $idPost,
            "meta_query" => array(
                array(
                    "key" => "adCity",
                    "value" => $city
                )
            ),
            "tax_query" => array(
                array(
                    "taxonomy" => "adTypeAd",
                    "field" => "name",
                    "terms" => $typeAd
                )
            )
        ));
        
        get_header();                 
?>


    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <span class="titleAd"><h1><?= the_title(); ?></h1></span>
            <span class="subtitleAd"><?= "$city - $price$afterPrice"; ?></span>
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
                    <?= the_content(); ?>
                </div>
                <div class="mainFeatures">
                    <h4>Caractéristiques principales</h4>
                    <ul>
                    <?php foreach($mainFeatures as $mainFeature) { ?>
                        <li>
                            <span class="nameFeature"><?= $mainFeature["name"] ;?></span>
                            <span class="valueFeature"><?= get_post_meta($idPost, "ad".ucfirst($mainFeature["name"]), true); ?></span>
                        </li>
                    <?php } ?>
                    </ul>
                </div>
                <div class="complementaryFeatures">
                    <h4>Caractéristiques complémentaires</h4>
                    <ul>
                    <?php foreach($complementaryFeatures as $complementaryFeatures) { ?>
                        <li>
                            <span class="nameFeature"><?= $complementaryFeatures["name"] ;?></span>
                            <span class="valueFeature"><?= get_post_meta($idPost, "ad".ucfirst($complementaryFeatures["name"]), true); ?></span>
                        </li>
                    <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="contentRightAd">
                <?php if($getCoords) { ?>
                <div id="map" class="map" data-coords="<?= implode(',', $coords); ?>"></div>
                <?php } ?>
                <div class="contact">
                    <div class="headerContact">
                        <?php if($getContact) { ?>
                        <div class="headerContactLeft">
                            <img src="<?= $thumbnailContact; ?>" alt="Miniature du contact" id="thumbnailContact">
                        </div>
                        <div class="headerContactRight">
                            <span id="nameContact"><?= $nameContact; ?></span>
                            <div id="phoneContact">
                                <span class="material-symbols-outlined">
                                    call
                                </span>
                                <a href="tel:<?= $phone; ?>"><?= isset($phone)?$phone:'' ?></a>
                            </div>
                            
                            <span id="mobilePhoneContact"><a href="tel:<?= $mobilePhone; ?>"><?= isset($mobilePhone)?$mobilePhone:'' ?></a></span>
                        </div>
                        <?php }else{ ?>
                        <span id="noContactTitle">Formulaire de contact</span>
                        <?php } ?>
                    </div>
                    <form action="" method="post" class="formContact">
                        <label for="names">Nom et prénom</label><input type="text" name="names" class="formContactInput" required>
                        <label for="phone">Téléphone</label><input type="tel" name="phone" class="formContactInput" required>
                        <label for="email">Adresse mail</label><input type="text" name="email" class="formContactInput" required>
                        <label for="message">Message</label><textarea name="message" class="formContactInput" required></textarea>
                        <input type="submit" name="submit" id="formContactSubmit" value="Envoyer">
                    </div>
                </div>
            </div>
            <div class="more">
                <span id="moreTitle">Autres <?= lcfirst($typeAd); ?>s à <?= $city; ?></span>
                <div class="morePosts">
                <?php foreach($morePosts as $post) {
                    echo '<a src="'.get_post_permalink($post).'">'.get_the_post_thumbnail($post, "thumbnail").get_the_title($post).'</a>';
                } ?>
                </div>
            </div>
    <?php 
    }           
    ?>
        </main>
    </div>

<?php
get_footer();