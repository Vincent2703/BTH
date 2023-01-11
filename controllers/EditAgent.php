<?php

class editAgent {
    public function addMetaBoxes() {
        add_meta_box( 
            "agentMetaBox", //ID HTML
            "Coordonnées de l'agent", //Display
            array($this, "displayAgentMetaBox"), //Callback
            "agent", //Custom type
            "normal", //Location on the page
            "high" //Priority
        );
        add_meta_box( 
            "agencyMetaBox", //ID HTML
            "Agence", //Display
            array($this, "displayAgencyMetaBox"), //Callback
            "agent", //Custom type
            "side", //Location on the page
            "low" //Priority
        );
    }
    
    function savePost($agentId, $agent) {
        if($agent->post_type == "agent") {
            if(isset($_POST["phone"]) && !ctype_space($_POST["phone"])) {
                update_post_meta($agentId, "agentPhone", sanitize_text_field($_POST["phone"]));
            }
            if(isset($_POST["mobilePhone"]) && !ctype_space($_POST["mobilePhone"])) {
                update_post_meta($agentId, "agentMobilePhone", sanitize_text_field($_POST["mobilePhone"]));
            }
            if(isset($_POST["email"]) && is_email($_POST["email"])) {
                update_post_meta($agentId, "agentEmail", sanitize_text_field($_POST["email"]));
            }
            
            if(isset($_POST["agency"]) && is_int($_POST["agency"])) {
                //wp_update_post(array("ID" => $agentId, "post_parent" => sanitize_text_field($_POST["agency"])));
                //$agent->post_parent = sanitize_text_field($_POST["agency"]);
                remove_action("save_post_agent", array($this, "savePost"));

                wp_update_post( array(
                    "ID" => $agentId,
                    "post_parent" => sanitize_text_field($_POST["agency"])
                ) );
                
            }
            
            
        }       
    }
    
    public function displayAgentMetaBox($agent) {
        $phone = esc_html(get_post_meta($agent->ID, "agentPhone", true));
        $mobilePhone = esc_html(get_post_meta($agent->ID, "agentMobilePhone", true));
        $email = esc_html(get_post_meta($agent->ID, "agentEmail", true));
        ?>
            <div id="agentDetails">
                <div class="text">
                    <label><?php _e("Phone"); ?></label>
                    <input type="text" name="phone" id="phone" placeholder="<?php _e("0100000000", "Home phone", "retxtdom"); ?>" value="<?= $phone; ?>">
                </div>
                <div class="text">
                    <label><?php _e("Mobile phone"); ?></label>
                    <input type="text" name="mobilePhone" id="mobilePhone" placeholder="<?php _e("0600000000", "retxtdom"); ?>" value="<?= $mobilePhone; ?>">
                </div>
                <div class="text">
                    <label><?php _e("Email address"); ?></label>
                    <input type="email" name="email" id="email" placeholder="<?php _e("address@email.com", "retxtdom"); ?>" value="<?= $email; ?>">
                </div>
            </div>
        <?php
    }
    
    public function displayAgencyMetaBox($agent) {
        $allAgencies = get_posts(array("post_type" => "agency"));
        ?>
            <select name="agency" id="agencies" onclick="reloadAgencies();">
                <?php
                    foreach($allAgencies as $agency) {
                        $nameAgency = get_the_title($agency);
                        $idAgency = $agency->ID;
                        ?>
                        <option value="<?= $idAgency; ?>" <?=(isset($agent->post_parent) && $idAgency==$agent->post_parent)?"selected":NULL;?>><?= $nameAgency; ?></option>
                        <?php
                    }
                ?>
            </select>
            <a target="_blank" href="post-new.php?post_type=agency"><?php _e("Add an agent", "retxtdom"); ?></a>
        <?php
    }
}
