<?php


class REALMP_EditProfile {
    
    public function addProfileCustomFields($user) {
        require_once(PLUGIN_RE_PATH."models/admin/UserAdmin.php");
        
        $role = $user->roles[0];
        //Customer
        $customFieldsValues = array();

        $options = get_option(PLUGIN_REP_NAME."Options");
        if($options !== false && isset($options["customFields"])) {
            $customFields = $options["customFields"];
            if(!empty($customFields) || $customFields !== "[]") {
                $customFields = json_decode($customFields, true);
                foreach($customFields as $field) {
                    $name = sanitize_text_field($field["name"]);
                    $nameAttr = $field["nameAttr"];
                    $type = sanitize_text_field($field["type"]);
                    $optionnal = boolval($field["optionnal"]);
                    $value = maybe_unserialize(get_user_meta($user->ID, "userCF".$nameAttr, true));

                    if($field["type"] === "text") {                        
                        $customFieldsValues[$name] = array("nameAttr"=>$nameAttr, "type"=>$type, "optionnal"=>$optionnal, "value"=>sanitize_text_field($value));
                    }else if($field["type"] === "file") {
                        $extensions = sanitize_text_field($field["extensions"]);
                        $customFieldsValues[$name] = array("nameAttr"=>$nameAttr, "type"=>$type, "extensions"=>$extensions, "optionnal"=>$optionnal, "file"=>$value);
                    }
                }
            }
        }
        $agencies = REALM_UserAdmin::getUsersByRole("agency");
        
        $customerPhone = sanitize_text_field(get_user_meta($user->ID, "userPhone", true));
        
        //Agent
        $agentPhone = sanitize_text_field(get_user_meta($user->ID, "agentPhone", true));
        $agentMobilePhone = sanitize_text_field(get_user_meta($user->ID, "agentMobilePhone", true));
        $agentAgency = intval(get_user_meta($user->ID, "agentAgency", true));      
        
        //Agency
        $agencyPhone = sanitize_text_field(get_user_meta($user->ID, "agencyPhone", true));
        $agencyAddress = sanitize_text_field(get_user_meta($user->ID, "agencyAddress", true));
        $agencyDescription = wp_kses_post(get_user_meta($user->ID, "agencyDescription", true));
        ?>
            <h3 id="extraInformationTitle"><?= ucfirst($role);?></h3>
            <table class="form-table" id="extraInformation">
                <tr class="form-field customer">
                    <th scope="row">
                        <label for="customerPhone"><?php _e("Phone", "reptxtdom");?></label>
                    </th>
                    <td>
                        <input type="text" name="customerPhone" id="customerPhone" class="regular-text" value="<?=$customerPhone;?>">
                    </td>
                </tr>
                <?php foreach($customFieldsValues as $kField => $vField) { ?>
                <tr class="form-field customer">
                    <th scope="row">
                        <label for="CF<?=$vField["nameAttr"];?>"><?=$kField;?></label>
                    </th>
                    <td>
                    <?php if($vField["type"] === "text") { ?>
                        <input type="text" id="CF<?=$vField["nameAttr"];?>" name="CF<?=$vField["nameAttr"];?>" class="regular-text" value="<?=$vField["value"];?>">
                    <?php }else if($vField["type"] === "file") { ?>
                        <input type="file" name="CF<?=$vField["nameAttr"];?>" accept="<?=$vField["extensions"];?>"<?=$vField["optionnal"]?'':" required";?>>
                        <?php if(isset($vField["file"]) && is_array($vField["file"]) && !empty($vField["file"])) { ?>
                        <a target="_blank" href="<?=$vField["file"]["url"];?>"><?=$vField["file"]["name"];?></a>
                        <?php }
                        }
                    } ?>
                    </td>
                </tr>
                <tr class="form-field agent">
                    <th scope="row">
                        <label for="agentPhone"><?php _e("Phone", "retxtdom");?></label>
                    </th>
                    <td>
                        <input type="text" name="agentPhone" id="agentPhone" class="regular-text" value="<?=$agentPhone;?>">
                    </td>
                </tr>
                <tr class="form-field agent">
                    <th scope="row">
                         <label for="agentMobilePhone"><?php _e("Mobile phone", "retxtdom");?></label>
                    </th>
                    <td>
                        <input type="text" name="agentMobilePhone" id="agentMobilePhone" class="regular-text" value="<?=$agentMobilePhone;?>">
                    </td>
                </tr>
                <tr class="form-field agent">
                    <th scope="row">
                        <label for="agentAgency"><?php _e("Agent's agency");?></label>
                    </th>
                    <td>
                        <select name="agentAgency" id="agencies">
                        <?php
                            foreach($agencies as $agency) {
                                ?>
                                <option value="<?= $agency->ID; ?>" <?php selected($agency->ID, $agentAgency); ?>><?= $agency->display_name; ?></option>
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
                        <input type="text" name="agencyPhone" id="agencyPhone" class="regular-text" value="<?=$agencyPhone;?>">
                    </td>
                </tr>
                <tr class="form-field agency">
                    <th scope="row">
                         <label for="agencyAddress"><?php _e("Address", "retxtdom");?></label>
                    </th>
                    <td>
                        <input type="text" name="agencyAddress" id="agencyAddress" class="regular-text" value="<?=$agencyAddress;?>">
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
    
    
    public function saveProfileCustomFields($idUser) {               
        if(!isset($_POST["_wpnonce"]) || !wp_verify_nonce($_POST["_wpnonce"], "update-user_$idUser")) {
            return;
	}
	
	if(!current_user_can("edit_user", $idUser)) {
            return;
	}else {
            require_once(PLUGIN_RE_PATH."models/admin/UserAdmin.php");
            REALM_UserAdmin::setData($idUser); //Save in BDD         
        }    
    }
}
