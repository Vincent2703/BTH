<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Create or edit agency
 * 
 */
class REALM_EditAgency {
    public function addMetaBox() {
        add_meta_box( 
            "agencyMetaBox", //ID HTML
            __("Agency's coordinates", "retxtdom"), //Display
            array($this, "displayAgencyMetaBox"), //Callback
            "agency", //Custom type
            "normal", //Location on the page
            "high" //Priority
        );
    }
    
    public function savePost($agencyId, $agency) {
        if($agency->post_type == "agency") {
            if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE || (!isset($_POST["nonceSecurity"]) || (isset($_POST["nonceSecurity"]) && !wp_verify_nonce($_POST["nonceSecurity"], "formEditAgency")))) { //Don't save if it's an autosave or if the nonce is inexistant/incorrect
                return;
            }else if(isset($_POST["nonceSecurity"]) && wp_verify_nonce($_POST["nonceSecurity"], "formEditAgency")) {
                require_once(PLUGIN_RE_PATH."models/admin/AgencyAdmin.php");
                remove_action("save_post_agency", array($this, "savePost")); //Avoid infinite loop
                REALM_AgencyAdmin::setData($agencyId); //Save in BDD
            }
        }    
    }
        
    public function displayAgencyMetaBox($agency) {
        require_once(PLUGIN_RE_PATH."models/admin/AgencyAdmin.php");
        REALM_AgencyAdmin::getData($agency->ID); //Get values
        wp_nonce_field("formEditAgency", "nonceSecurity");
        ?>
            <div id="agencyDetails">
                <div class="text">
                    <label for="phone"><?php _e("Phone", "retxtdom"); ?></label>
                    <input type="text" name="phone" id="phone" placeholder="<?php _e("0100000000", "retxtdom"); ?>" value="<?= REALM_AgencyAdmin::$phone; ?>">
                </div>
                <div class="text">
                    <label for="email"><?php _e("Email address", "retxtdom"); ?></label>
                    <input type="email" name="email" id="email" placeholder="<?php _e("address@email.com", "retxtdom"); ?>" value="<?= REALM_AgencyAdmin::$email; ?>">
                </div>
                <div class="text">
                    <label for="addressInput"><?php _e("Postal address", "retxtdom"); ?></label>
                    <input type="text" name="address" id="addressInput" autocomplete="off" placeholder="<?php _e("123 Chester Square, London", "Postal address example", "retxtdom"); ?>" value="<?= REALM_AgencyAdmin::$address; ?>">
                </div>
            </div>
        <?php
    }
    
}
