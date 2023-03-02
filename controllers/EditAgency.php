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
    
    function savePost($agencyId, $agency) {
        if($agency->post_type == "agency") {
            if(isset($_POST["phone"]) && !ctype_space($_POST["phone"])) {
                update_post_meta($agencyId, "agencyPhone", sanitize_text_field($_POST["phone"]));
            }
            if(isset($_POST["email"]) && !ctype_space($_POST["email"])) {
                update_post_meta($agencyId, "agencyEmail", sanitize_text_field($_POST["email"]));
            }
            if(isset($_POST["address"]) && !ctype_space($_POST["address"])) {
                update_post_meta($agencyId, "agencyAddress", sanitize_text_field($_POST["address"]));
            }         
        }    
    }
        
    public function displayAgencyMetaBox($agency) {
        require_once(PLUGIN_RE_PATH."models/admin/AgencyAdmin.php");
        AgencyAdmin::getData($agency->ID);
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
