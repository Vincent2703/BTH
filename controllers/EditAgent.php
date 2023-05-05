<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Create or edit agent
 * 
 */
class REALM_EditAgent {
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
            if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE || (!isset($_POST["nonceSecurity"]) || (isset($_POST["nonceSecurity"]) && !wp_verify_nonce($_POST["nonceSecurity"], "formEditAgent")))) { //Don't save if it's an autosave or if the nonce is inexistant/incorrect
                return;
            }else if(isset($_POST["nonceSecurity"]) && wp_verify_nonce($_POST["nonceSecurity"], "formEditAgent")) {
                require_once(PLUGIN_RE_PATH."models/admin/AgentAdmin.php");
                remove_action("save_post_agent", array($this, "savePost")); //Avoid infinite loop
                REALM_AgentAdmin::setData($agentId); //Save in BDD
            }
        }       
    }
    
    public function displayAgentMetaBox($agent) {
        require_once(PLUGIN_RE_PATH."models/admin/AgentAdmin.php");
        REALM_AgentAdmin::getData($agent->ID);
        wp_nonce_field("formEditAgent", "nonceSecurity");
        ?>
            <div id="agentDetails">
                <div class="text">
                    <label><?php _e("Phone"); ?></label>
                    <input type="text" name="phone" id="phone" placeholder="<?php _e("0100000000", "Home phone", "retxtdom"); ?>" value="<?= esc_attr(REALM_AgentAdmin::$phone); ?>">
                </div>
                <div class="text">
                    <label><?php _e("Mobile phone"); ?></label>
                    <input type="text" name="mobilePhone" id="mobilePhone" placeholder="<?php _e("0600000000", "retxtdom"); ?>" value="<?= esc_attr(REALM_AgentAdmin::$mobilePhone); ?>">
                </div>
                <div class="text">
                    <label><?php _e("Email address"); ?></label>
                    <input type="email" name="email" id="email" placeholder="<?php _e("address@email.com", "retxtdom"); ?>" value="<?= esc_attr(REALM_AgentAdmin::$email); ?>">
                </div>
            </div>
        <?php
    }
    
    public function displayAgencyMetaBox($agent) {
        $allAgencies = get_posts(array("post_type" => "agency", "numberposts" => -1));
        ?>
            <select name="agency" id="agencies" onclick="reloadAgencies();">
                <?php
                    foreach($allAgencies as $agency) {
                        $nameAgency = esc_attr(get_the_title($agency));
                        $idAgency = intval($agency->ID);
                        ?>
                        <option value="<?= $idAgency; ?>" <?php selected(isset($agent->post_parent) && $idAgency==$agent->post_parent); ?>><?= $nameAgency; ?></option>
                        <?php
                    }
                ?>
            </select>
            <a target="_blank" href="post-new.php?post_type=agency"><?php _e("Add an agency", "retxtdom"); ?></a>
        <?php
    }
}
