<?php
class EditAd {
    public function addMetaBoxes() {
        add_meta_box( 
            "adBasicsMetaBox", //ID HTML
            __("Basic information", "retxtdom"), //Display
            array($this, "displayAdBasicsMetaBox"), //Callback
            "re-ad", //Custom type
            "normal", //Location on the page
            "high" //Priority
        );
             
        add_meta_box( 
            "adComplementariesMetaBox", //ID HTML
            __("Complementary information", "retxtdom"), //Display
            array($this, "displayAdComplementariesMetaBox"), //Callback
            "re-ad", //Custom type
            "advanced", //Location on the page
            "high" //Priority
        );
    }
    
    function savePost($adId, $ad) {
        if($ad->post_type == "re-ad") {
            
            $ad->post_title = substr(sanitize_text_field($ad->postTitle), 0, 64);
            
            if(isset($_POST["adTypeProperty"]) && !ctype_space($_POST["adTypeProperty"])) {
                $this->saveTaxonomy($adId, "adTypeProperty");
            }
            if(isset($_POST["adTypeAd"]) && !ctype_space($_POST["adTypeAd"])) {
                $this->saveTaxonomy($adId, "adTypeAd");
            }
            if(isset($_POST["adAvailable"]) && $_POST["adAvailable"] === "available") {
                $this->saveTaxonomyAdAvailable($adId, "available");
            }else{
                $this->saveTaxonomyAdAvailable($adId, "unavailable");
            }            
            
            if(isset($_POST["refAgency"]) && !ctype_space($_POST["refAgency"])) {
                update_post_meta($adId, "adRefAgency", sanitize_text_field($_POST["refAgency"]));
            }
            if(isset($_POST["price"]) && !ctype_space($_POST["price"])) {
                update_post_meta($adId, "adPrice", intval($_POST["price"]));
            }
            if(isset($_POST["fees"]) && !ctype_space($_POST["fees"])) {
                update_post_meta($adId, "adFees", intval($_POST["fees"]));
            }        
            if(isset($_POST["surface"]) && !ctype_space($_POST["surface"])) {
                update_post_meta($adId, "adSurface", intval($_POST["surface"]));
            }
            if(isset($_POST["landSurface"]) && !ctype_space($_POST["landSurface"])) {
                update_post_meta($adId, "adTotalSurface", intval($_POST["landSurface"]));
            }
            if(isset($_POST["nbRooms"]) && !ctype_space($_POST["nbRooms"])) {
                update_post_meta($adId, "adNbRooms", intval($_POST["nbRooms"]));
            }
            if(isset($_POST["nbBedrooms"]) && !ctype_space($_POST["nbBedrooms"])) {
                update_post_meta($adId, "adNbBedrooms", intval($_POST["nbBedrooms"]));
            }
            $nbBathWaterRooms = 0;
            if(isset($_POST["nbBathrooms"]) && !ctype_space($_POST["nbBathrooms"])) {
                update_post_meta($adId, "adNbBathrooms", intval($_POST["nbBathrooms"]));
                $nbBathWaterRooms += intval($_POST["nbBathrooms"]);
            }
            if(isset($_POST["nbWaterRooms"]) && !ctype_space($_POST["nbWaterRooms"])) {
                update_post_meta($adId, "adNbWaterRooms", intval($_POST["nbWaterRooms"]));
                $nbBathWaterRooms += intval($_POST["nbWaterRooms"]);
            }
            update_post_meta($adId, "adNbBathWaterRooms", $nbBathWaterRooms);
            if(isset($_POST["nbWC"]) && !ctype_space($_POST["nbWC"])) {
                update_post_meta($adId, "adNbWC", intval($_POST["nbWC"]));
            }            
            
            if(isset($_POST["showMap"]) && !ctype_space($_POST["showMap"])) {
                update_post_meta($adId, "adShowMap", sanitize_text_field($_POST["showMap"]));
                if(isset($_POST["address"]) && !ctype_space($_POST["address"])) {
                    update_post_meta($adId, "adAddress", sanitize_text_field($_POST["address"]));
                        $query = urlencode(addslashes(htmlentities(sanitize_text_field($_POST["address"]))));
                        if($_POST["showMap"] === "onlyPC") { 
                            $zoom = 14;
                            $radiusCircle = 0;
                            $url = plugin_dir_url(__DIR__)."includes/php/getAddressData.php?query=$query&city";
                            $addressData = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true)[0];
                        }else{
                            $zoom = 16;
                            $radiusCircle = 10;
                            $url = plugin_dir_url(__DIR__)."includes/php/getAddressData.php?query=$query";
                            $addressData = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true)[0];
                        }
                    
                        $coordinates = $addressData["coordinates"];
                        update_post_meta($adId, "adDataMap", array("lat" => $coordinates[1], "long" => $coordinates[0], "zoom" => $zoom, "circ" => $radiusCircle));
                        update_post_meta($adId, "adLatitude", $coordinates[1]);
                        update_post_meta($adId, "adLongitude", $coordinates[0]);

                        $PC = $addressData["postcode"];
                        update_post_meta($adId, "adPC", $PC);

                        $city = $addressData["city"];
                        update_post_meta($adId, "adCity", $city);
                }
            }
            if(isset($_POST["images"]) && !ctype_space($_POST["images"])) {
                update_post_meta($adId, "adImages", sanitize_text_field($_POST["images"]));
            }
            if(isset($_POST["agent"]) && !ctype_space($_POST["agent"])) {
                update_post_meta($adId, "adIdAgent", intval($_POST["agent"]));
            }
            if(isset($_POST["showAgent"])) {
                update_post_meta($adId, "adShowAgent", '1');
            }else{
                update_post_meta($adId, "adShowAgent", '0');
            }
  
                       
            if(isset($_POST["labels"]) && !ctype_space($_POST["labels"])) {
                update_post_meta($adId, "adLabels", sanitize_text_field($_POST["labels"]));
            }
            
            
            if(isset($_POST["floor"]) && !ctype_space($_POST["floor"])) {
                update_post_meta($adId, "adFloor", intval($_POST["floor"]));
            }
            if(isset($_POST["nbFloors"]) && !ctype_space($_POST["nbFloors"])) {
                update_post_meta($adId, "adNbFloors", intval($_POST["nbFloors"]));
            }
            if(isset($_POST["furnished"]) && !ctype_space($_POST["furnished"])) {
                update_post_meta($adId, "adFurnished", '1');
            }else{
                update_post_meta($adId, "adFurnished", '0');
            }
            if(isset($_POST["year"]) && !ctype_space($_POST["year"])) {
                update_post_meta($adId, "adYear", intval($_POST["year"]));
            }
            if(isset($_POST["typeHeating"]) && !ctype_space($_POST["typeHeating"])) {
                update_post_meta($adId, "adTypeHeating", $_POST["typeHeating"]);
            }
            if(isset($_POST["typeKitchen"]) && !ctype_space($_POST["typeKitchen"])) {
                update_post_meta($adId, "adTypeKitchen", $_POST["typeKitchen"]);
            }
            if(isset($_POST["nbBalconies"]) && !ctype_space($_POST["nbBalconies"])) {
                update_post_meta($adId, "adNbBalconies", intval($_POST["nbBalconies"]));
            }
            if(isset($_POST["elevator"]) && !ctype_space($_POST["elevator"])) {
                update_post_meta($adId, "adElevator", '1');
            }else{
                update_post_meta($adId, "adElevator", '0');
            }
            if(isset($_POST["cellar"]) && !ctype_space($_POST["cellar"])) {
                update_post_meta($adId, "adCellar", '1');
            }else{
                update_post_meta($adId, "adCellar", '0');                
            }
            if(isset($_POST["terrace"]) && !ctype_space($_POST["terrace"])) {
                update_post_meta($adId, "adTerrace", '1');
            }else{
                update_post_meta($adId, "adTerrace", '0');                
            }
            if(isset($_POST["DPE"]) && !ctype_space($_POST["DPE"])) {
                update_post_meta($adId, "adDPE", intval($_POST["DPE"]));
            }
            if(isset($_POST["GES"]) && !ctype_space($_POST["GES"])) {
                update_post_meta($adId, "adGES", intval($_POST["GES"]));
            }
            
