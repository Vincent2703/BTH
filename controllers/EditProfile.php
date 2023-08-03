<?php


class REALM_EditProfile {
    
    public function addProfileCustomFields($userWP) {
        require_once(PLUGIN_RE_PATH."models/UserModel.php");
        
        $role = $userWP->roles[0];        
        $agencies = REALM_UserModel::getUsersByRole("agency");
        
        $user = REALM_UserModel::getUser($userWP->ID);
        ?>
        <h3 id="extraInformationTitle"><span id="roleName"><?= ucfirst($role);?></span></h3>
        <input type="hidden" name="nickname" value="<?=$user["displayName"];?>">
            <table class="form-table" id="extraInformation">
                <tr class="form-field customer">
                    <th scope="row">
                        <label for="customerPhone"><?php _e("Phone", "reptxtdom");?></label>
                    </th>
                    <td>
                        <input type="text" name="customerPhone" id="customerPhone" class="regular-text" value="<?=$user["customerPhone"];?>">
                    </td>
                </tr>
                <?php if($role === "customer") {
                foreach($user["customFields"] as $kField => $vField) { ?>
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
                <?php } ?>
                <tr class="form-field agent">
                    <th scope="row">
                        <label for="agentPhone"><?php _e("Phone", "retxtdom");?></label>
                    </th>
                    <td>
                        <input type="text" name="agentPhone" id="agentPhone" class="regular-text" value="<?=$user["agentPhone"];?>">
                    </td>
                </tr>
                <tr class="form-field agent">
                    <th scope="row">
                         <label for="agentMobilePhone"><?php _e("Mobile phone", "retxtdom");?></label>
                    </th>
                    <td>
                        <input type="text" name="agentMobilePhone" id="agentMobilePhone" class="regular-text" value="<?=$user["agentMobilePhone"];?>">
                    </td>
                </tr>
                <tr class="form-field agent">
                    <th scope="row">
                        <label for="agentAgency"><?php _e("Agency", "retxtdom");?></label>
                    </th>
                    <td>
                        <select name="agentAgency" id="agencies">
                        <?php
                            foreach($agencies as $agency) {
                                ?>
                                <option value="<?= $agency->ID; ?>" <?php selected($agency->ID, $user["agentAgency"]); ?>><?= $agency->display_name; ?></option>
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
                        <input type="text" name="agencyPhone" id="agencyPhone" class="regular-text" value="<?=$user["agencyPhone"];?>">
                    </td>
                </tr>
                <tr class="form-field agency">
                    <th scope="row">
                         <label for="agencyAddress"><?php _e("Address", "retxtdom");?></label>
                    </th>
                    <td>
                        <input type="text" name="agencyAddress" id="agencyAddress" class="regular-text" value="<?=$user["agencyAddress"];?>">
                    </td>
                </tr>
                <tr class="form-field agency">
                    <th scope="row">
                         <label for="agencyDescription"><?php _e("Description", "retxtdom");?></label>
                    </th>
                    <td>
                        <?php
                            wp_editor(
                                $user["agencyDescription"], //Content
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
            require_once(PLUGIN_RE_PATH."models/UserModel.php");
            REALM_UserModel::updateUser($idUser); //Save in BDD         
        }    
    }
}
