<?php


class REALM_EditProfile {
    
    public function addProfileCustomFields($userWP) {
        require_once(PLUGIN_RE_PATH."models/UserModel.php");
        
        $role = $userWP->roles[0];        
        //$agencies = REALM_UserModel::getUsersByRole("agency");
        
        $user = REALM_UserModel::getUser($userWP->ID);
        
        if(defined("PLUGIN_REP_NAME") && $role === "customer") { 
            $customerCustomFieldsOptions = json_decode(get_option(PLUGIN_REP_NAME."Options")["customFields"], true);
            
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
                   
            foreach($customerCustomFieldsOptions as $CF) {
                if($CF["forWhom"] === "applicant" || $CF["forWhom"] === "guarantor") {
                    array_push($fields[$CF["forWhom"]][$CF["category"]], $CF);
                }else{
                    array_push($fields["applicant"][$CF["category"]], $CF);
                    array_push($fields["guarantor"][$CF["category"]], $CF);
                }
            }
           
            $userCF = get_user_meta($userWP->ID, "customerCustomFields", true);
            $nbApplicants = absint(get_user_meta($userWP->ID, "customerNbApplicants", true));
            var_dump($nbApplicants);
            $nbGuarantors = absint(get_user_meta($userWP->ID, "customerNbGuarantors", true));
            var_dump($userCF);
            
        ?>
            
        <div id="tableCustomProfile">
            <div id="tabsControl">
                <input type="radio" class="tct account" name="tct" id="tctAccount">
                <input type="radio" class="tct applicant" name="tct" id="tctApplicant1" checked>
                <input type="radio" class="tct guarantor" name="tct" id="tctGuarantor1">
                <?php
                    for($a=2; $a<=$nbApplicants; $a++) { ?>
                        <input type="radio" class="tct applicant" name="tct" id="tctApplicant<?=$a;?>">
                    <?php }
                    for($g=2; $g<=$nbGuarantors; $g++) { ?>
                        <input type="radio" class="tct guarantor" name="tct" id="tctGuarantor<?=$g;?>">
                    <?php }
                ?>
            </div>
            <div id="tabs">
                <div id="tabsContainer">
                    <span class="tab account" id="tabAccount">
                        <label for="tctAccount"><?php _e("Account", "reptxtdom"); ?></label>
                    </span>
                    <span class="tab applicant" id="tabApplicant1">
                        <label class="selectedTab" for="tctApplicant1"><?php _e("Applicant", "reptxtdom"); ?> 1</label>
                    </span>
                    <?php
                    for($a=2; $a<=$nbApplicants; $a++) { ?>
                         <span class="tab applicant" id="tabApplicant<?=$a;?>">
                            <label for="tctApplicant<?=$a;?>"><?php _e("Applicant", "reptxtdom"); ?> <?=$a;?></label>
                            <span id="deleteApplicant<?=$a;?>" class="deleteTab" onclick="deleteTab(this);"><?php _e("Delete", "reptxtdom"); ?></span>
                        </span>
                    <?php } ?>
                    <span class="tab guarantor" id="tabGuarantor1">
                        <label for="tctGuarantor1"><?php _e("Guarantor", "reptxtdom"); ?> 1</label>
                    </span>
                    <?php
                    for($g=2; $g<=$nbGuarantors; $g++) { ?>
                        <span class="tab guarantor" id="tabGuarantor<?=$g;?>">
                            <label for="tctGuarantor<?=$g;?>"><?php _e("Guarantor", "reptxtdom"); ?> <?=$g;?></label>
                            <span id="deleteApplicant<?=$g;?>" class="deleteTab" onclick="deleteTab(this);"><?php _e("Delete", "reptxtdom"); ?></span>
                        </span>
                    <?php }
                ?>
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
                <?php for($a=0; $a<$nbApplicants; $a++) { ?>
                <div id="contentApplicant<?=$a+1;?>" class="defaultContent" <?=$a>0?'style="display: none;"':'';?>>
                    <fieldset>
                        <legend><?php _e("Personal situation", "reptxtdom"); ?></legend>
                        <table class="form-table personalSituation" role="presentation">
                            <tbody> 
                                <?php foreach($fields["applicant"]["personalSituation"] as $field) { 
                                    $value = isset($userCF["applicants"][$a]["personalSituation"][$field["nameAttr"]])?$userCF["applicants"][$a]["personalSituation"][$field["nameAttr"]]:'';
                                    self::printField($field, $value, in_array($field["type"], array("file", "files"))?"personalSituation_applicant". $a+1 ."_".$field["nameAttr"]:false);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend><?php _e("Professional situation", "reptxtdom"); ?></legend>
                        <table class="form-table professionalSituation" role="presentation">
                            <tbody>
                                <?php foreach($fields["applicant"]["professionalSituation"] as $field) {
                                    $value = isset($userCF["applicants"][$a]["professionalSituation"][$field["nameAttr"]])?$userCF["applicants"][$a]["professionalSituation"][$field["nameAttr"]]:'';
                                    self::printField($field, $value, in_array($field["type"], array("file", "files"))?"professionalSituation_applicant". $a+1 ."_".$field["nameAttr"]:false);
                                    var_dump($value);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                    <fieldset>
                        <legend><?php _e("Other", "reptxtdom"); ?></legend>
                        <table class="form-table other" role="presentation">
                            <tbody>
                                <?php foreach($fields["applicant"]["other"] as $field) {
                                    $value = isset($userCF["applicants"][$a]["other"][$field["nameAttr"]])?$userCF["applicants"][$a]["other"][$field["nameAttr"]]:'';
                                    self::printField($field, $value, in_array($field["type"], array("file", "files"))?"other_applicant". $a+1 ."_".$field["nameAttr"]:false);
                                } ?>
                            </tbody>
                        </table>
                    </fieldset>
                </div>
                <?php }
                for($g=0; $g<$nbGuarantors; $g++) { ?>
                    <div id="contentGuarantor<?=$g+1;?>" <?=$g>0?'style="display: none;"':'';?>>
                        <fieldset>
                            <legend><?php _e("Personal situation", "reptxtdom"); ?></legend>
                            <table class="form-table personalSituation" role="presentation">
                                <tbody>
                                    <?php foreach($fields["guarantor"]["personalSituation"] as $field) {
                                        $value = isset($userCF["guarantors"][$g]["personalSituation"][$field["nameAttr"]])?$userCF["guarantors"][$g]["personalSituation"][$field["nameAttr"]]:'';
                                        self::printField($field, $value, in_array($field["type"], array("file", "files"))?"personalSituation_guarantor". $g+1 ."_".$field["nameAttr"]:false);
                                    } ?>
                                </tbody>
                            </table>
                        </fieldset>
                        <fieldset>
                            <legend><?php _e("Professional situation", "reptxtdom"); ?></legend>
                            <table class="form-table professionalSituation" role="presentation">
                                <tbody>
                                    <?php foreach($fields["guarantor"]["professionalSituation"] as $field) {
                                        $value = isset($userCF["guarantors"][$g]["professionalSituation"][$field["nameAttr"]])?$userCF["guarantors"][$g]["professionalSituation"][$field["nameAttr"]]:'';
                                        self::printField($field, $value, in_array($field["type"], array("file", "files"))?"professionalSituation_guarantor". $g+1 ."_".$field["nameAttr"]:false);
                                    } ?>
                                </tbody>
                            </table>
                        </fieldset>
                        <fieldset>
                            <legend><?php _e("Other", "reptxtdom"); ?></legend>
                            <table class="form-table other" role="presentation">
                                <tbody>
                                    <?php foreach($fields["guarantor"]["other"] as $field) {
                                        $value = isset($userCF["guarantors"][$g]["other"][$field["nameAttr"]])?$userCF["guarantors"][$g]["other"][$field["nameAttr"]]:'';
                                        self::printField($field, $value, in_array($field["type"], array("file", "files"))?"other_guarantor". $g+1 ."_".$field["nameAttr"]:false);
                                    } ?>
                                </tbody>
                            </table>
                        </fieldset>
                    </div>        
                <?php } ?>
            </div>
            <input type="hidden" name="customFieldsJSON" id="customFieldsJSON">
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
    
    private function printField($field, $value, $descriminator=false) { ?>
        <tr class="customFieldWrap">
            <th scope="row">
                <label for="<?=$field["nameAttr"];?>"><?=$field["name"];?></label>
            </th>
            <td>
                <?php
                    switch($field["type"]) {
                        case "text": 
                            printf('<input type="text" value="%s" class="regular-text %s"%s>', $value, $field["nameAttr"], $field["optionnal"]?" required":'');
                            break;
                        case "date":
                            printf('<input type="date" value="%s" class="regular-text %s"%s>', $value, $field["nameAttr"], $field["optionnal"]?" required":'');
                            break;
                        case "number":
                            printf('<input type="number" min="0" value="%s" class="regular-text %s"%s>', $value, $field["nameAttr"], $field["optionnal"]?" required":'');
                            break;
                        case "file":
                            $extensions = implode(", ", array_map(function($ext){ return "." . $ext; }, $field["extensions"]));
                            printf('<input type="file" name="%s" accept="%s" class="regular-text"%s>', $descriminator, $extensions, $field["optionnal"]?" required":'');
                            printf('<span>l %s</span>', $value);
                            break;
                        case "files":
                            $extensions = implode(", ", array_map(function($ext){ return "." . $ext; }, $field["extensions"]));
                            printf('<input type="file" name="%s" accept="%s" class="regular-text" multiple%s>', $descriminator, $extensions, $field["optionnal"]?" required":'');
                            printf('<span>l %s</span>', $value);
                            break;
                        case "select":
                            printf('<select class="select %s"%s>', $field["nameAttr"], $field["optionnal"]?" required":'');
                            foreach($field["selectValues"] as $option) {
                                printf('<option value="%s"%s>%s</option>', str_replace(' ', '', strtolower($option)), $value===str_replace(' ', '', strtolower($option))?"selected":'', $option);
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
