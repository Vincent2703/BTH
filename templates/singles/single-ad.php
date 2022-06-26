<?php
    if(have_posts()) {
        get_header();                 
        while(have_posts()) { //?
   if ( is_active_sidebar( 'before_content-side-bar' ) ) {
      dynamic_sidebar( 'before_content-side-bar' );
   }
            the_post();

            $idPost = get_the_id();

            if(wp_get_post_terms($idPost, "adAvailable")[0]->name === "Indisponible" && !get_option(PLUGIN_RE_NAME."OptionsAds")["displayAdsUnavailable"]) {
                wp_redirect(get_home_url(), "302");
                exit();
            }

            $metas = get_post_custom();
           

            $price = getMeta("adPrice");
            $images = getMeta("adImages");
            $typeAd = get_the_terms($idPost, "adTypeAd")[0]->name;
            $afterPrice = '€';
            if($typeAd === "Location") {
                $afterPrice .= "/mois";
            }

            if(!is_null($images)) {
                $imagesIds = explode(';', $images);
            }

            $showMap = getMeta("adShowMap");
            if($showMap !== "no" && $showMap) {
                if($showMap === "onlyPC") {
                    $address = getMeta("adPC").", ". getMeta("adCity");
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
                if(getMeta("adShowAgent") === "OUI") {
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
                $adRef = getMeta("adRefAgency");
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
                $feature = array();
                $feature["name"] = isset($field["FRName"]) ? $field["FRName"] : $field["name"];

                if($field["section"] === "mainFeatures") {
                    array_push($mainFeatures, $field);
                }else if($field["section"] === "complementaryFeatures") {
                    array_push($complementaryFeatures, $field);
                }
            }

            $morePosts = get_posts(array(
                "post_type" => "ad",
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
                        "field" => "name",
                        "terms" => "Disponible"
                    )
                )
            ));

    ?>


        <div id="primary" class="content-area">
            <main id="main" class="site-main">
                <span class="titleAd"><h1><?= the_title(); ?></h1></span>
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
                        <?= the_content(); ?>
                    </div>
                    <div class="mainFeatures">
                        <h4>Caractéristiques principales</h4>
                        <ul>
                        <?php foreach($mainFeatures as $mainFeature) { 
                            $meta = getMeta("ad".ucfirst($mainFeature["name"]));
                            if(isset($mainFeature["FRValuesReplace"])) {
                                $value = $mainFeature["FRValuesReplace"][0][$meta];
                            }else{
                                $value = $meta;
                            }
                            if(!empty($value)) {?>
                            <li>
                                <span class="nameFeature"><?= $mainFeature["FRName"] ;?></span>
                                <span class="valueFeature"><?= $value ?></span>
                            </li>
                            <?php }} ?>
                        </ul>
                    </div>
                    <div class="complementaryFeatures">
                        <h4>Caractéristiques complémentaires</h4>
                        <ul>
                        <?php foreach($complementaryFeatures as $complementaryFeature) { 
                            $meta = getMeta("ad".ucfirst($complementaryFeature["name"]));
                            if(isset($complementaryFeature["FRValuesReplace"])) {
                                $value = $complementaryFeature["FRValuesReplace"][0][$meta];
                            }else{
                                $value = $meta;                         
                            }
                            if(!empty($value)) {?>
                            <li>
                                <span id="<?= $complementaryFeature["name"]; ?>Name" class="nameFeature"><?= isset($complementaryFeature["FRName"])?$complementaryFeature["FRName"]:'' ;?></span>
                                <span id="<?= $complementaryFeature["name"]; ?>Value" class="valueFeature"><?= $value; ?></span>
                            </li>
                            <?php }} ?>
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
                        </form>
                    </div>
                </div>
                <?php if(!empty($morePosts)) { ?>
                <div class="more">
                    <span id="moreTitle">Autres <?= lcfirst($typeAd); ?>s à <?= ucfirst($city); ?></span><br />
                    <div class="morePosts">
                        <?php 
                            $nbPosts = count($morePosts);
                            $adByPanel = 5;
                            $nbPanels = ceil($nbPosts/$adByPanel);
                            for($i=0; $i<$nbPanels; $i++) { ?>
                                <div class="morePostsPanel" <?= $i>0 ? 'style="display: none;"':'';?>>
                                    <span class="prevMorePosts" ><</span>
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
                                    <span class="nextMorePosts" >></span>
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