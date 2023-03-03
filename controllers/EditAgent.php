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
    
    public function savePost($agentId, $agent) {
        if($agent->post_type == "agent") {
            if(isset($_POST["nonceSecurity"]) || wp_verify_nonce($_POST["nonceSecurity"], "formEditAgent")) {
                if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
                    return;
                }
                require_once(PLUGIN_RE_PATH."models/admin/AgentAdmin.php");
                remove_action("save_post_agent", array($this, "savePost"));
                AgentAdmin::setData($agentId);
            }
        }       
    }
    
    public function displayAgentMetaBox($agent) {
        require_once(PLUGIN_RE_PATH."models/admin/AgentAdmin.php");
        AgentAdmin::getData($agent->ID);
        wp_nonce_field("formEditAgent", "nonceSecurity");
        ?>
            <div id="agentDetails">
                <div class="text">
                    <label><?php _e("Phone"); ?></label>
                    <input type="text" name="phone" id="phone" placeholder="<?php _e("0100000000", "Home phone", "retxtdom"); ?>" value="<?= AgentAdmin::$phone; ?>">
                </div>
                <div class="text">
                    <label><?php _e("Mobile phone"); ?></label>
                    <input type="text" name="mobilePhone" id="mobilePhone" placeholder="<?php _e("0600000000", "retxtdom"); ?>" value="<?= AgentAdmin::$mobilePhone; ?>">
                </div>
                <div class="text">
                    <label><?php _e("Email address"); ?></label>
                    <input type="email" name="email" id="email" placeholder="<?php _e("address@email.com", "retxtdom"); ?>" value="<?= AgentAdmin::$email; ?>">
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
            <a target="_blank" href="post-new.php?post_type=agency"><?php _e("Add an agency", "retxtdom"); ?></a>
        <?php
    }
}
