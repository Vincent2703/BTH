<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Create a new user
 * 
 */
class REALM_RegistrationUser {
    public function addFieldsNewUser() {
        require_once(PLUGIN_RE_PATH."models/UserModel.php");
        
        $agentPhone = isset($_POST["agentPhone"])?sanitize_text_field($_POST["agentPhone"]):'';
        $agentMobilePhone = isset($_POST["agentMobilePhone"])?sanitize_text_field($_POST["agentMobilePhone"]):'';
        
        $agencyPhone = isset($_POST["agencyPhone"])?sanitize_text_field($_POST["agencyPhone"]):'';
        $agencyAddress = isset($_POST["agencyAddress"])?sanitize_text_field($_POST["agencyAddress"]):'';
        $agencyDescription = isset($_POST["agencyDescription"])?wp_kses_post($_POST["agencyDescription"]):'';
        
        $agencies = REALM_UserAdmin::getUsersByRole("agency");
        ?>
        <h3 id="extraInformationTitle"><?php _e("Extra profile information for", "retxtdom");?>&nbsp;<span id="roleName"></span></h3>
        <table class="form-table" id="extraInformation">
            <tr class="form-field agent">
                <th scope="row">
                    <label for="agentPhone"><?php _e("Phone", "retxtdom");?></label>
                </th>
                <td>
                    <input type="text" name="agentPhone" id="agentPhone" class="input" value="<?=$agentPhone;?>">
                </td>
            </tr>
            <tr class="form-field agent">
                <th scope="row">
                     <label for="agentMobilePhone"><?php _e("Mobile phone", "retxtdom");?></label>
                </th>
                <td>
                    <input type="text" name="agentMobilePhone" id="agentMobilePhone" class="input" value="<?=$agentMobilePhone;?>">
                </td>
            </tr>
            <tr class="form-field agent">
                <th scope="row">
                    <label for="agentAgency"><?php _e("Agent's agency");?></label>
                </th>
                <td>
                    <select name="agentAgency" id="agencies"">
                    <?php
                        foreach($agencies as $agency) { ?>
                            <option value="<?= $agency->ID; ?>"><?= $agency->display_name; ?></option>
                            <?php
                        }
                    ?>
                    </select>
                </td>
            </tr>
            <tr class="form-field agency">
                <th scope="row">
                    <label for="agencyPhone"><?php _e("Phone", "retxtdom");?></label>
                </th>
                <td>
                    <input type="text" name="agencyPhone" id="agencyPhone" class="input" value="<?=$agencyPhone;?>">
                </td>
            </tr>
            <tr class="form-field agency">
                <th scope="row">
                     <label for="agencyAddress"><?php _e("Address", "retxtdom");?></label>
                </th>
                <td>
                    <input type="text" name="agencyAddress" id="agencyAddress" class="input" value="<?=$agencyAddress;?>">
                </td>
            </tr>
            <tr class="form-field agency">
                <th scope="row">
                     <label for="agencyDescription"><?php _e("Description", "retxtdom");?></label>
                </th>
                <td>
                    <?php
                        wp_editor(
                            $agencyDescription, //Content
                            "agencyDescription", array(
                                "textarea_name" => "agencyDescription",
                                "textarea_rows" => 10,
                            )
                        );
                    ?>
                </td>
            </tr>
        </table>
    <?php
    }
    
    public static function saveCustomFieldsNewUser($idUser) {
        if(!isset($_POST["_wpnonce_create-user"]) || !wp_verify_nonce($_POST["_wpnonce_create-user"], "create-user")) {
            return;
	}
	
	if(!current_user_can("create_users")) {
            return;
	}else {
            require_once(PLUGIN_RE_PATH."models/UserModel.php");
            REALM_UserAdmin::setData($idUser); //Save in BDD         
        }
    }
    
    /*
     * Create an agent or agency post if a new agent or agency user is created
     * The author is the user
     */
    public function createPostOnNewUser($idUser) {
        $user = get_user_by("id", $idUser);
        $role = $user->roles[0];
        if($role === "agent") {
            require_once(PLUGIN_RE_PATH."models/admin/AgentAdmin.php");
            REALM_AgentAdmin::createPost($idUser);
        }else if($role === "agency") {
            require_once(PLUGIN_RE_PATH."models/admin/AgencyAdmin.php");
            REALM_AgencyAdmin::createPost($idUser);
        }
        
    }
    
}
    