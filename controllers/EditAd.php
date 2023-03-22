<?php
class EditAd {
    public function addMetaBoxes() {
        add_meta_box( 
            "adBasicsMetaBox", //ID HTML
            __("Basic information", "retxtdom"), //Display
            array($this, "displayAdMainFeaturesMetaBox"), //Callback
            "re-ad", //Custom type
            "normal", //Location on the page
            "high" //Priority
        );
             
        add_meta_box( 
            "adComplementariesMetaBox", //ID HTML
            __("Complementary information", "retxtdom"), //Display
            array($this, "displayAdComplementaryFeaturesMetaBox"), //Callback
            "re-ad", //Custom type
            "advanced", //Location on the page
            "high" //Priority
        );
    }
    
    public function savePost($adId, $ad) {
        if($ad->post_type == "re-ad") {
            if(isset($_POST["nonceSecurity"]) && wp_verify_nonce($_POST["nonceSecurity"], "formImportAds")) {
                return;
            }else if(isset($_POST["nonceSecurity"]) && wp_verify_nonce($_POST["nonceSecurity"], "formEditAd")) {
                if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
                    return;
                }
                require_once(PLUGIN_RE_PATH."models/admin/AdAdmin.php");
                AdAdmin::setData($adId, $ad);           
            }
        }
    }
    
    public function displayAdMainFeaturesMetaBox($ad) {
        require_once(PLUGIN_RE_PATH."models/admin/AdAdmin.php");
        AdAdmin::getMainFeatures($ad->ID);
        wp_nonce_field("formEditAd", "nonceSecurity");
        ?>
        <div id="refAgency">
            <div class="text">
                <label><?php _e("Ad reference", "retxtdom");?><span id="generateRef" onclick="document.getElementById('refAgencyInput').value = <?= $ad->ID;?>;"><?php _e("Generate a reference", "retxtdom");?></span></label>
                <input type="text" name="refAgency" id="refAgencyInput" placeholder="Ex : A-123" value="<?= adAdmin::$refAgency; ?>">
            </div>
        </div>
        <div id="prices">
            <div class="text">
                <label><?php _e("Property price", "retxtdom");?> <abbr title="<?php _e("Charges included", "retxtdom"); ?>"><sup>?</sup></abbr></label>
                <input type="number" name="price" id="priceInput" placeholder="Ex : 180000" value="<?= adAdmin::$price; ?>" required>
            </div>
            <div class="text">
                <label><?php _e("Fees amount", "retxtdom");?></label>
                <input type="number" name="fees" id="feesInput" placeholder="Ex : 85" value="<?= adAdmin::$fees; ?>" required>
            </div>
        </div>
        <div id="surfaces">
            <div class="text">
                <label><?php _e("Living space", "retxtdom");?> (m²)</label>
                <input type="number" name="surface" id="surfaceInput" placeholder="Ex : 90" value="<?= adAdmin::$surface; ?>"  required>
            </div>
            <div class="text">
                <label><?php _e("Land area", "retxtdom");?> (m²)</label>
                <input type="number" name="landSurface" id="landSurfaceInput" placeholder="Ex : 90" value="<?= adAdmin::$landSurface; ?>">
            </div>
        </div>
        <div id="address">
            <div class="text">
                <label><?php _e("Property address", "retxtdom");?></label>
                <input type="text" name="address" id="addressInput" autocomplete="off" placeholder='Ex : <?php _e("123 Chester Square, London", "retxtdom");?>' value="<?= adAdmin::$address; ?>" required>
            </div>             

            <div class="radio">
                <input type="radio" name="showMap" id="map1" value="onlyPC" <?php checked(adAdmin::$showMap, "onlyPC");?> required><label for="map1"><?php _e("Show postal code and city", "retxtdom");?></label>
                <input type="radio" name="showMap" id="map2" value="all" <?php checked(adAdmin::$showMap, "all");?> required><label for="map2"><?php _e("Show full address", "retxtdom");?></label>
            </div>
        </div>
        <div id="nbRooms">
            <div class="text">
                <label><?php _e("Number rooms", "retxtdom");?></label>       
                <input type="number" name="nbRooms" id="nbRoomsInput" placeholder="<?php _e("Number rooms", "retxtdom");?>" value="<?= adAdmin::$nbRooms; ?>">
            </div>
        </div>
        <div id="otherRooms">
            <div class="text">
                <label><?php _e("Number bedrooms", "retxtdom");?></label>
                <input type="number" name="nbBedrooms" id="nbBedroomsInput" placeholder="<?php _e("Number bedrooms", "retxtdom");?>" value="<?= adAdmin::$nbBedrooms; ?>">

                <label><?php _e("Number bathrooms", "retxtdom");?></label>
                <input type="number" name="nbBathrooms" id="nbBathroomsInput" placeholder="<?php _e("Number bathrooms", "retxtdom");?>" value="<?= adAdmin::$nbBathrooms; ?>">
            </div>
            <div class="text">
                <label><?php _e("Number shower rooms", "retxtdom");?></label>
                <input type="number" name="nbWaterRooms" id="nbWaterRoomsInput" placeholder="<?php _e("Number shower rooms", "retxtdom");?>" value="<?= adAdmin::$nbWaterRooms; ?>">

                <label><?php _e("Number toilets", "retxtdom");?></label>
                <input type="number" name="nbWC" id="nbWCInput" placeholder="<?php _e("Number toilets", "retxtdom");?>" value="<?= adAdmin::$nbWC; ?>">
            </div>
        </div>
        <div id="agent">
            <div class="text">
                <label><?php _e("Agent linked to the ad", "retxtdom");?></label>
                <select name="agent" id="agents" onclick="reloadAgents();">
                    <?php
                        foreach(adAdmin::$allAgents as $agent) {
                            $nameAgent = get_the_title($agent);
                            $idAgent = $agent->ID;
                            ?>
                            <option value="<?= $idAgent; ?>" <?=($idAgent==adAdmin::$agentSaved)?"selected":NULL;?>><?= $nameAgent; ?></option>
                            <?php
                        }
                    ?>
                </select>
            </div>
            <div class="select">
                <input type="checkbox" id="showAgent" name="showAgent" <?=(adAdmin::$showAgent=='1')?"checked":NULL;?>>
                <label for="showAgent"><?php _e("Post agent contact", "retxtdom");?></label>
                <a target="_blank" href="post-new.php?post_type=agent"><?php _e("Add an agent", "retxtdom");?></a>
            </div>
        </div>
        <div id="addPictures">
            <a href="#" id="insertAdPictures" class="button"><?php _e("Add pictures", "retxtdom");?></a>
            <input type="hidden" name="images" id="images" value="<?= adAdmin::$images; ?>">
        </div>
        <div id="showPictures">
            <?php if(!empty(adAdmin::$images)) {
                $ids = explode(';', adAdmin::$images);
                foreach ($ids as $id) { ?>
                    <div class="aPicture" data-imgId="<?=$id;?>">
                        <?= wp_get_attachment_image($id, array(150, 150), false, array("class" => "imgAd")); ?>
                        <div class="controlPicture">
                            <span class="moveToLeft" onclick="movePicture(this, 'left');">←</span>
                            <span class="deletePicture" onclick="deletePicture(this);"><?php _e("Delete", "retxtdom"); ?></span>
                            <span class="moveToRight" onclick="movePicture(this, 'right');">→</span></div>
                    </div>
                <?php }
            }?>
        </div>

        <?php 
            if(isset(adAdmin::$customFieldsMF) && !empty(adAdmin::$customFieldsMF)) {
                echo '<div id="customMFields">';
                foreach(adAdmin::$customFieldsMF as $kField => $vField) { ?>
                    <div class="text">
                        <label><?=$kField;?></label>
                        <input type="text" name="CF<?=$kField;?>" value="<?=$vField;?>">
                    </div>
                    <?php 
                }
                echo "</div>";                         
            }
    }
      
    public function displayAdComplementaryFeaturesMetaBox($ad) {
        require_once(PLUGIN_RE_PATH."models/admin/AdAdmin.php");
        AdAdmin::getComplementaryFeatures($ad->ID);
        ?>
        <div id="floors">
            <div class="text">
                <label><?php _e("Floor", "retxtdom");?></label>
                <input type="number" name="floor" id="floorInput" placeholder="<?php _e("Floor", "retxtdom");?>" value="<?= AdAdmin::$floor; ?>">
            </div>
            <div class="text">
                <label><?php _e("Number floors", "retxtdom");?></label>
                <input type="number" name="nbFloors" id="nbFloorsInput" placeholder="<?php _e("Number floors", "retxtdom");?>" value="<?= AdAdmin::$nbFloors; ?>">
            </div>
        </div>
        <div id="kitchenHeater">
            <div class="select">
            <label><?php _e("Type heating", "retxtdom");?></label>&nbsp;
            <select name="typeHeating">
                <option value="unknown" <?php selected(AdAdmin::$typeHeating, "unknown"); ?>>
                    <?php _e("Do not fill", "retxtdom");?>
                </option>
                <option value="individualGas" <?php selected(AdAdmin::$typeHeating, "individualGas"); ?>>
                    <?php _e("Invidual gas", "retxtdom");?>
                </option>
                <option value="collectiveGas" <?php selected(AdAdmin::$typeHeating, "collectiveGas"); ?>>
                    <?php _e("Collective gas", "retxtdom");?>
                </option>
                <option value="individualFuel" <?php selected(AdAdmin::$typeHeating, "individualFuel"); ?>>
                    <?php _e("Individual fuel", "retxtdom");?>
                </option>
                <option value="collectiveFuel" <?php selected(AdAdmin::$typeHeating, "collectiveFuel"); ?>>
                    <?php _e("Collective fuel", "retxtdom");?>
                </option>
                <option value="individualElectric" <?php selected(AdAdmin::$typeHeating, "individualElectric"); ?>>
                    <?php _e("Individual electric", "retxtdom");?>
                </option>
                <option value="collectiveElectric" <?php selected(AdAdmin::$typeHeating, "collectiveElectric"); ?>>
                    <?php _e("Collective electric", "retxtdom");?>
                </option>
            </select>
            </div>
            <div class="select">
                <label><?php _e("Type kitchen", "retxtdom");?></label>&nbsp;
                <select name="typeKitchen">
                    <option value="unknown" <?=(AdAdmin::$typeKitchen==="unknown")?"selected":NULL;?>>
                        <?php _e("Do not fill", "retxtdom");?>
                    </option>
                    <option value="notEquipped" <?php selected(AdAdmin::$typeKitchen, "notEquipped"); ?>><?php _e("Not equipped", "retxtdom");?></option>
                    <option value="kitchenette" <?php selected(AdAdmin::$typeKitchen, "kitchenette"); ?>><?php _e("Kitchenette", "retxtdom");?></option>
                    <option value="american" <?php selected(AdAdmin::$typeKitchen, "american"); ?>><?php _e("American", "retxtdom");?></option>
                    <option value="industrial" <?php selected(AdAdmin::$typeKitchen, "industrial"); ?>><?php _e("Industrial", "retxtdom");?></option>
                </select>
            </div>
        </div>
        <div id="balconies">
            <div class="text">
                <label><?php _e("Number balconies", "retxtdom");?></label>
                <input type="number" name="nbBalconies" id="nbBalconiesInput" placeholder="<?php _e("Number balconies", "retxtdom");?>" value="<?= AdAdmin::$nbBalconies; ?>">
            </div>
        </div>
        <div id="propertyHas">
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="elevatorInput" name="elevator"<?php checked(AdAdmin::$elevator, '1'); ?>>
                    <span class="slider"></span>
                </label> 
                <label for="elevator"><?php _e("Elevator", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="cellarInput" name="cellar" <?php checked(AdAdmin::$cellar, '1'); ?>>
                    <span class="slider"></span>
                </label> 
                <label for="cellar"><?php _e("Cellar", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="terraceInput" name="terrace" <?php checked(AdAdmin::$terrace, '1'); ?>>
                    <span class="slider"></span>
                </label>   
                <label for="terrace"><?php _e("Terrace", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="furnishedInput" name="furnished" <?php checked(AdAdmin::$furnished, '1'); ?>>
                    <span class="slider"></span>
                </label>   
                <label for="furnished"><?php _e("Furnished", "retxtdom");?></label>
            </div>
        </div>
        <div id="year">
            <div class="text">
                <label><?php _e("Construction year", "retxtdom");?></label>
                <input type="number" name="year" id="yearInput" placeholder="<?php _e("Construction year", "retxtdom");?>" value="<?= AdAdmin::$year; ?>">
            </div>
        </div>
        <div id="diag">
            <div class="text">
                <label><?php _e("EPD in kWhPE/m²/year", "retxtdom");?></label>
                <input type="number" id="DPE" name="DPE" min="0" max="500" value="<?= AdAdmin::$DPE; ?>">
            </div>
            <div class="text">
                <label><?php _e("Greenhouse gas in kg eqCO2/m²/year", "retxtdom");?></label>
                <input type="number" id="GES" name="GES" min="0" max="100" value="<?= AdAdmin::$GES; ?>">
            </div>
        </div>

        <?php 
            if(isset(AdAdmin::$customFieldsCF) && !empty(AdAdmin::$customFieldsCF)) {
                echo '<div id="customCFields">';
                foreach(AdAdmin::$customFieldsCF as $kField => $vField) { ?>
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