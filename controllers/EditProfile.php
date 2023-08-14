<?php


class REALM_EditProfile {
    
    public function addProfileCustomFields($userWP) {
        require_once(PLUGIN_RE_PATH."models/UserModel.php");
        
        $role = $userWP->roles[0];        
        //$agencies = REALM_UserModel::getUsersByRole("agency");
        
        $user = REALM_UserModel::getUser($userWP->ID);
        
        if(defined("PLUGIN_REP_NAME") && $role === "customer") { 
            $customerCustomFields = json_decode(get_option(PLUGIN_REP_NAME."Options")["customFields"], true);
            
            $fields = array(
                "buyer" => array(
                    "personalSituation" => array(),
                    "professionalSituation" => array(),
                    "other" => array()
                ),
                "guarantor" =>  array(
                    "personalSituation" => array(),
                    "professionalSituation" => array(),
                    "other" => array()
                )
            );
                   
            foreach($customerCustomFields as $CF) {
                if($CF["forWhom"] === "buyer" || $CF["forWhom"] === "guarantor") {
                    array_push($fields[$CF["forWhom"]][$CF["category"]], $CF);
                }else{
                    array_push($fields["buyer"][$CF["category"]], $CF);
                    array_push($fields["guarantor"][$CF["category"]], $CF);
                }
            }
            
        ?>
            
        <div id="tableCustomProfile">
            <input type="radio" class="tct" name="tct" id="tctAccount">
            <input type="radio" class="tct" name="tct" id="tctBuyer" checked>
            <input type="radio" class="tct" name="tct" id="tctGuarantor">
            <div id="tabs">
                <span class="tabAccount">
                    <label for="tctAccount"><?php _e("Account", "reptxtdom"); ?></label>
                </span>
                <span class="tabBuyer">
                    <label class="selectedTab" for="tctBuyer"><?php _e("Buyer", "reptxtdom"); ?> 1</label>
                </span>
                <span class="tabGuarantor">
                    <label for="tctGuarantor"><?php _e("Guarantor", "reptxtdom"); ?> 1</label>
                </span>
            </div>
            <div id="tabsContent">
                <div class="contentAccount">
                    <fieldset>
                        <legend><?php _e("Account", "reptxtdom"); ?></legend>
                        <!-- profileCallback() -->
                    </fieldset>
                </div>
                <div class="contentBuyer defaultContent">
                    <fieldset>
                        <legend><?php _e("Personal situation", "reptxtdom"); ?></legend>
                        <table class="form-table personalSituation" role="presentation">
                            <tbody>
                                <?php foreach($fields["buyer"]["personalSituation"] as $field) { 
                                    self::printField($field);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend><?php _e("Professional situation", "reptxtdom"); ?></legend>
                        <table class="form-table professionalSituation" role="presentation">
                            <tbody>
                                <?php foreach($fields["buyer"]["professionalSituation"] as $field) {
                                    self::printField($field);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend><?php _e("Other", "reptxtdom"); ?></legend>
                        <table class="form-table other" role="presentation">
                            <tbody>
                                <?php foreach($fields["buyer"]["other"] as $field) {
                                    self::printField($field);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                </div>
                <div class="contentGuarantor">
                    <fieldset>
                        <legend><?php _e("Personal situation", "reptxtdom"); ?></legend>
                        <table class="form-table personalSituation" role="presentation">
                            <tbody>
                                <?php foreach($fields["guarantor"]["personalSituation"] as $field) {
                                    self::printField($field);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend><?php _e("Professional situation", "reptxtdom"); ?></legend>
                        <table class="form-table professionalSituation" role="presentation">
                            <tbody>
                                <?php foreach($fields["guarantor"]["professionalSituation"] as $field) {
                                    self::printField($field);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend><?php _e("Personal situation", "reptxtdom"); ?></legend>
                        <table class="form-table personalSituation" role="presentation">
                            <tbody>
                                <?php foreach($fields["guarantor"]["other"] as $field) {
                                    self::printField($field);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                </div>
                
            </div>
        </div>
        <?php }        
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
    
    private function printField($field) { ?>
        <tr class="customFieldWrap">
            <th scope="row">
                <label for="<?=$field["nameAttr"];?>"><?=$field["name"];?></label>
            </th>
            <td>
                <?php
                    switch($field["type"]) {
                        case "text": 
                            printf('<input type="text" name="%s" value="%s" class="regular-text"%s>', $field["nameAttr"], null, $field["optionnal"]?" required":'');
                            break;
                        case "date":
                            printf('<input type="date" name="%s" value="%s" class="regular-text"%s>', $field["nameAttr"], null, $field["optionnal"]?" required":'');
                            break;
                        case "number":
                            printf('<input type="number" name="%s" min="0" value="%s" class="regular-text"%s>', $field["nameAttr"], null, $field["optionnal"]?" required":'');
                            break;
                        case "file":
                            printf('<input type="file" name="%s" accept="%s" value="%s" class="regular-text"%s>', $field["nameAttr"], $field["extensions"], null, $field["optionnal"]?" required":'');
                            break;
                        case "files":
                            printf('<input type="file" name="%s" accept="%s" value="%s" class="regular-text" multiple%s>', $field["nameAttr"], $field["extensions"], null, $field["optionnal"]?" required":'');
                            break;
                        case "select":
                            printf('<select name="%s"%s>', $field["nameAttr"], $field["optionnal"]?" required":'');
                            foreach($field["selectValues"] as $option) {
                                printf('<option value="%s"%s>%s</option>', str_replace(' ', '', strtolower($option)), null, $option);
                            }
                            ?></select><?php
                            break;
                    }

                    if(isset($field["description"]) && !empty($field["description"])) { ?>
                        <span class="description"></span>
                    <?php }
                ?>
            </td>
        </tr>
    <?php }
}
