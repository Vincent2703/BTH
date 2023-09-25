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
    
    private static $ad;
    
    /*
     * Add meta boxes 
     */
    public function addMetaBoxes() {
        global $post;
        $idPost = intval($post->ID);
        require_once(PLUGIN_RE_PATH."models/AdModel.php");
        self::$ad = REALM_AdModel::getAd($idPost);
        
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
        
        if(PLUGIN_RE_REP) {
            add_meta_box( //Housing files authorization
                "adSubmissionMetaBox", //ID HTML
                __("Housing file submissions", "retxtdom"), //Display
                array($this, "submissionMetabox"), //Callback
                "re-ad", //Custom type
                "side", //Location on the page
                "core" //Priority
            );
        }
    }
    
    /* Save the post */
    public function savePost($adId, $ad) {
        if($ad->post_type == "re-ad") {
            if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
                $currentUserId = get_current_user_id();
                $currentUser = get_user_by("ID", $currentUserId);
                if(!$currentUser) {
                    return;
                }
                $currentUserRole = $currentUser->roles[0];
                require_once(PLUGIN_RE_PATH."models/UserModel.php");
                if($currentUserRole === "agency") {
                    $agent = REALM_UserModel::getAgentsAgency($currentUserId)[0];
                }else if($currentUserRole === "agent") {
                    $agent = $currentUserId;
                }else if($currentUserRole === "administrator") {
                    $agent = REALM_UserModel::getUsersByRole("agent")[0];
                }
                update_post_meta($adId, "adIdAgent", intval($agent));
            }else if(!isset($_POST["nonceSecurity"]) || (isset($_POST["nonceSecurity"]) && !wp_verify_nonce($_POST["nonceSecurity"], "formEditAd"))) { //Don't save if the nonce is inexistant/incorrect                return;
                return;              
            }else if(isset($_POST["nonceSecurity"]) && is_numeric(wp_verify_nonce($_POST["nonceSecurity"], "formEditAd"))) {
                require_once(PLUGIN_RE_PATH."models/AdModel.php");
                remove_action("save_post_re-ad", array($this, "savePost")); //Avoid infinite loop
                REALM_AdModel::updateAd($adId, $ad); //Save in BDD
            }
        }
    }
    
    public function mainFeaturesMetaBox($adWP) {
        require_once(PLUGIN_RE_PATH."models/UserModel.php");
                
        $ad = self::$ad;
        $currentUserID = get_current_user_id();
        $currentUser = get_user_by("ID", $currentUserID);
        $userRole = $currentUser->roles[0];
        
        if($userRole === "administrator") {
            $agents = REALM_UserModel::getUsersByRole("agent");
        }else if($userRole === "agent") {
            $user = REALM_UserModel::getUser($currentUserID);
            $agents = REALM_UserModel::getAgentsAgency($user["agentAgency"]);
        }else if($userRole === "agency") {
            $agents = REALM_UserModel::getAgentsAgency($currentUserID);
        }
        if(is_numeric($ad["agent"])) {
            $agentSelected = $ad["agent"];
        }else if($userRole === "agent") {
            $agentSelected = get_current_user_id();
        }else{
            $agentSelected = null;
        }
        
        $generalOptions = get_option(PLUGIN_RE_NAME."OptionsGeneral");
        $currency = $generalOptions["currency"];
        $areaUnit = $generalOptions["areaUnit"];
        wp_nonce_field("formEditAd", "nonceSecurity"); //Add nonce
        ?>
        <div id="refAgency">
            <div class="text">
                <label><?php _e("Ad's reference", "retxtdom");?><span id="generateRef" onclick="document.getElementById('refAgencyInput').value = <?= $adWP->ID;?>;"><?php _e("Generate a reference", "retxtdom");?></span></label>
                <input type="text" name="refAgency" id="refAgencyInput" placeholder="Eg : A-123" value="<?= $ad["refAd"]; ?>">
            </div>
        </div>
        <div id="prices">
            <div class="text">
                <label><?php _e("Property price", "retxtdom");?> (<a target="_blank" href="<?=admin_url("edit.php?post_type=re-ad&page=realmoptions");?>"><?=$currency;?></a>) <abbr title="<?php _e("Charges included", "retxtdom"); ?>"><sup>?</sup></abbr></label>
                <input type="number" name="price" id="priceInput" placeholder="Eg : 180000" value="<?= $ad["price"]; ?>" required>
            </div>
            <div class="text">
                <label><?php _e("Fees amount", "retxtdom");?> (<a target="_blank" href="<?=admin_url("edit.php?post_type=re-ad&page=realmoptions");?>"><?=$currency;?></a>)</label>
                <input type="number" name="fees" id="feesInput" placeholder="Eg : 85" value="<?= $ad["fees"]; ?>" required>
            </div>
        </div>
        <div id="surfaces">
            <div class="text">
                <label><?php _e("Living space", "retxtdom");?> (<a target="_blank" href="<?=admin_url("edit.php?post_type=re-ad&page=realmoptions");?>"><?=$areaUnit;?></a>)</label>
                <input type="number" name="surface" id="surfaceInput" placeholder="Eg : 90" value="<?= $ad["surface"]; ?>"  required>
            </div>
            <div class="text">
                <label><?php _e("Land area", "retxtdom");?> (<a target="_blank" href="<?=admin_url("edit.php?post_type=re-ad&page=realmoptions");?>"><?=$areaUnit;?></a>)</label>
                <input type="number" name="landSurface" id="landSurfaceInput" placeholder="Eg : 90" value="<?= $ad["landSurface"]; ?>">
            </div>
        </div>
        <div id="address">
            <div class="text">
                <label><?php _e("Property address", "retxtdom");?></label>
                <input type="text" name="address" id="addressInput" autocomplete="off" placeholder='Eg : <?php _e("123 Chester Square, London", "retxtdom");?>' value="<?= $ad["fullAddress"]; ?>" required>
            </div>             

            <div class="radio">
                <input type="radio" name="showMap" id="map1" value="onlyPC" <?php checked($ad["showMap"], "onlyPC");?> required><label for="map1"><?php _e("Show postal code and city", "retxtdom");?></label>
                <input type="radio" name="showMap" id="map2" value="all" <?php checked($ad["showMap"], "all");?> required><label for="map2"><?php _e("Show full address", "retxtdom");?></label>
            </div>
        </div>
        <div id="nbRooms">
            <div class="text">
                <label><?php _e("Number of rooms", "retxtdom");?></label>       
                <input type="number" name="nbRooms" id="nbRoomsInput" placeholder="<?php _e("Number of rooms", "retxtdom");?>" value="<?= $ad["nbRooms"]; ?>">
            </div>
        </div>
        <div id="otherRooms">
            <div class="text">
                <label><?php _e("Number of bedrooms", "retxtdom");?></label>
                <input type="number" name="nbBedrooms" id="nbBedroomsInput" placeholder="<?php _e("Number of bedrooms", "retxtdom");?>" value="<?= $ad["nbBedrooms"]; ?>">

                <label><?php _e("Number of bathrooms", "retxtdom");?></label>
                <input type="number" name="nbBathrooms" id="nbBathroomsInput" placeholder="<?php _e("Number of bathrooms", "retxtdom");?>" value="<?= $ad["nbBathrooms"]; ?>">
            </div>
            <div class="text">
                <label><?php _e("Number of shower rooms", "retxtdom");?></label>
                <input type="number" name="nbWaterRooms" id="nbWaterRoomsInput" placeholder="<?php _e("Number of shower rooms", "retxtdom");?>" value="<?= $ad["nbWaterRooms"]; ?>">

                <label><?php _e("Number of toilets", "retxtdom");?></label>
                <input type="number" name="nbWC" id="nbWCInput" placeholder="<?php _e("Number of toilets", "retxtdom");?>" value="<?= $ad["nbWC"]; ?>">
            </div>
        </div>
        <div id="agent">
            <div class="text">
                <label><?php _e("Agent linked to the ad", "retxtdom");?></label>
                <select name="agent" id="agents">
                    <?php
                        foreach($agents as $agent) { ?>
                            <option value="<?= $agent->ID; ?>" <?php selected($agent->ID, $agentSelected); ?>><?= $agent->display_name; ?></option>
                            <?php
                        }
                    ?>
                </select>
            </div>
            <div class="select">
                <input type="checkbox" id="showAgent" name="showAgent" <?php checked($ad["showAgent"], '1'); ?>>
                <label for="showAgent"><?php _e("Publish the agent contact", "retxtdom");?></label>
                <?php if($userRole === "administrator") { 
                    $createUserURL = admin_url("user-new.php");?>
                <a target="_blank" href="<?=$createUserURL;?>"><?php _e("Add an agent", "retxtdom");?></a>
                <?php } ?>
            </div>
        </div>
        <div id="addPictures">
            <a href="#" id="insertAdPictures" class="button"><?php empty($ad["imagesIds"])?_e("Add pictures", "retxtdom"):_e("Replace pictures", "retxtdom");?></a>
            <input type="hidden" name="images" id="images" value="<?= !is_null($ad["imagesIds"])?implode(';', $ad["imagesIds"]):''; ?>">
        </div>
        <div id="showPictures">
            <?php $images = $ad["imagesIds"];
            if(!is_null($images)) {
                foreach($images as $id) { ?>
                    <div class="aPicture" data-imgId="<?=absint($id);?>">
                        <?= wp_get_attachment_image($id, array(150, 150), false, array("class" => "imgAd")); ?>
                        <div class="controlPicture">
                            <span class="moveToLeft" onclick="movePicture(this, 'left');">←</span>
                            <span class="deletePicture" onclick="deletePicture(this);"><?php _e("Delete", "retxtdom"); ?></span>
                            <span class="moveToRight" onclick="movePicture(this, 'right');">→</span>
                        </div>
                    </div>
                <?php }
            }?>
        </div>

        <?php 
            if(isset($ad["customMainFields"]) && is_array($ad["customMainFields"])) {
                echo '<div id="customMFields">';
                foreach($ad["customMainFields"] as $kField => $vField) { ?>
                    <div class="text">
                        <label><?=$kField;?></label>
                        <input type="text" name="CF<?=$vField["nameAttr"];?>" value="<?=$vField["value"];?>">
                    </div>
                    <?php 
                }
                echo "</div>";                         
            }
    }
      
    public function additionalFeaturesMetaBox() {
        $ad = self::$ad;
        ?>
        <div id="floors">
            <div class="text">
                <label><?php _e("Floor", "retxtdom");?></label>
                <input type="number" name="floor" id="floorInput" placeholder="<?php _e("Floor", "retxtdom");?>" value="<?= $ad["floor"]; ?>">
            </div>
            <div class="text">
                <label><?php _e("Number of floors", "retxtdom");?></label>
                <input type="number" name="nbFloors" id="nbFloorsInput" placeholder="<?php _e("Number of floors", "retxtdom");?>" value="<?= $ad["nbFloors"]; ?>">
            </div>
        </div>
        <div id="kitchenHeater">
            <div class="select">
            <label><?php _e("Type of heating", "retxtdom");?></label>&nbsp;
            <select name="typeHeating">
                <option value="Unknown" <?php selected($ad["typeHeating"], "Unknown"); ?>>
                    <?php _e("Do not fill", "retxtdom");?>
                </option>
                <option value="Individual gas" <?php selected($ad["typeHeating"], "Individual gas"); ?>>
                    <?php _e("Invidual gas", "retxtdom");?>
                </option>
                <option value="Collective gas" <?php selected($ad["typeHeating"], "CollectiveGas"); ?>>
                    <?php _e("Collective gas", "retxtdom");?>
                </option>
                <option value="Individual fuel" <?php selected($ad["typeHeating"], "Individual fuel"); ?>>
                    <?php _e("Individual fuel", "retxtdom");?>
                </option>
                <option value="Collective fuel" <?php selected($ad["typeHeating"], "Collective fuel"); ?>>
                    <?php _e("Collective fuel", "retxtdom");?>
                </option>
                <option value="Individual electric" <?php selected($ad["typeHeating"], "Individual electric"); ?>>
                    <?php _e("Individual electric", "retxtdom");?>
                </option>
                <option value="Collective electric" <?php selected($ad["typeHeating"], "Collective electric"); ?>>
                    <?php _e("Collective electric", "retxtdom");?>
                </option>
            </select>
            </div>
            <div class="select">
                <label><?php _e("Type of kitchen", "retxtdom");?></label>&nbsp;
                <select name="typeKitchen">
                    <option value="unknown" <?php selected($ad["typeKitchen"], "unknown"); ?>>
                        <?php _e("Do not fill", "retxtdom");?>
                    </option>
                    <option value="Not equipped" <?php selected($ad["typeKitchen"], "Not equipped"); ?>><?php _e("Not equipped", "retxtdom");?></option>
                    <option value="Kitchenette" <?php selected($ad["typeKitchen"], "Kitchenette"); ?>><?php _e("Kitchenette", "retxtdom");?></option>
                    <option value="Standard" <?php selected($ad["typeKitchen"], "Standard"); ?>><?php _e("Standard", "retxtdom");?></option>
                    <option value="Industrial" <?php selected($ad["typeKitchen"], "Industrial"); ?>><?php _e("Industrial", "retxtdom");?></option>
                </select>
            </div>
        </div>
        <div id="balconies">
            <div class="text">
                <label><?php _e("Number of balconies", "retxtdom");?></label>
                <input type="number" name="nbBalconies" id="nbBalconiesInput" placeholder="<?php _e("Number of balconies", "retxtdom");?>" value="<?= $ad["nbBalconies"]; ?>">
            </div>
        </div>
        <div id="propertyHas">
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="elevatorInput" name="elevator"<?php checked($ad["elevator"]); ?>>
                    <span class="slider"></span>
                </label> 
                <label for="elevator"><?php _e("Elevator", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="basementInput" name="basement" <?php checked($ad["basement"]); ?>>
                    <span class="slider"></span>
                </label> 
                <label for="basement"><?php _e("Basement", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="outdoorSpaceInput" name="outdoorSpace" <?php checked($ad["outdoorSpace"]); ?>>
                    <span class="slider"></span>
                </label>   
                <label for="outdoorSpace"><?php _e("Outdoor space", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="furnishedInput" name="furnished" <?php checked($ad["furnished"]); ?>>
                    <span class="slider"></span>
                </label>   
                <label for="furnished"><?php _e("Furnished", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="garageInput" name="garage" <?php checked($ad["garage"]); ?>>
                    <span class="slider"></span>
                </label>   
                <label for="garage"><?php _e("Garage", "retxtdom");?></label>
            </div>
            <div class="checkbox">
                <label class="switch">
                    <input type="checkbox" id="parkingInput" name="parking" <?php checked($ad["parking"]); ?>>
                    <span class="slider"></span>
                </label>   
                <label for="parking"><?php _e("Parking", "retxtdom");?></label>
            </div>
        </div>
        <div id="year">
            <div class="text">
                <label><?php _e("Construction year", "retxtdom");?></label>
                <input type="number" name="year" id="yearInput" placeholder="<?php _e("Construction year", "retxtdom");?>" value="<?= $ad["year"]; ?>">
            </div>
        </div>
        <div id="diag">
            <div class="text">
                <label><?php _e("EPD in kWhPE/m²/year", "retxtdom");?></label>
                <input type="number" id="DPE" name="DPE" min="0" max="500" value="<?= $ad["DPE"]; ?>">
            </div>
            <div class="text">
                <label><?php _e("Greenhouse gas in kg eqCO2/m²/year", "retxtdom");?></label>
                <input type="number" id="GES" name="GES" min="0" max="100" value="<?= $ad["GES"]; ?>">
            </div>
        </div>

        <?php 
            if(isset($ad["customAdditionalFields"]) && is_array($ad["customAdditionalFields"])) {
                echo '<div id="customCFields">';
                foreach($ad["customAdditionalFields"] as $kField => $vField) { ?>
                    <div class="text">
                        <label><?=$kField;?></label>
                        <input type="text" name="CF<?=$vField["nameAttr"];?>" value="<?=$vField["value"];?>">
                    </div>
                    <?php 
                }
                echo "</div>";                         
            }

    }
    
    public function submissionMetabox() { 
        $ad = self::$ad; 
        ?>
        <input type="checkbox" id="allowSubmission" name="allowSubmission" <?php checked($ad["allowSubmission"]); ?>><label for="allowSubmission"><?php _e("Allow file submission", "reptxtdom"); ?></label><br />
        <input type="checkbox" id="needGuarantors" name="needGuarantors" <?php checked($ad["needGuarantors"]); disabled(!$ad["allowSubmission"]); ?>><label for="needGuarantors"><?php _e("Need guarantors", "reptxtdom"); ?></label>
    <?php }
    
}
?>