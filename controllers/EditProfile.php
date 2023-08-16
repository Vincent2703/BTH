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
                "applicant" => array(
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
                if($CF["forWhom"] === "applicant" || $CF["forWhom"] === "guarantor") {
                    array_push($fields[$CF["forWhom"]][$CF["category"]], $CF);
                }else{
                    array_push($fields["applicant"][$CF["category"]], $CF);
                    array_push($fields["guarantor"][$CF["category"]], $CF);
                }
            }
            
        ?>
            
        <div id="tableCustomProfile">
            <input type="hidden" name="nbApplicants" class="counter applicant" value="1">
            <input type="hidden" name="nbGuarantors" class="counter guarantor" value="1">
            <div id="tabsControl">
                <input type="radio" class="tct account" name="tct" id="tctAccount">
                <input type="radio" class="tct applicant" name="tct" id="tctApplicant1" checked>
                <input type="radio" class="tct guarantor" name="tct" id="tctGuarantor1">
            </div>
            <div id="tabs">
                <div id="tabsContainer">
                    <span class="tab account" id="tabAccount">
                        <label for="tctAccount"><?php _e("Account", "reptxtdom"); ?></label>
                    </span>
                    <span class="tab applicant" id="tabApplicant1">
                        <label class="selectedTab" for="tctApplicant1"><?php _e("Applicant", "reptxtdom"); ?> 1</label>
                    </span>
                    <span class="tab guarantor" id="tabGuarantor1">
                        <label for="tctGuarantor1"><?php _e("Guarantor", "reptxtdom"); ?> 1</label>
                    </span>
                </div>
                <div id="btnActions">
                    <span class="btn applicant">
                        <label><span class="dashicons dashicons-plus-alt"></span>&nbsp;<?php _e("Add an applicant", "reptxtdom"); ?></label>
                    </span>
                    <span class="btn guarantor">
                        <label><span class="dashicons dashicons-plus-alt"></span>&nbsp;<?php _e("Add a guarantor", "reptxtdom"); ?></label>
                    </span>
                </div>
            </div>
            <div id="tabsContent">
                <div id="contentAccount">
                    <fieldset>
                        <legend><?php _e("Account", "reptxtdom"); ?></legend>
                        <!-- profileCallback() -->
                    </fieldset>
                </div>
                <div id="contentApplicant1" class="defaultContent">
                    <fieldset>
                        <legend><?php _e("Personal situation", "reptxtdom"); ?></legend>
                        <table class="form-table personalSituation" role="presentation">
                            <tbody>
                                <?php foreach($fields["applicant"]["personalSituation"] as $field) { 
                                    self::printField($field);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend><?php _e("Professional situation", "reptxtdom"); ?></legend>
                        <table class="form-table professionalSituation" role="presentation">
                            <tbody>
                                <?php foreach($fields["applicant"]["professionalSituation"] as $field) {
                                    self::printField($field);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend><?php _e("Other", "reptxtdom"); ?></legend>
                        <table class="form-table other" role="presentation">
                            <tbody>
                                <?php foreach($fields["applicant"]["other"] as $field) {
                                    self::printField($field);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                </div>
                <div id="contentGuarantor1">
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
                            printf('<input type="file" name="%s" accept="%s" value="%s" class="regular-text"%s>', $field["nameAttr"], implode(';', $field["extensions"]), null, $field["optionnal"]?" required":'');
                            break;
                        case "files":
                            printf('<input type="file" name="%s" accept="%s" value="%s" class="regular-text" multiple%s>', $field["nameAttr"], implode(';', $field["extensions"]), null, $field["optionnal"]?" required":'');
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
