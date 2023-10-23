<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Managing plugin's options
 * 
 */
class REALM_Options {
    
    /*
     * Main function - Display the form
     */
    public function showPage() {
        settings_errors();
        ?>
        <div class="wrap" id="REOptions">
            <form method="post" action="options.php" enctype="multipart/form-data">
                <?php
                    if(isset($_GET["tab"])) { //If a tab is selected
                        $option = "Options".ucfirst($_GET["tab"]); //Will display the corresponding tab
                    }else{
                        $option = "OptionsCustomFields"; //Default tab
                    }
                    settings_fields(PLUGIN_RE_NAME.$option."Group");
                    do_settings_sections(PLUGIN_RE_NAME.$option."Page");
                    submit_button();
                ?>
            </form>
        </div>
    <?php }
    
    /*
     * Display the tabs bar
     */
    public function tabsOption() { 
        $base = get_current_screen()->base;
        if($base === "edit-tags" || $base === "re-ad_page_".PLUGIN_RE_NAME."options") { //Either editing the tags or the custom options
            if($base === "re-ad_page_".PLUGIN_RE_NAME."options") { //Custom options
                $tab = isset($_GET["tab"])?$_GET["tab"]:"customFields";
            }else{ //Edit-tags
                $taxonomy = get_current_screen()->taxonomy;
                $tab = $taxonomy==="adTypeProperty"?"propertyTypes":"adTypes";
            }
            $pluginName = strtoupper(PLUGIN_RE_NAME);
            $pageOptions = PLUGIN_RE_NAME."options"; ?>
            <h2><?= $pluginName; ?></h2>
            <p>Interface de configuration - <?= $pluginName; ?></p>
            <h2 class="nav-tab-wrapper">
                <a href="edit.php?post_type=re-ad&page=<?=$pageOptions;?>&tab=customFields" class="nav-tab <?= $tab === "customFields" ? "nav-tab-active" : ''; ?>"><?php _e("Custom fields", "retxtdom"); ?></a>
                <a href="edit-tags.php?taxonomy=adTypeProperty&post_type=re-ad" class="nav-tab <?= $tab === "propertyTypes" ? "nav-tab-active" : ''; ?>"><?php _e("Property types", "retxtdom"); ?></a>
                <a href="edit-tags.php?taxonomy=adTypeAd&post_type=re-ad" class="nav-tab <?= $tab === "adTypes" ? "nav-tab-active" : ''; ?>"><?php _e("Ad types", "retxtdom"); ?></a>
                <a href="edit.php?post_type=re-ad&page=<?=$pageOptions;?>&tab=apis" class="nav-tab <?= $tab === "apis" ? "nav-tab-active" : ''; ?>"><?php _e("APIs", "retxtdom"); ?></a>    
                <a href="edit.php?post_type=re-ad&page=<?=$pageOptions;?>&tab=misc" class="nav-tab <?= $tab === "misc" ? "nav-tab-active" : ''; ?>"><?php _e("Miscellaneous", "retxtdom"); ?></a>
            </h2>
        <?php   
        }
    }

