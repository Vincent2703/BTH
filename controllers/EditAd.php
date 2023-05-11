<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Create or edit ad
 * 
 */
class REALM_EditAd {
    
    /*
     * Add meta boxes 
     */
    public function addMetaBoxes() {
        add_meta_box( //Main features
            "adMainFeaturesMetaBox", //ID HTML
            __("Main features", "retxtdom"), //Display
            array($this, "mainFeaturesMetaBox"), //Callback
            "re-ad", //Custom type
            "normal", //Location on the page
            "high" //Priority
        );
             
        add_meta_box( //Complementary features
            "adAdditionalFeaturesMetaBox", //ID HTML
            __("Additional features", "retxtdom"), //Display
            array($this, "additionalFeaturesMetaBox"), //Callback
            "re-ad", //Custom type
            "advanced", //Location on the page
            "high" //Priority
        );
    }
    
    /* Save the post */
    public function savePost($adId, $ad) {
        if($ad->post_type == "re-ad") {
            if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE || (!isset($_POST["nonceSecurity"]) || (isset($_POST["nonceSecurity"]) && !wp_verify_nonce($_POST["nonceSecurity"], "formEditAd")))) { //Don't save if it's an autosave or if the nonce is inexistant/incorrect
                return;
            }else if(isset($_POST["nonceSecurity"]) && wp_verify_nonce($_POST["nonceSecurity"], "formEditAd")) {
                require_once(PLUGIN_RE_PATH."models/admin/AdAdmin.php");
                remove_action("save_post_re-ad", array($this, "savePost")); //Avoid infinite loop
                REALM_AdAdmin::setData($adId, $ad); //Save in BDD
            }
        }
    }
    
    public function mainFeaturesMetaBox($ad) {
        require_once(PLUGIN_RE_PATH."models/admin/AdAdmin.php");
        REALM_AdAdmin::getMainFeatures($ad->ID); //Get values
        wp_nonce_field("formEditAd", "nonceSecurity"); //Add nonce
        ?>
        <div id="refAgency">
            <div class="text">
                <label><?php _e("Ad's reference", "retxtdom");?><span id="generateRef" onclick="document.getElementById('refAgencyInput').value = <?= $ad->ID;?>;"><?php _e("Generate a reference", "retxtdom");?></span></label>
                <input type="text" name="refAgency" id="refAgencyInput" placeholder="Eg : A-123" value="<?= REALM_AdAdmin::$refAgency; ?>">
            </div>
        </div>
        <div id="prices">
            <div class="text">
                <label><?php _e("Property price", "retxtdom");?> <abbr title="<?php _e("Charges included", "retxtdom"); ?>"><sup>?</sup></abbr></label>
                <input type="number" name="price" id="priceInput" placeholder="Eg : 180000" value="<?= REALM_AdAdmin::$price; ?>" required>
            </div>
            <div class="text">
                <label><?php _e("Fees amount", "retxtdom");?></label>
                <input type="number" name="fees" id="feesInput" placeholder="Eg : 85" value="<?= REALM_AdAdmin::$fees; ?>" required>
            </div>
        </div>
        <div id="surfaces">
            <div class="text">
                <label><?php _e("Living space", "retxtdom");?> (m²)</label>
                <input type="number" name="surface" id="surfaceInput" placeholder="Eg : 90" value="<?= REALM_AdAdmin::$surface; ?>"  required>
            </div>
            <div class="text">
                <label><?php _e("Land area", "retxtdom");?> (m²)</label>
                <input type="number" name="landSurface" id="landSurfaceInput" placeholder="Eg : 90" value="<?= REALM_AdAdmin::$landSurface; ?>">
            </div>
        </div>
        <div id="address">
            <div class="text">
                <label><?php _e("Property address", "retxtdom");?></label>
                <input type="text" name="address" id="addressInput" autocomplete="off" placeholder='Eg : <?php _e("123 Chester Square, London", "retxtdom");?>' value="<?= REALM_AdAdmin::$address; ?>" required>
            </div>             

            <div class="radio">
                <input type="radio" name="showMap" id="map1" value="onlyPC" <?php checked(REALM_AdAdmin::$showMap, "onlyPC");?> required><label for="map1"><?php _e("Show postal code and city", "retxtdom");?></label>
                <input type="radio" name="showMap" id="map2" value="all" <?php checked(REALM_AdAdmin::$showMap, "all");?> required><label for="map2"><?php _e("Show full address", "retxtdom");?></label>
            </div>
        </div>
        <div id="nbRooms">
            <div class="text">
                <label><?php _e("Number of rooms", "retxtdom");?></label>       
                <input type="number" name="nbRooms" id="nbRoomsInput" placeholder="<?php _e("Number of rooms", "retxtdom");?>" value="<?= REALM_AdAdmin::$nbRooms; ?>">
            </div>
        </div>
        <div id="otherRooms">
            <div class="text">
                <label><?php _e("Number of bedrooms", "retxtdom");?></label>
                <input type="number" name="nbBedrooms" id="nbBedroomsInput" placeholder="<?php _e("Number of bedrooms", "retxtdom");?>" value="<?= REALM_AdAdmin::$nbBedrooms; ?>">

                <label><?php _e("Number of bathrooms", "retxtdom");?></label>
                <input type="number" name="nbBathrooms" id="nbBathroomsInput" placeholder="<?php _e("Number of bathrooms", "retxtdom");?>" value="<?= REALM_AdAdmin::$nbBathrooms; ?>">
            </div>
            <div class="text">
                <label><?php _e("Number of shower rooms", "retxtdom");?></label>
                <input type="number" name="nbWaterRooms" id="nbWaterRoomsInput" placeholder="<?php _e("Number of shower rooms", "retxtdom");?>" value="<?= REALM_AdAdmin::$nbWaterRooms; ?>">

                <label><?php _e("Number of toilets", "retxtdom");?></label>
                <input type="number" name="nbWC" id="nbWCInput" placeholder="<?php _e("Number of toilets", "retxtdom");?>" value="<?= REALM_AdAdmin::$nbWC; ?>">
            </div>
        </div>
        <div id="agent">
            <div class="text">
                <label><?php _e("Agent linked to the ad", "retxtdom");?></label>
                <select name="agent" id="agents" onclick="reloadAgents();">
                    <?php
                        foreach(REALM_AdAdmin::$allAgents as $agent) {
                            $nameAgent = esc_attr(get_the_title($agent));
                            $idAgent = absint($agent->ID);
                            ?>
                            <option value="<?= $idAgent; ?>" <?php selected($idAgent, REALM_AdAdmin::$idAgent); ?>><?= $nameAgent; ?></option>
                            <?php
                        }
                    ?>
                </select>
            </div>
            <div class="select">
                <input type="checkbox" id="showAgent" name="showAgent" <?php checked(REALM_AdAdmin::$showAgent, '1'); ?>>
                <label for="showAgent"><?php _e("Publish the agent contact", "retxtdom");?></label>
                <a target="_blank" href="post-new.php?post_type=agent"><?php _e("Add an agent", "retxtdom");?></a>
            </div>
        </div>
        <div id="addPictures">
            <a href="#" id="insertAdPictures" class="button"><?php empty(REALM_AdAdmin::$images)?_e("Add pictures", "retxtdom"):_e("Replace pictures", "retxtdom");?></a>
            <input type="hidden" name="images" id="images" value="<?= REALM_AdAdmin::$images; ?>">
        </div>
        <div id="showPictures">
            <?php if(!empty(REALM_AdAdmin::$images)) {
                $ids = explode(';', REALM_AdAdmin::$images);
                foreach ($ids as $id) { ?>
                    <div class="aPicture" data-imgId="<?=absint($id);?>">
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
            if(isset(REALM_AdAdmin::$customFieldsMF) && is_array(REALM_AdAdmin::$customFieldsMF)) {
                echo '<div id="customMFields">';
                foreach(REALM_AdAdmin::$customFieldsMF as $kField => $vField) { ?>
                    <div class="text">
                        <label><?=$kField;?></label>
                        <input type="text" name="CF<?=$vField["nameAttr"];?>" value="<?=$vField["value"];?>">
                    </div>
                    <?php 
                }
                echo "</div>";                         
            }
    }
      
    public function additionalFeaturesMetaBox($ad) {
        require_once(PLUGIN_RE_PATH."models/admin/AdAdmin.php");
        REALM_AdAdmin::getAdditionalFeatures($ad->ID); //Get values
        ?>
        <div id="floors">
            <div class="text">
                <label><?php _e("Floor", "retxtdom");?></label>
                <input type="number" name="floor" id="floorInput" placeholder="<?php _e("Floor", "retxtdom");?>" value="<?= REALM_AdAdmin::$floor; ?>">
            </div>
            <div class="text">
                <label><?php _e("Number of floors", "retxtdom");?></label>
                <input type="number" name="nbFloors" id="nbFloorsInput" placeholder="<?php _e("Number of floors", "retxtdom");?>" value="<?= REALM_AdAdmin::$nbFloors; ?>">
            </div>
        </div>
        <div id="kitchenHeater">
            <div class="select">
            <label><?php _e("Type of heating", "retxtdom");?></label>&nbsp;
            <select name="typeHeating">
                <option value="Unknown" <?php selected(REALM_AdAdmin::$typeHeating, "Unknown"); ?>>
                    <?php _e("Do not fill", "retxtdom");?>
                </option>
                <option value="Individual gas" <?php selected(REALM_AdAdmin::$typeHeating, "Individual gas"); ?>>
                    <?php _e("Invidual gas", "retxtdom");?>
                </option>
                <option value="Collective gas" <?php selected(REALM_AdAdmin::$typeHeating, "CollectiveGas"); ?>>
                    <?php _e("Collective gas", "retxtdom");?>
                </option>
                <option value="Individual fuel" <?php selected(REALM_AdAdmin::$typeHeating, "Individual fuel"); ?>>
                    <?php _e("Individual fuel", "retxtdom");?>
                </option>
                <option value="Collective fuel" <?php selected(REALM_AdAdmin::$typeHeating, "Collective fuel"); ?>>
                    <?php _e("Collective fuel", "retxtdom");?>
                </option>
                <option value="Individual electric" <?php selected(REALM_AdAdmin::$typeHeating, "Individual electric"); ?>>
                    <?php _e("Individual electric", "retxtdom");?>
                </option>
                <option value="Collective electric" <?php selected(REALM_AdAdmin::$typeHeating, "Collective electric"); ?>>
                    <?php _e("Collective electric", "retxtdom");?>
                </option>
            </select>
            </div>
            <div class="select">
                <label><?php _e("Type of kitchen", "retxtdom");?></label>&nbsp;
                <select name="typeKitchen">
                    <option value="unknown" <?php selected(REALM_AdAdmin::$typeKitchen, "unknown"); ?>>
                        <?php _e("Do not fill", "retxtdom");?>
                    </option>
                    <option value="Not equipped" <?php selected(REALM_AdAdmin::$typeKitchen, "Not equipped"); ?>><?php _e("Not equipped", "retxtdom");?></option>
                    <option value="Kitchenette" <?php selected(REALM_AdAdmin::$typeKitchen, "Kitchenette"); ?>><?php _e("Kitchenette", "retxtdom");?></option>
                    <option value="Standard" <?php selected(REALM_AdAdmin::$typeKitchen, "Standard"); ?>><?php _e("Standard", "retxtdom");?></option>
                    <option value="Industrial" <?php selected(REALM_AdAdmin::$typeKitchen, "Industrial"); ?>><?php _e("Industrial", "retxtdom");?></option>
                </select>
            </div>
        </div>
        <div id="balconies">
            <div class="text">
                <label><?php _e("Number of balconies", "retxtdom");?></label>
                <input type="number" name="nbBalconies" id="nbBalconiesInput" placeholder="<?php _e("Number of balconies", "retxtdom");?>" value="<?= REALM_AdAdmin::$nbBalconies; ?>">
            </div>
        </div>
        <div id="propertyHas">
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="elevatorInput" name="elevator"<?php checked(REALM_AdAdmin::$elevator); ?>>
                    <span class="slider"></span>
                </label> 
                <label for="elevator"><?php _e("Elevator", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="basementInput" name="basement" <?php checked(REALM_AdAdmin::$basement); ?>>
                    <span class="slider"></span>
                </label> 
                <label for="basement"><?php _e("Basement", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="terraceInput" name="terrace" <?php checked(REALM_AdAdmin::$terrace); ?>>
                    <span class="slider"></span>
                </label>   
                <label for="terrace"><?php _e("Terrace", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="furnishedInput" name="furnished" <?php checked(REALM_AdAdmin::$furnished); ?>>
                    <span class="slider"></span>
                </label>   
                <label for="furnished"><?php _e("Furnished", "retxtdom");?></label>
            </div>
        </div>
        <div id="year">
            <div class="text">
                <label><?php _e("Construction year", "retxtdom");?></label>
                <input type="number" name="year" id="yearInput" placeholder="<?php _e("Construction year", "retxtdom");?>" value="<?= REALM_AdAdmin::$year; ?>">
            </div>
        </div>
        <div id="diag">
            <div class="text">
                <label><?php _e("EPD in kWhPE/m²/year", "retxtdom");?></label>
                <input type="number" id="DPE" name="DPE" min="0" max="500" value="<?= REALM_AdAdmin::$DPE; ?>">
            </div>
            <div class="text">
                <label><?php _e("Greenhouse gas in kg eqCO2/m²/year", "retxtdom");?></label>
                <input type="number" id="GES" name="GES" min="0" max="100" value="<?= REALM_AdAdmin::$GES; ?>">
            </div>
        </div>

        <?php 
            if(isset(REALM_AdAdmin::$customFieldsAF) && is_array(REALM_AdAdmin::$customFieldsAF)) {
                echo '<div id="customCFields">';
                foreach(REALM_AdAdmin::$customFieldsAF as $kField => $vField) { ?>
                    <div class="text">
                        <label><?=$kField;?></label>
                        <input type="text" name="CF<?=$vField["nameAttr"];?>" value="<?=$vField["value"];?>">
                    </div>
                    <?php 
                }
                echo "</div>";                         
            }

    }
    
}
?>