            $optionsDisplayads = get_option(PLUGIN_RE_NAME."OptionsDisplayads")["customFields"];
            if(!empty($optionsDisplayads) || $optionsDisplayads !== "[]") {
                foreach(json_decode($optionsDisplayads, true) as $field) {
                    if(isset($_POST["CF".$field["name"]]) && !ctype_space($_POST["CF".$field["name"]])) {
                        update_post_meta($adId, "adCF".$field["name"], sanitize_text_field($_POST["CF".$field["name"]]));
                    }
                }
            }
            
        }
    }
    
        
    function saveTaxonomy($postId, $taxonomyName) {
        /*if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
            return;
        }*/

        $taxonomy = sanitize_text_field($_POST[$taxonomyName]);

        if(!empty($taxonomy)) {

            $term = get_term_by("name", $taxonomy, $taxonomyName);
            if(!empty($term) && !is_wp_error($term)) {
                wp_set_object_terms($postId, $term->term_id, $taxonomyName, false);
            }
        }
    }
    
    private static function saveTaxonomyAdAvailable($postId, $state) {
        $taxonomyName = "adAvailable";
        if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
            return;
        }
        
        if($term = get_term_by("slug", $state, $taxonomyName)) {
            wp_set_object_terms($postId, $term->term_id, $taxonomyName, false);
        }
        
    }
    
    public function displayAdBasicsMetaBox($ad) {
        $refAgency = sanitize_text_field(get_post_meta($ad->ID, "adRefAgency", true));
        $price = intval(get_post_meta($ad->ID, "adPrice", true));
        $fees = intval(get_post_meta($ad->ID, "adFees", true));
        $surface = sanitize_text_field(get_post_meta($ad->ID, "adSurface", true));
        $landSurface = sanitize_text_field(get_post_meta($ad->ID, "adTotalSurface", true));
        $nbRooms = intval(get_post_meta($ad->ID, "adNbRooms", true));
        $nbBedrooms = intval(get_post_meta($ad->ID, "adNbBedrooms", true));
        $nbBathrooms = intval(get_post_meta($ad->ID, "adNbBathrooms", true));
        $nbWaterRooms = intval(get_post_meta($ad->ID, "adNbWaterRooms", true));
        $nbWC = intval(get_post_meta($ad->ID, "adNbWC", true));
        $address = sanitize_text_field(get_post_meta($ad->ID, "adAddress", true));
        $showMap = sanitize_text_field(get_post_meta($ad->ID, "adShowMap", true));
        $images = sanitize_text_field(get_post_meta($ad->ID, "adImages", true));
        $allAgents = get_posts(array("post_type" => "agent"));
        $agentSaved = sanitize_text_field(get_post_meta($ad->ID, "adAgent", true));
        $showAgent = sanitize_text_field(get_post_meta($ad->ID, "adShowAgent", true));
        
        $optionsDisplayads = get_option(PLUGIN_RE_NAME."OptionsDisplayads")["customFields"];
        $customFieldsMF = array();
        if(!empty($optionsDisplayads) || $optionsDisplayads !== "[]") {
            foreach(json_decode($optionsDisplayads, true) as $field) {
                if($field["section"] === "mainFeatures") {
                    $customFieldsMF[$field["name"]] = sanitize_text_field(get_post_meta($ad->ID, "adCF".$field["name"], true));
                }
            }
        }
        
        ?>
        <div id="refAgency">
            <div class="text">
                <label><?php _e("Ad reference", "retxtdom");?><span id="generateRef" onclick="document.getElementById('refAgencyInput').value = <?= $ad->ID;?>;"><?php _e("Generate a reference", "retxtdom");?></span></label>
                <input type="text" name="refAgency" id="refAgencyInput" placeholder="Ex : A-123" value="<?= $refAgency; ?>">
            </div>
        </div>
        <div id="prices">
            <div class="text">
                <label><?php _e("Property price", "retxtdom");?> <abbr title="<?php _e("Charges included", "retxtdom"); ?>"><sup>?</sup></abbr></label>
                <input type="number" name="price" id="priceInput" placeholder="Ex : 180000" value="<?= $price; ?>" required>
            </div>
            <div class="text">
                <label><?php _e("Fees amount", "retxtdom");?></label>
                <input type="number" name="fees" id="feesInput" placeholder="Ex : 85" value="<?= $fees; ?>" required>
            </div>
        </div>
        <div id="surfaces">
            <div class="text">
                <label><?php _e("Living space", "retxtdom");?> (m²)</label>
                <input type="number" name="surface" id="surfaceInput" placeholder="Ex : 90" value="<?= $surface; ?>"  required>
            </div>
            <div class="text">
                <label><?php _e("Land area", "retxtdom");?> (m²)</label>
                <input type="number" name="landSurface" id="landSurfaceInput" placeholder="Ex : 90" value="<?= $landSurface; ?>">
            </div>
        </div>
        <div id="address">
            <div class="text">
                <label><?php _e("Property address", "retxtdom");?></label>
                <input type="text" name="address" id="addressInput" autocomplete="off" placeholder='Ex : <?php _e("123 Chester Square, London", "retxtdom");?>' value="<?= $address; ?>" required>
            </div>             

            <div class="radio">
                <input type="radio" name="showMap" id="map1" value="no" <?php checked($showMap, "no");?> required><label for="map1"><?php _e("Do not show address", "retxtdom");?></label>
                <input type="radio" name="showMap" id="map2" value="onlyPC" <?php checked($showMap, "onlyPC");?> required><label for="map2"><?php _e("Show postal code and city", "retxtdom");?></label>
                <input type="radio" name="showMap" id="map3" value="all" <?php checked($showMap, "all");?> required><label for="map3"><?php _e("Show full address", "retxtdom");?></label>
            </div>
        </div>
        <div id="nbRooms">
            <div class="text">
                <label><?php _e("Number rooms", "retxtdom");?></label>       
                <input type="number" name="nbRooms" id="nbRoomsInput" placeholder="<?php _e("Number rooms", "retxtdom");?>" value="<?= $nbRooms; ?>" required>
            </div>
        </div>
        <div id="otherRooms">
            <div class="text">
                <label><?php _e("Number bedrooms", "retxtdom");?></label>
                <input type="number" name="nbBedrooms" id="nbBedroomsInput" placeholder="<?php _e("Number bedrooms", "retxtdom");?>" value="<?= $nbBedrooms; ?>" required>

                <label><?php _e("Number bathrooms", "retxtdom");?></label>
                <input type="number" name="nbBathrooms" id="nbBathroomsInput" placeholder="<?php _e("Number bathrooms", "retxtdom");?>" value="<?= $nbBathrooms; ?>" required>
            </div>
            <div class="text">
                <label><?php _e("Number shower rooms", "retxtdom");?></label>
                <input type="number" name="nbWaterRooms" id="nbWaterRoomsInput" placeholder="<?php _e("Number shower rooms", "retxtdom");?>" value="<?= $nbWaterRooms; ?>" required>

                <label><?php _e("Number toilets", "retxtdom");?></label>
                <input type="number" name="nbWC" id="nbWCInput" placeholder="<?php _e("Number toilets", "retxtdom");?>" value="<?= $nbWC; ?>" required>
            </div>
        </div>
        <div id="agent">
            <div class="text">
                <label><?php _e("Agent linked to the ad", "retxtdom");?></label>
                <select name="agent" id="agents" onclick="reloadAgents();">
                    <?php
                        foreach($allAgents as $agent) {
                            $nameAgent = get_the_title($agent);
                            $idAgent = $agent->ID;
                            ?>
                            <option value="<?= $idAgent; ?>" <?=($idAgent==$agentSaved)?"selected":NULL;?>><?= $nameAgent; ?></option>
                            <?php
                        }
                    ?>
                </select>
            </div>
            <div class="select">
                <input type="checkbox" id="showAgent" name="showAgent" <?=($showAgent=='1')?"checked":NULL;?>>
                <label for="showAgent"><?php _e("Post agent contact", "retxtdom");?></label>
                <a target="_blank" href="post-new.php?post_type=agent"><?php _e("Add an agent", "retxtdom");?></a>
            </div>
        </div>
        <div id="addPictures" class="radio">
            <a href="#" id="insertAdPictures" class="button"><?php _e("Add pictures", "retxtdom");?></a>
            <input type="hidden" name="images" id="images" value="<?= $images; ?>">
        </div>
        <div id="showPictures" class="radio">
                <?php if(!is_null($images)) {
                    $ids = explode(';', $images);
                    foreach ($ids as $id) {
                        echo wp_get_attachment_image($id, array(150, 150), false, array("class" => "imgAd"));
                    }
                }?>
        </div>

        <?php 
            if(!empty($optionsDisplayads) && $optionsDisplayads !== "[]") {
                echo '<div id="customMFields">';
                foreach($customFieldsMF as $kField => $vField) { ?>
                    <div class="text">
                        <label><?=$kField;?></label>
                        <input type="text" name="CF<?=$kField;?>" value="<?=$vField;?>">
                    </div>
                    <?php 
                }
                echo "</div>";                         
            }
    }
      
    public function displayAdComplementariesMetaBox($ad) {
        $floor = intval(get_post_meta($ad->ID, "adFloor", true));
        $nbFloors = intval(get_post_meta($ad->ID, "adNbFloors", true));
        $furnished = sanitize_text_field(get_post_meta($ad->ID, "adFurnished", true));
        $year = intval(get_post_meta($ad->ID, "adYear", true));
        $typeHeating = sanitize_text_field(get_post_meta($ad->ID, "adTypeHeating", true));
        $typeKitchen = sanitize_text_field(get_post_meta($ad->ID, "adTypeKitchen", true));
        //$orientation = intval(get_post_meta($ad->ID, "adOrientation", true));
        $nbBalconies = intval(get_post_meta($ad->ID, "adNbBalconies", true));
        $elevator = sanitize_text_field(get_post_meta($ad->ID, "adElevator", true));
        $cellar = sanitize_text_field(get_post_meta($ad->ID, "adCellar", true));
        $terrace = sanitize_text_field(get_post_meta($ad->ID, "adTerrace", true));
        $DPE = intval(get_post_meta($ad->ID, "adDPE", true));
        $GES = intval(get_post_meta($ad->ID, "adGES", true));
        
        $optionsDisplayads = get_option(PLUGIN_RE_NAME."OptionsDisplayads")["customFields"];
        $customFieldsCF = array();
        if(!empty($optionsDisplayads) || $optionsDisplayads !== "[]") {
            foreach(json_decode($optionsDisplayads, true) as $field) {
                if($field["section"] === "complementaryFeatures") {
                    $customFieldsCF[$field["name"]] = sanitize_text_field(get_post_meta($ad->ID, "adCF".$field["name"], true));
                }
            }
        }
        ?>
        <div id="floors">
            <div class="text">
                <label><?php _e("Floor", "retxtdom");?></label>
                <input type="number" name="floor" id="floorInput" placeholder="<?php _e("Floor", "retxtdom");?>" value="<?= $floor; ?>" required>
            </div>
            <div class="text">
                <label><?php _e("Number floors", "retxtdom");?></label>
                <input type="number" name="nbFloors" id="nbFloorsInput" placeholder="<?php _e("Number floors", "retxtdom");?>" value="<?= $nbFloors; ?>" required>
            </div>
        </div>
        <div id="kitchenHeater">
            <div class="select">
            <label><?php _e("Type heating", "retxtdom");?></label>
            <select name="typeHeating">
                <option value="unknown" <?php selected($typeHeating, "unknown"); ?>>
                    <?php _e("Do not fill", "retxtdom");?>
                </option>
                <option value="individualGas" <?php selected($typeHeating, "individualGas"); ?>>
                    <?php _e("Invidual gas", "retxtdom");?>
                </option>
                <option value="collectiveGas" <?php selected($typeHeating, "collectiveGas"); ?>>
                    <?php _e("Collective gas", "retxtdom");?>
                </option>
                <option value="individualFuel" <?php selected($typeHeating, "individualFuel"); ?>>
                    <?php _e("Individual fuel", "retxtdom");?>
                </option>
                <option value="collectiveFuel" <?php selected($typeHeating, "collectiveFuel"); ?>>
                    <?php _e("Collective fuel", "retxtdom");?>
                </option>
                <option value="individualElectric" <?php selected($typeHeating, "individualElectric"); ?>>
                    <?php _e("Individual electric", "retxtdom");?>
                </option>
                <option value="collectiveElectric" <?php selected($typeHeating, "collectiveElectric"); ?>>
                    <?php _e("Collective electric", "retxtdom");?>
                </option>
            </select>
            </div>
            <div class="select">
                <label><?php _e("Type kitchen", "retxtdom");?></label>
                <select name="typeKitchen">
                    <option value="unknown" <?=($typeKitchen==="unknown")?"selected":NULL;?>>
                        <?php _e("Do not fill", "retxtdom");?>
                    </option>
                    <option value="notEquipped" <?php selected($typeKitchen, "notEquipped"); ?>><?php _e("Not equipped", "retxtdom");?></option>
                    <option value="kitchenette" <?php selected($typeKitchen, "kitchenette"); ?>><?php _e("Kitchenette", "retxtdom");?></option>
                    <option value="american" <?php selected($typeKitchen, "american"); ?>><?php _e("American", "retxtdom");?></option>
                    <option value="industrial" <?php selected($typeKitchen, "industrial"); ?>><?php _e("Industrial", "retxtdom");?></option>
                </select>
            </div>
        </div>
        <div id="balconies">
            <div class="text">
                <label><?php _e("Number balconies", "retxtdom");?></label>
                <input type="number" name="nbBalconies" id="nbBalconiesInput" placeholder="<?php _e("Number balconies", "retxtdom");?>" value="<?= $nbBalconies; ?>" required>
            </div>
        </div>
        <div id="propertyHas">
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="elevatorInput" name="elevator"<?php checked($elevator, '1'); ?>>
                    <span class="slider"></span>
                </label> 
                <label for="elevator"><?php _e("Elevator", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="cellarInput" name="cellar" <?php checked($cellar, '1'); ?>>
                    <span class="slider"></span>
                </label> 
                <label for="cellar"><?php _e("Cellar", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="terraceInput" name="terrace" <?php checked($terrace, '1'); ?>>
                    <span class="slider"></span>
                </label>   
                <label for="terrace"><?php _e("Terrace", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="furnishedInput" name="furnished" <?php checked($furnished, '1'); ?>>
                    <span class="slider"></span>
                </label>   
                <label for="furnished"><?php _e("Furnished", "retxtdom");?></label>
            </div>
        </div>
        <div id="year">
            <div class="text">
                <label><?php _e("Construction year", "retxtdom");?></label>
                <input type="number" name="year" id="yearInput" placeholder="<?php _e("Construction year", "retxtdom");?>" value="<?= $year; ?>" required>
            </div>
        </div>
        <div id="diag">
            <div class="text">
                <label><?php _e("EPD in kWhPE/m²/year", "retxtdom");?></label>
                <input type="number" id="DPE" name="DPE" value="<?= $DPE; ?>">
            </div>
            <div class="text">
                <label><?php _e("Greenhouse gas in kg eqCO2/m²/year", "retxtdom");?></label>
                <input type="number" id="GES" name="GES" value="<?= $GES; ?>">
            </div>
        </div>

        <?php 
            if(!empty($optionsDisplayads) && $optionsDisplayads !== "[]") {
                echo '<div id="customCFields">';
                foreach($customFieldsCF as $kField => $vField) { ?>
                    <div class="text">
                        <label><?=$kField;?></label>
                        <input type="text" name="CF<?=$kField;?>" value="<?=$vField;?>">
                    </div>
                    <?php 
                }
                echo "</div>";                         
            }

    }
    
}
?>