    /*
     * Add settings for each "categories", a corresponding section and corresponding fields
     */
    public function optionsPageInit() {
        //Get options
        $this->optionsCustomFields = get_option(PLUGIN_RE_NAME."OptionsCustomFields");
        $this->optionsApis = get_option(PLUGIN_RE_NAME."OptionsApis");
        $this->optionsMisc = get_option(PLUGIN_RE_NAME."OptionsMisc");        
        
        //Register settings
        register_setting( //Register a setting for custom fields options
            PLUGIN_RE_NAME."OptionsCustomFieldsGroup", //option group
            PLUGIN_RE_NAME."OptionsCustomFields", //option name
            array($this, "sanitizationCustomFields") //Sanitization callback
        );
                               
        register_setting( //Register a setting for the fee schedule
            PLUGIN_RE_NAME."OptionsApisGroup", //option group
            PLUGIN_RE_NAME."OptionsApis", //option name
            array($this, "sanitizationApisFields") //Sanitization callback
        );
        
        register_setting( //Register a setting for the fee schedule
            PLUGIN_RE_NAME."OptionsMiscGroup", //option group
            PLUGIN_RE_NAME."OptionsMisc", //option name
            array($this, "sanitizationMiscFields") //Sanitization callback
        );

        
        
        //Add sections
        add_settings_section( //Section for custom field setting
            PLUGIN_RE_NAME."optionsSection", //id
            __("Manage your custom fields", "retxtdom"), //title
            array($this, "customFieldsSettingPreForm"), //callback - display info before 
            PLUGIN_RE_NAME."OptionsCustomFieldsPage" //page
        );
        
        add_settings_section( //Section for APIs setting
            PLUGIN_RE_NAME."optionsSection", //id
            __("APIs", "retxtdom"), //title
            null, //callback
            PLUGIN_RE_NAME."OptionsApisPage" //page
        );
        
        add_settings_section(
            PLUGIN_RE_NAME."optionsSection", //id
            __("Miscellaneous", "retxtdom"), //title
            null, //callback
            PLUGIN_RE_NAME."OptionsMiscPage" //page
        );
        
        
        //Add fields
        
        $titleFormat = '%s&nbsp;<abbr title="%s"><sup>?</sup></abbr>';
        
        /* Custom fields options */        
        add_settings_field(
            "customFields", //id
            sprintf($titleFormat, __("Custom fields", "retxtdom"), __("Add custom fields to the ads.", "retxtdom")), //title
            array($this, "customFieldsCallback"), //callback
            PLUGIN_RE_NAME."OptionsCustomFieldsPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        /* APIs options */
        
        add_settings_field(
            "apiUsed", //id
            sprintf($titleFormat, __("API to use", "retxtdom"), __("API to use to retrieve address data.", "retxtdom")), //title    
            array($this, "apiUsedCallback"), //callback
            PLUGIN_RE_NAME."OptionsApisPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "apiKeyGoogle", //id
            sprintf($titleFormat, __("Google API key", "retxtdom"), ''), //title    
            array($this, "apiKeyGoogleCallback"), //callback
            PLUGIN_RE_NAME."OptionsApisPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "apiLimitNbRequests",
            sprintf($titleFormat, __("Limit the number of requests per day and user", "retxtdom"), __("Limiting the number of requests makes abuse less likely.", "retxtdom")), //title    
            array($this, "apiLimitNbRequestsCallback"),
            PLUGIN_RE_NAME."OptionsApisPage",
            PLUGIN_RE_NAME."optionsSection"
        );
        
        add_settings_field(
            "apiMaxNbRequests",
            sprintf($titleFormat, __("The maximum number of requests made by a user in a day", "retxtdom"), ''), //title    
            array($this, "apiMaxNbRequestsCallback"),
            PLUGIN_RE_NAME."OptionsApisPage",
            PLUGIN_RE_NAME."optionsSection"
        );
        
        add_settings_field(
            "apiLanguage", //id
            sprintf($titleFormat, __("Display the results in a specific language", "retxtdom"), ''), //title 
            array($this, "apiLanguageCallback"), //callback
            PLUGIN_RE_NAME."OptionsApisPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "apiLimitCountry", //id
            sprintf($titleFormat, __("Limit search to one country", "retxtdom"), ''), //title 
            array($this, "apiLimitCountryCallback"), //callback
            PLUGIN_RE_NAME."OptionsApisPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "apiAdminAreaLvl1", //id
            sprintf($titleFormat, __("Display addresses with the administration area level 1", "retxtdom"), __("Generally a state or a prefecture.", "retxtdom")), //title    
            array($this, "apiAdminAreaLvl1Callback"), //callback
            PLUGIN_RE_NAME."OptionsApisPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "apiAdminAreaLvl2", //id
            sprintf($titleFormat, __("Display addresses with the administration area level 2", "retxtdom"), __("Generally a country or a district.", "retxtdom")), //title                   
            array($this, "apiAdminAreaLvl2Callback"), //callback
            PLUGIN_RE_NAME."OptionsApisPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        /* Miscellaneous options */ 
        
        add_settings_field(
            "currency", //id
            sprintf($titleFormat, __("Currency", "retxtdom"), __("Monetary currency symbol.", "retxtdom")), //title
            array($this, "currencyCallback"), //callback
            PLUGIN_RE_NAME."OptionsMiscPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "areaUnit", //id
            sprintf($titleFormat, __("Area Unit", "retxtdom"), __("Unit used to define an area.", "retxtdom")), //title
            array($this, "areaUnitCallback"), //callback
            PLUGIN_RE_NAME."OptionsMiscPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "similarAdsSameCity", //id
            sprintf($titleFormat, __("Refine similar ads by same city", "retxtdom"), __("At the bottom of each ad page, show only similar ads located in the same city.", "retxtdom")), //title
            array($this, "similarAdsSameCityCallback"), //callback
            PLUGIN_RE_NAME."OptionsMiscPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "searchBarHook", //id
            sprintf($titleFormat, __("Add the search bar when the hook fires", "retxtdom"), __("When this hook fires, add the search bar to the page at that time.", "retxtdom")), //title
            array($this, "searchBarHookCallback"), //callback
            PLUGIN_RE_NAME."OptionsMiscPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "feesUrl", //id
            sprintf($titleFormat, __("URL address to the fees schedule", "retxtdom"), __("URL to the file presenting the fees schedule. It will be displayed on each ad. You can also directly upload the file with the button below.", "retxtdom")), //title    
            array($this, "feesUrlCallback"), //callback
            PLUGIN_RE_NAME."OptionsMiscPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "feesFile", //id
            sprintf($titleFormat, __("File with the fees schedule", "retxtdom"), ''), //title    
            array($this, "feesFileCallback"), //callback
            PLUGIN_RE_NAME."OptionsMiscPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "deleteOptions", //id
            sprintf($titleFormat, __("Delete the options when deactivating the plugin", "retxtdom"), ''), //title    
            array($this, "deleteOptionsCallback"), //callback
            PLUGIN_RE_NAME."OptionsMiscPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
    }
    
    //Sanitization callbacks
    public function sanitizationCustomFields($input) {
        $sanitaryValues = array();
        
        if(isset($input["customFields"]) && !empty($input["customFields"]) && $input["customFields"][0] === '[' && $input["customFields"][-1] === ']' && (json_decode($input["customFields"]) !== null || json_last_error() === JSON_ERROR_NONE)) { //Waiting for PHP 8.3 json_validate()
            $sanitaryValues["customFields"] = sanitize_text_field($input["customFields"]);
        } 
        
        return $sanitaryValues;
    }
    
    public function sanitizationApisFields($input) {
        $sanitaryValues = array();
        
        if(isset($input["apiUsed"]) && in_array($input["apiUsed"], array("govFr", "google"))) {
            $sanitaryValues["apiUsed"] = $input["apiUsed"];
        }
        
        if(isset($input["apiKeyGoogle"]) && !empty(trim($input["apiKeyGoogle"]))) {
            $sanitaryValues["apiKeyGoogle"] = sanitize_text_field($input["apiKeyGoogle"]);
        }
        
        $sanitaryValues["apiLimitNbRequests"] = isset($input["apiLimitNbRequests"]);
        
        if(isset($input["apiMaxNbRequests"]) && is_numeric($input["apiMaxNbRequests"])) {
             $sanitaryValues["apiMaxNbRequests"] = intval($input["apiMaxNbRequests"]);
        }
        
        if(isset($input["apiLanguage"]) && !empty(trim($input["apiLanguage"]))) {
            $sanitaryValues["apiLanguage"] = sanitize_text_field($input["apiLanguage"]);
        }
        
        if(isset($input["apiLimitCountry"]) && !empty(trim($input["apiLimitCountry"]))) {
            $sanitaryValues["apiLimitCountry"] = sanitize_text_field($input["apiLimitCountry"]);
        }
        
        $sanitaryValues["apiAdminAreaLvl1"] = isset($input["apiAdminAreaLvl1"]);

        
        $sanitaryValues["apiAdminAreaLvl2"] = isset($input["apiAdminAreaLvl2"]);

        
        return $sanitaryValues;
    }
    
    public function sanitizationMiscFields($input) {
        $inputFile = $_FILES[PLUGIN_RE_NAME."OptionsMisc"];
        
        foreach($inputFile as $key => $value) {
            $inputFile[$key] = $value["feesFile"]; 
        }
        
        if(isset($input["feesUrl"]) && !empty(trim($input["feesUrl"]))) {
            $sanitaryValues["feesUrl"] = sanitize_url($input["feesUrl"], array("https", "http"));
        }
                
        if(isset($inputFile)) {
            $validMimeTypes = array(
                "pdf"   => "application/pdf",
                "jpg"   => "image/jpeg",
                "jpeg"  => "image/jpeg",
                "png"   => "image/png",
                "bmp"   => "image/bmp",
            );
            $upload = wp_handle_upload($inputFile, array("test_form" => false, "test_type" => true, "mimes"=>$validMimeTypes));
            if(!isset($upload["error"]) && isset($upload["url"]) && !empty($upload["url"])) {
                $sanitaryValues["feesUrl"] = $upload["url"];
            }
        }
        
        if(isset($input["currency"]) && !empty(trim($input["currency"]))) {
            $sanitaryValues["currency"] = sanitize_text_field($input["currency"]);
        }

        if(isset($input["areaUnit"]) && !empty(trim($input["areaUnit"]))) {
            $sanitaryValues["areaUnit"] = sanitize_text_field($input["areaUnit"]);
        }
        
        $sanitaryValues["similarAdsSameCity"] = isset($input["similarAdsSameCity"]);
        
        if(isset($input["searchBarHook"])) {
            $sanitaryValues["searchBarHook"] = sanitize_text_field($input["searchBarHook"]);
        }
        
        $sanitaryValues["deleteOptions"] = isset($input["deleteOptions"]);
        
        return $sanitaryValues;
    }
    
    /*
     * Display link to edit-tags before the form in custom fields setting
     */
    public function customFieldsSettingPreForm() { ?>
        <p>
            Hello
        </p>
    <?php }

    
    //Fields
    
    /* Custom fields setting */
    public function customFieldsCallback() { ?>
        <table id="customFields">
            <thead>
                <tr>
                    <th id="fieldName"><?php _e("Field name", "retxtdom"); ?></th>
                    <th id="section"><?php _e("Section", "retxtdom"); ?></th>
                    <th id="arrows">
                        <span class="dashicons-before dashicons-arrow-up-alt"></span>
                        <span class="dashicons-before dashicons-arrow-down-alt"></span>
                    </th>
                    <th id="trash">
                        <span class="dashicons-before dashicons-trash"></span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="demo">
                    <td class="fieldName"><input type="text" placeholder="<?php _e("Eg : Orientation", "retxtdom"); ?>"></td>
                    <td class="section"><select><option id="mainFeatures"><?php _e("Main features", "retxtdom"); ?></option><option id="additionalFeatures"><?php _e("Additional features", "retxtdom"); ?></option></select></td>
                    <td>
                        <span class="dashicons-before dashicons-arrow-up-alt fieldUp"></span>
                        <span class="dashicons-before dashicons-arrow-down-alt fieldDown"></span>
                    </td>
                    <td>
                        <span class="dashicons-before dashicons-trash fieldTrash"></span>
                    </td>
                </tr>
                <?php 
                if(isset($this->optionsCustomFields["customFields"])) {
                    $customFields = json_decode($this->optionsCustomFields["customFields"], true);
                    foreach($customFields as $field) { ?>
                        <tr>
                            <td class="fieldName"><input type="text" value="<?=$field["name"];?>"></td>
                            <td class="section"><select><option id="mainFeatures" <?php selected($field["section"], "mainFeatures"); ?>><?php _e("Main features", "retxtdom"); ?></option><option id="additionalFeatures"  <?php selected($field["section"], "additionalFeatures"); ?>><?php _e("Additional features", "retxtdom"); ?></option></select></td>
                            <td>
                                <span class="dashicons-before dashicons-arrow-up-alt fieldUp"></span>
                                <span class="dashicons-before dashicons-arrow-down-alt fieldDown"></span>
                            </td>
                            <td>
                                <span class="dashicons-before dashicons-trash fieldTrash"></span>
                            </td>
                        </tr>
                <?php               
                    } 
                }?>
            </tbody>
        </table>
        <br />
        <span class="dashicons-before dashicons-plus fieldPlus"></span>
        <input type="hidden" name="<?=PLUGIN_RE_NAME."OptionsCustomFields[customFields]";?>" id="customFieldsData">
    <?php }
    
    
    /* APIs settings */
    public function apiUsedCallback() {         
        $name = PLUGIN_RE_NAME."OptionsApis[apiUsed]"; ?>
            <input type="radio" name="<?=$name;?>" id="govFr" value="govFr" <?php isset($this->optionsApis["apiUsed"])?checked($this->optionsApis["apiUsed"], "govFr"):'';?>><label for="govFr">adresse.data.gouv.fr API&nbsp;</label><br />
            <input type="radio" name="<?=$name;?>" value="google" id="google" <?php isset($this->optionsApis["apiUsed"])?checked($this->optionsApis["apiUsed"], "google"):'';?>><label for="google">Google API&nbsp;</label>
        <?php
    }
    
    public function apiKeyGoogleCallback() {
        $value = isset($this->optionsApis["apiKeyGoogle"]) ? esc_attr($this->optionsApis["apiKeyGoogle"]) : ''; ?>
            <input type="text" class="regular-text" 
                   name="<?=PLUGIN_RE_NAME."OptionsApis[apiKeyGoogle]";?>" id="apiKeyGoogle" placeholder="123" 
                   value="<?=$value;?>">
    <?php }
    
    public function apiLimitNbRequestsCallback() { ?>
        <input type="checkbox" 
               name="<?=PLUGIN_RE_NAME."OptionsApis[apiLimitNbRequests]";?>" id="apiLimitNbRequests" 
                   <?php checked(isset($this->optionsApis["apiLimitNbRequests"]) && $this->optionsApis["apiLimitNbRequests"]); ?>>&nbsp;
        <label for="apiAdminAreaLvl1"><?php _e("Yes", "retxtdom"); ?></label>
    <?php }
    
    public function apiMaxNbRequestsCallback() {
        $value = isset($this->optionsApis["apiMaxNbRequests"]) ? absint($this->optionsApis["apiMaxNbRequests"]) : "300"; ?>
        <input type="number" min="1" id="apiMaxNbRequests" class="regular-text" required
                name="<?=PLUGIN_RE_NAME."OptionsApis[apiMaxNbRequests];"?>" 
                value="<?=$value;?>">
    <?php }
    
    public function apiLanguageCallback() { ?>
        <p><i><a target="_blank" href="https://developers.google.com/maps/faq?hl=en#languagesupport"><?php _e("See the list of supported languages", "retxtdom"); ?></a></i></p>
        <?php $value = isset($this->optionsApis["apiLanguage"]) ? esc_attr($this->optionsApis["apiLanguage"]) : ''; ?>
            <input type="text" class="regular-text" 
                   name="<?=PLUGIN_RE_NAME."OptionsApis[apiLanguage]";?>" id="apiLanguage" placeholder="fr" 
                   value="<?=$value;?>">
    <?php }
        
    public function apiLimitCountryCallback() { ?>
        <p><i><a target="_blank" href="https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes"><?php _e("See the list of ISO 3166-1 Alpha-2 codes for compatible countries", "retxtdom"); ?></a></i></p>
        <?php $value = isset($this->optionsApis["apiLimitCountry"]) ? esc_attr($this->optionsApis["apiLimitCountry"]) : ''; ?>
            <input type="text" class="regular-text" 
                   name="<?=PLUGIN_RE_NAME."OptionsApis[apiLimitCountry]";?>" id="apiLimitCountry" placeholder="fr" 
                   value="<?=$value;?>">
    <?php }
    
    public function apiAdminAreaLvl1Callback() { ?>
        <input type="checkbox" 
               name="<?=PLUGIN_RE_NAME."OptionsApis[apiAdminAreaLvl1]";?>" id="apiAdminAreaLvl1" 
                   <?php checked(isset($this->optionsApis["apiAdminAreaLvl1"]) && $this->optionsApis["apiAdminAreaLvl1"]); ?>>&nbsp;
        <label for="apiAdminAreaLvl1"><?php _e("Yes", "retxtdom"); ?></label>
    <?php }
    
    public function apiAdminAreaLvl2Callback() { ?>
        <input type="checkbox" 
               name="<?=PLUGIN_RE_NAME."OptionsApis[apiAdminAreaLvl2]";?>" id="apiAdminAreaLvl2" 
                   <?php checked(isset($this->optionsApis["apiAdminAreaLvl2"]) && $this->optionsApis["apiAdminAreaLvl2"]); ?>>&nbsp;
        <label for="apiAdminAreaLvl1"><?php _e("Yes", "retxtdom"); ?></label>
    <?php }
    
    
    /* Misc settings */
    public function currencyCallback() { ?>
            <input type="text" id="currency" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsMisc[currency]";?>" 
               placeholder='€' 
               value="<?=isset($this->optionsMisc["currency"]) ? esc_attr($this->optionsMisc["currency"]) : '$';?>">
    <?php }
    
    public function areaUnitCallback() { ?>
            <input type="text" id="areaUnit" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsMisc[areaUnit]";?>" 
               placeholder='m²' 
               value="<?=isset($this->optionsMisc["areaUnit"]) ? esc_attr($this->optionsMisc["areaUnit"]) : 'm²';?>">
    <?php }
    
    public function similarAdsSameCityCallback() { ?>
        <input type="checkbox" 
               name="<?=PLUGIN_RE_NAME."OptionsMisc[similarAdsSameCity]";?>" id="similarAdsSameCity" 
                   <?php isset($this->optionsMisc["similarAdsSameCity"])?checked($this->optionsMisc["similarAdsSameCity"], true):''?>>&nbsp;
        <label for="similarAdsSameCity"><?php _e("Yes", "retxtdom"); ?></label>
    <?php }
    
    public function searchBarHookCallback() { ?>
            <input type="text" id="searchBarHook" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsMisc[searchBarHook]";?>" 
               value="<?=isset($this->optionsMisc["searchBarHook"]) ? esc_attr($this->optionsMisc["searchBarHook"]) : '';?>">
            <p><i><?php _e('If this field is empty, the search bar will be added using Javascript. You can also <a target="_blank" href="https://developer.wordpress.org/plugins/hooks/custom-hooks">create your own hook</a> and add it to your theme, preferably at the end of the &lt;header&gt; tag.', "retxtdom"); ?></i></p>
    <?php }
    
    public function feesUrlCallback() { ?>
        <input type="text" id="feesUrl" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsMisc[feesUrl]";?>" 
               placeholder="<?=get_site_url().'/'. __("feesSchedule", "retxtdom").".pdf";?>" 
               value="<?=isset($this->optionsMisc["feesUrl"]) ? esc_url($this->optionsMisc["feesUrl"]) : '';?>">
    <?php }
    
    public function feesFileCallback() {
        $name = PLUGIN_RE_NAME."OptionsMisc[feesFile]";
        echo "<input type='file' name='$name' accept='.pdf, .jpg, .jpeg, .png, .bmp'>";
    }
    
    public function deleteOptionsCallback() { ?>
        <input type="checkbox" 
               name="<?=PLUGIN_RE_NAME."OptionsMisc[deleteOptions]";?>" id="deleteOptions" 
                   <?php isset($this->optionsMisc["deleteOptions"])?checked($this->optionsMisc["deleteOptions"], true):''?>>&nbsp;
        <label for="deleteOptions"><?php _e("Yes", "retxtdom"); ?></label>
    <?php }
    
}
