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
            if(isset($_POST["phone"]) && $_POST["phone"] !== '') {
                update_post_meta($agencyId, "agencyPhone", sanitize_text_field($_POST["phone"]));
            }
            if(isset($_POST["email"]) && $_POST["email"] !== '') {
                update_post_meta($agencyId, "agencyEmail", sanitize_text_field($_POST["email"]));
            }
            if(isset($_POST["address"]) && $_POST["address"] !== '') {
                update_post_meta($agencyId, "agencyAddress", sanitize_text_field($_POST["address"])); //No need to check with the API
            }         
        }    
    }
        
    public function displayAgencyMetaBox($agency) {
        $phone = esc_html(get_post_meta($agency->ID, "agencyPhone", true));
        $email = esc_html(get_post_meta($agency->ID, "agencyEmail", true));
        $address = esc_html(get_post_meta($agency->ID, "agencyAddress", true));
        ?>
            <div id="agencyDetails">
                <div class="text">
                    <label for="phone"><?php _e("Phone", "retxtdom"); ?></label>
                    <input type="text" name="phone" id="phone" placeholder="<?php _e("0100000000", "retxtdom"); ?>" value="<?= $phone; ?>">
                </div>
                <div class="text">
                    <label for="email"><?php _e("Email address", "retxtdom"); ?></label>
                    <input type="email" name="email" id="email" placeholder="<?php _e("address@email.com", "retxtdom"); ?>" value="<?= $email; ?>">
                </div>
                <div class="text">
                    <label for="addressInput"><?php _e("Postal address", "retxtdom"); ?></label>
                    <input type="text" name="address" id="addressInput" autocomplete="off" placeholder="<?php _e("123 Chester Square, London", "Postal address example", "retxtdom"); ?>" value="<?= $address; ?>">
                </div>
            </div>
        <?php
    }
    
}
