<?php

class editAgency {
    public function addMetaBox() {
        add_meta_box( 
            'agencyMetaBox', //ID HTML
            "Coordonnées de l'agence", //Display
            array($this, 'displayAgencyMetaBox'), //Callback
            'agency', //Custom type
            'normal', //Location on the page
            'high' //Priority
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
                update_post_meta($agencyId, "agencyAddress", sanitize_text_field($_POST["address"]));
            }
        }    
    }
        
    public function displayAgencyMetaBox($agency) {
        $phone = esc_html(get_post_meta($agency->ID, "agencyPhone", true));
        $email = esc_html(get_post_meta($agency->ID, "agencyEmail", true));
        $address = esc_html(get_post_meta($agency->ID, "agencyAddress", true));
        ?>
            <input type="text" name="phone" id="phone" placeholder="Téléphone fixe" value="<?= $phone; ?>">
            <input type="email" name="email" id="email" placeholder="Adresse mail" value="<?= $email; ?>">
            <input type="text" name="address" id="addressInput" autocomplete="off" placeholder="Ex : 123 rue de Grenoble 75002 Paris" value="<?= $address; ?>">
        <?php
    }
    
}
