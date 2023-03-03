<?php

class editAgency {
    public function addMetaBox() {
        add_meta_box( 
            "agencyMetaBox", //ID HTML
            "Coordonnées de l'agence", //Display
            array($this, "displayAgencyMetaBox"), //Callback
            "agency", //Custom type
            "normal", //Location on the page
            "high" //Priority
        );
    }
    
    public function savePost($agencyId, $agency) {
        if($agency->post_type == "agency") {
            if(isset($_POST["nonceSecurity"]) || wp_verify_nonce($_POST["nonceSecurity"], "formEditAgency")) {
                if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
                    return;
                }
                require_once(PLUGIN_RE_PATH."models/admin/AgencyAdmin.php");
                AgencyAdmin::setData($agencyId);         
            }
        }    
    }
        
    public function displayAgencyMetaBox($agency) {
        require_once(PLUGIN_RE_PATH."models/admin/AgencyAdmin.php");
        AgencyAdmin::getData($agency->ID);
        wp_nonce_field("formEditAgency", "nonceSecurity");
        ?>
            <div id="agencyDetails">
                <div class="text">
                    <label for="phone"><?php _e("Phone", "retxtdom"); ?></label>
                    <input type="text" name="phone" id="phone" placeholder="<?php _e("0100000000", "retxtdom"); ?>" value="<?= AgencyAdmin::$phone; ?>">
                </div>
                <div class="text">
                    <label for="email"><?php _e("Email address", "retxtdom"); ?></label>
                    <input type="email" name="email" id="email" placeholder="<?php _e("address@email.com", "retxtdom"); ?>" value="<?= AgencyAdmin::$email; ?>">
                </div>
                <div class="text">
                    <label for="addressInput"><?php _e("Postal address", "retxtdom"); ?></label>
                    <input type="text" name="address" id="addressInput" autocomplete="off" placeholder="<?php _e("123 Chester Square, London", "Postal address example", "retxtdom"); ?>" value="<?= AgencyAdmin::$address; ?>">
                </div>
            </div>
        <?php
    }
    
}
