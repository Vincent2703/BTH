<?php

/*
 * 
 * Managing plugin's options
 * 
 */
class Options {
    
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
                        $option = "OptionsGeneral"; //Default tab
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
                if(isset($_GET["tab"])) { //If in a tab
                    $tab = $_GET["tab"]; //We set it
                }else{
                    $tab = "general"; //By default
                }
            }else{ //Edit-tags
                $tab = "general"; //Set the default tab (it will appear as selected)
            }
            $pageOptions = PLUGIN_RE_NAME."options"; ?>
            <h2><?= PLUGIN_RE_NAME; ?></h2>
            <p>Interface de configuration - <?= PLUGIN_RE_NAME; ?></p>
            <h2 class="nav-tab-wrapper">
                <a href="edit.php?post_type=re-ad&page=<?=$pageOptions;?>&tab=general" class="nav-tab <?= $tab === "general" ? "nav-tab-active" : ''; ?>"><?php _e("General", "retxtdom"); ?></a>
                <a href="edit.php?post_type=re-ad&page=<?=$pageOptions;?>&tab=imports" class="nav-tab <?= $tab === "imports" ? "nav-tab-active" : ''; ?>"><?php _e("Imports", "retxtdom"); ?></a>
                <a href="edit.php?post_type=re-ad&page=<?=$pageOptions;?>&tab=exports" class="nav-tab <?= $tab === "exports" ? "nav-tab-active" : ''; ?>"><?php _e("Exports", "retxtdom"); ?></a>
                <a href="edit.php?post_type=re-ad&page=<?=$pageOptions;?>&tab=email" class="nav-tab <?= $tab === "email" ? "nav-tab-active" : ''; ?>"><?php _e("Email", "retxtdom"); ?></a>
                <a href="edit.php?post_type=re-ad&page=<?=$pageOptions;?>&tab=fees" class="nav-tab <?= $tab === "fees" ? "nav-tab-active" : ''; ?>"><?php _e("Fees schedule", "retxtdom"); ?></a>
                <a href="edit.php?post_type=re-ad&page=<?=$pageOptions;?>&tab=apis" class="nav-tab <?= $tab === "apis" ? "nav-tab-active" : ''; ?>">APIs</a>    
                <?php if(isset($this->optionsImports["templateUsedImport"]) && $this->optionsImports["templateUsedImport"] == "seloger" || isset($this->optionsImports["templateUsedExport"]) && $this->optionsExports["templateUsedExport"] == "seloger") { ?>
                    <a href="edit.php?post_type=re-ad&page=<?=$pageOptions;?>&tab=seloger" class="nav-tab <?= $tab === "seloger" ? "nav-tab-active" : ''; ?>"><?php _e("Template", "retxtdom"); ?> SeLoger</a>  
                <?php } ?>
            </h2>
        <?php   
        }
    }

    /*
     * Add settings for each "categories", a corresponding section and corresponding fields
     */
    public function optionsPageInit() {
        //Get options
        $this->optionsGeneral = get_option(PLUGIN_RE_NAME."OptionsGeneral");
        $this->optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");
        $this->optionsExports = get_option(PLUGIN_RE_NAME."OptionsExports");
        $this->optionsEmail = get_option(PLUGIN_RE_NAME."OptionsEmail");
        $this->optionsFees = get_option(PLUGIN_RE_NAME."OptionsFees");
        $this->optionsApis = get_option(PLUGIN_RE_NAME."OptionsApis");
        //$this->optionsSeLoger = get_option(PLUGIN_RE_NAME."OptionsSeloger");
        
        
        //Regitster settings
        register_setting( //Register a setting for general options
            PLUGIN_RE_NAME."OptionsGeneralGroup", //option group
            PLUGIN_RE_NAME."OptionsGeneral", //option name
            array($this, "sanitizationGeneralFields") //Sanitization callback
        );
        
        register_setting( //Register a setting for importation options
            PLUGIN_RE_NAME."OptionsImportsGroup", //option group
            PLUGIN_RE_NAME."OptionsImports", //option name
            array($this, "sanitizationImportFields") //Sanitization callback
        );
        
        register_setting( //Register a setting for exportation options
            PLUGIN_RE_NAME."OptionsExportsGroup", //option group
            PLUGIN_RE_NAME."OptionsExports", //option name
            array($this, "sanitizationExportFields") //Sanitization callback
        );
                       
        register_setting( //Register a setting for mail options
            PLUGIN_RE_NAME."OptionsEmailGroup", //option group
            PLUGIN_RE_NAME."OptionsEmail", //option name
            array($this, "sanitizationEmailFields") //Sanitization callback
        );
        
        register_setting( //Register a setting for the fee schedule
            PLUGIN_RE_NAME."OptionsFeesGroup", //option group
            PLUGIN_RE_NAME."OptionsFees", //option name
            array($this, "sanitizationFeesScheduleFields") //Sanitization callback
        );

        /*register_setting( //Register a setting for SeLoger template
            PLUGIN_RE_NAME."OptionsSeLogerGroup", //option group
            PLUGIN_RE_NAME."OptionsSeloger", //option name
            array($this, "sanitizationSeLogerFields") //Sanitization callback
        );*/
        
        
        //Add sections
        add_settings_section( //Section for general setting
            PLUGIN_RE_NAME."optionsSection", //id
            __("Ads", "retxtdom"), //title
            array($this, "generalSettingPreForm"), //callback - display info before 
            PLUGIN_RE_NAME."OptionsGeneralPage" //page
        );
        
        add_settings_section( //Section for importation setting
            PLUGIN_RE_NAME."optionsSection", //id
            __("Imports", "retxtdom"), //title
            null, //callback
            PLUGIN_RE_NAME."OptionsImportsPage" //page
        );
        
        add_settings_section( //Section for exportation setting
            PLUGIN_RE_NAME."optionsSection", //id
            __("Exports", "retxtdom"), //title
            null, //callback
            PLUGIN_RE_NAME."OptionsExportsPage" //page
        );
                
        add_settings_section( //Section for mail setting
            PLUGIN_RE_NAME."optionsSection", //id
            __("Email", "retxtdom"), //title
            null, //callback
            PLUGIN_RE_NAME."OptionsEmailPage" //page
        );
        
        add_settings_section( //Section for fees schedule setting
            PLUGIN_RE_NAME."optionsSection", //id
            __("Fees", "retxtdom"), //title
            null, //callback
            PLUGIN_RE_NAME."OptionsFeesPage" //page
        );
        
        add_settings_section( //Section for APIs setting
            PLUGIN_RE_NAME."optionsSection", //id
            "APIs", //title
            null, //callback
            PLUGIN_RE_NAME."OptionsApisPage" //page
        );
        
        /*add_settings_section( //Section for SeLoger template setting
            PLUGIN_RE_NAME."optionsSection", //id
            "SeLoger", //title
            null,
            PLUGIN_RE_NAME."OptionsSelogerPage" //page
        );*/
        
        
        //Add fields
        
        /* General options */
        add_settings_field(
            "currency", //id
            __("Currency", "retxtdom").SELF::fieldPurpose("Currency's symbol."), //title
            array($this, "currencyCallback"), //callback
            PLUGIN_RE_NAME."OptionsGeneralPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "customFields", //id
            __("Customs fields", "retxtdom").SELF::fieldPurpose("Allow to add custom fields for the ads."), //title
            array($this, "customFieldsCallback"), //callback
            PLUGIN_RE_NAME."OptionsGeneralPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        /* Importation options */
     
        /*add_settings_field(
            "templateUsedImport", //id
            __("Import template", "retxtdom").SELF::fieldPurpose("Template to use for imports."),
            array($this, "templateUsedImportCallback"), //callback
            PLUGIN_RE_NAME."OptionsImportsPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );*/
             
        add_settings_field(
            "maxSavesImports", //id
            __("Backups number", "retxtdom").SELF::fieldPurpose("Number of copies of files containing imported ads to keep."),
            array($this, "maxSavesImportsCallback"), //callback
            PLUGIN_RE_NAME."OptionsImportsPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "maxDim", //id
            __("Pictures size", "retxtdom").SELF::fieldPurpose("Maximum size of imported pictures."),
            array($this, "maxDimCallback"), //callback
            PLUGIN_RE_NAME."OptionsImportsPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "qualityPictures", //id
            __("Pictures quality", "retxtdom").SELF::fieldPurpose("The higher the value, the more the quality is faithful to the original, at the expense of the weight of the image."),
            array($this, "qualityPicturesCallback"), //callback
            PLUGIN_RE_NAME."OptionsImportsPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        /*add_settings_field(
            "allowAutoImport", //id
            __("Automatic ads import", "retxtdom").SELF::fieldPurpose("When this option is activated, a cronjob can be run to import the ads from the most recent file located in the plugin's import directory."),
            array($this, "allowAutoImportCallback"), //callback
            PLUGIN_RE_NAME."OptionsImportsPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );*/
        

        /* Exportation options */
        
        /*add_settings_field(
            "templateUsedExport", //id
            __("Export template", "retxtdom").SELF::fieldPurpose("Template to use for exports."),
            array($this, "templateUsedExportCallback"), //callback
            PLUGIN_RE_NAME."OptionsExportsPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );*/
        
        add_settings_field(
            "maxSavesExports", //id
            __("Backups number", "retxtdom").SELF::fieldPurpose("Number of copies of files containing exported ads to keep."),
            array($this, "maxSavesExportsCallback"), //callback
            PLUGIN_RE_NAME."OptionsExportsPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        /*add_settings_field(
            "allowAutoExport", //id
            __("Automatic ads export", "retxtdom").SELF::fieldPurpose("When this option is activated, a cronjob can be run to export the ads that feature an available property to the plugin's export directory."),
            array($this, "allowAutoExportCallback"), //callback
            PLUGIN_RE_NAME."OptionsExportsPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );*/
       
        /* Mail options */

        /*add_settings_field(
            "sendMail", //id
            __("Send email in case of error", "retxtdom").SELF::fieldPurpose("An email will be sent to the following email address if the plugin detects an error during an export or import."),
            array($this, "sendMailCallback"), //callback
            PLUGIN_RE_NAME."OptionsEmailPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );

        add_settings_field(
            "emailError", //id
            __("Email address to contact in case of error", "retxtdom"), //title
            array($this, "emailErrorCallback"), //callback
            PLUGIN_RE_NAME."OptionsEmailPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );*/
        
        add_settings_field(
            "emailAd", //id
            __("Email address to contact by default for ads", "retxtdom").SELF::fieldPurpose("Email address to contact if it is not possible to contact an agent or agency about an ad."),
            array($this, "emailAdCallback"), //callback
            PLUGIN_RE_NAME."OptionsEmailPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        /* Fees schedule options */
        
        add_settings_field(
            "feesUrl", //id
            __("URL address to the fees schedule", "retxtdom").SELF::fieldPurpose("URL to the file presenting the fees schedule. It will be displayed on each ad. You can also directly upload the file with the button below."), //title
            array($this, "feesUrlCallback"), //callback
            PLUGIN_RE_NAME."OptionsFeesPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "feesFile", //id
            __("File with the fees schedule", "retxtdom"), //title
            array($this, "feesFileCallback"), //callback
            PLUGIN_RE_NAME."OptionsFeesPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
       
        
        /* APIs options */
        
        add_settings_field(
            "apiUsed", //id
            __("API to use", "retxtdom").SELF::fieldPurpose("API to use to retrieve address data."), //title
            array($this, "apiUsedCallback"), //callback
            PLUGIN_RE_NAME."OptionsApisPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "apiKeyGoogle", //id
            __("Google API key", "retxtdom"), //title,
            array($this, "apiKeyGoogleCallback"), //callback
            PLUGIN_RE_NAME."OptionsApisPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "apiLimitNbRequests",
            __("Limit number of requests per day and user", "retxtdom").SELF::fieldPurpose("Limiting the number of requests makes abuse less likely."),
            array($this, "apiLimitNbRequestsCallback"),
            PLUGIN_RE_NAME."OptionsApisPage",
            PLUGIN_RE_NAME."optionsSection"
        );
        
        add_settings_field(
            "apiMaxNbRequests",
            __("The maximum number of requests made by a user in a day", "retxtdom"),
            array($this, "apiMaxNbRequestsCallback"),
            PLUGIN_RE_NAME."OptionsApisPage",
            PLUGIN_RE_NAME."optionsSection"
        );
        
        add_settings_field(
            "apiLimitCountry", //id
            __("Limit search to one country", "retxtdom"), //title
            array($this, "apiLimitCountryCallback"), //callback
            PLUGIN_RE_NAME."OptionsApisPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "apiAdminAreaLvl1", //id
            __("Display addresses with the administration area level 1", "retxtdom").SELF::fieldPurpose("Generally state or prefecture."), //title
            array($this, "apiAdminAreaLvl1Callback"), //callback
            PLUGIN_RE_NAME."OptionsApisPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "apiAdminAreaLvl2", //id
            __("Display addresses with the administration area level 2", "retxtdom").SELF::fieldPurpose("Generally countries or districts."), //title
            array($this, "apiAdminAreaLvl2Callback"), //callback
            PLUGIN_RE_NAME."OptionsApisPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        /* SeLoger template options */
        
        /*add_settings_field(
            "versionSeLoger", //id
            __("Version", "retxtdom")." SeLoger".SELF::fieldPurpose("Version and revision of the template used"), //title
            array($this, "versionSeLogerCallback"), //callback
            PLUGIN_RE_NAME."OptionsSelogerPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );
        
        add_settings_field(
            "idAgency", //id
            __("Agency ID ", "retxtdom").SELF::fieldPurpose("ID for using the template"), //title    
            array($this, "idAgencyCallback"), //callback
            PLUGIN_RE_NAME."OptionsSelogerPage", //page
            PLUGIN_RE_NAME."optionsSection" //section
        );*/
        
    }
    
    //Sanitization callbacks
    public function sanitizationGeneralFields($input) {
        $sanitaryValues = array();
        
        if(isset($input["currency"]) && !ctype_space($input["currency"])) {
            $sanitaryValues["currency"] = sanitize_text_field($input["currency"]);
        }
        
        if(isset($input["customFields"]) && $input["customFields"][0] === '[' && $input["customFields"][-1] === ']') {
           $sanitaryValues["customFields"] = sanitize_text_field($input["customFields"]);
        }   
        
        return $sanitaryValues;
    }

    public function sanitizationImportFields($input) {
        $sanitaryValues = array();
        
        /*if(isset($input["templateUsedImport"]) && !ctype_space($input["templateUsedImport"])) {
            $sanitaryValues["templateUsedImport"] = sanitize_text_field($input["templateUsedImport"]);
        }*/
                
        if(isset($input["maxSavesImports"]) && is_numeric($input["maxSavesImports"])) {
            $sanitaryValues["maxSavesImports"] = absint($input["maxSavesImports"]);
        }

        if(isset($input["maxDim"]) && is_numeric($input["maxDim"])) {
            $sanitaryValues["maxDim"] = absint($input["maxDim"]);
        }
        
        if(isset($input["qualityPictures"]) && is_numeric($input["qualityPictures"])) {
            $sanitaryValues["qualityPictures"] = absint($input["qualityPictures"]);
        }
        
        //$sanitaryValues["allowAutoImport"] = isset($input["allowAutoImport"]);

        
        return $sanitaryValues;
}

    public function sanitizationExportFields($input) {
        $sanitaryValues = array();
        
        /*if(isset($input["templateUsedExport"])) {
            $sanitaryValues["templateUsedExport"] = sanitize_text_field($input["templateUsedExport"]);
        }*/
        
        $sanitaryValues["maxSavesExports"] = absint($input["maxSavesExports"]);
        
        //$sanitaryValues["allowAutoExport"] = isset($input["allowAutoExport"]);

        
        return $sanitaryValues;
    }
    
    
    public function sanitizationEmailFields($input) {
        $sanitaryValues = array();
        
        /*$sanitaryValues["sendMail"] = isset($input["sendMail"]);


        if(isset($input["emailError"]) && is_email($input["emailError"])) {
            $sanitaryValues["emailError"] = sanitize_text_field($input["emailError"]);
        }*/
        
        if(isset($input["emailAd"]) && is_email($input["emailAd"])) {
            $sanitaryValues["emailAd"] = sanitize_text_field($input["emailAd"]);
        }
        
        return $sanitaryValues;
    }
    
    public function sanitizationFeesScheduleFields($input) {
        $sanitaryValues = array();
        $inputFile = $_FILES[PLUGIN_RE_NAME."OptionsFees"];
        
        foreach($inputFile as $key => $value) {
            $inputFile[$key] = $value["feesFile"]; 
        }
        
        if(isset($input["feesUrl"]) && !ctype_space($input["feesUrl"])) {
            $sanitaryValues["feesUrl"] = sanitize_url($input["feesUrl"], array("https", "http"));
        }
                
        if(isset($inputFile)) {
            $upload = wp_handle_upload($inputFile, array("test_form" => false));
            if(isset($upload["url"]) && !empty($upload["url"])) {
                $sanitaryValues["feesUrl"] = $upload["url"];
            }
        }
        
        return $sanitaryValues;
    }
    
    public function sanitizationApisFields($input) {
        $sanitaryValues = array();
        
        if(isset($input["apiUsed"]) && in_array($input["apiUsed"], array("govFr", "google"))) {
            $sanitaryValues["apiUsed"] = $input["apiUsed"];
        }
        
        if(isset($input["apiKeyGoogle"]) && !ctype_space($input["apiKeyGoogle"])) {
            $sanitaryValues["apiKeyGoogle"] = sanitize_text_field($input["apiKeyGoogle"]);
        }
        
        $sanitaryValues["apiLimitNbRequests"] = isset($input["apiLimitNbRequests"]);
        
        if(isset($input["apiMaxNbRequests"]) && is_numeric($input["apiMaxNbRequests"])) {
             $sanitaryValues["apiMaxNbRequests"] = intval($input["apiMaxNbRequests"]);
        }
        
        if(isset($input["apiLimitCountry"]) && !ctype_space($input["apiLimitCountry"])) {
            $sanitaryValues["apiLimitCountry"] = sanitize_text_field($input["apiLimitCountry"]);
        }
        
        $sanitaryValues["apiAdminAreaLvl1"] = isset($input["apiAdminAreaLvl1"]);

        
        $sanitaryValues["apiAdminAreaLvl2"] = isset($input["apiAdminAreaLvl2"]);

        
        return $sanitaryValues;
    }
    
    /*public function sanitizationSeLogerFields($input) {
        $sanitaryValues = array();
        
        if(isset($input["versionSeLoger"]) && !ctype_space($input["versionSeLoger"])) {
            $sanitaryValues["versionSeLoger"] = sanitize_text_field($input["versionSeLoger"]);
        }
        
        if(isset($input["idAgency"]) && !ctype_space($input["idAgency"])) {
            $sanitaryValues["idAgency"] = sanitize_text_field($input["idAgency"]);
        }
        
        return $sanitaryValues;
    }*/
    
    /*
     * Display link to edit-tags before the form in General setting
     */
    public function generalSettingPreForm() { ?>
        <p>
            <a target="_blank" href="edit-tags.php?taxonomy=adTypeProperty&post_type=re-ad"><?php _e("Click here to update the property types"); ?></a><br />
            <br />
            <a target="_blank" href="edit-tags.php?taxonomy=adTypeAd&post_type=re-ad"><?php _e("Click here to update the ad types"); ?></a><br />
        </p>
    <?php }

    
    //Fields
    
    /* General setting */
    public function currencyCallback() { 
        isset($this->optionsGeneral["currency"]) ? absint($this->optionsGeneral["currency"]) : '1'; ?>
            <input type="text" id="currency" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsGeneral[currency]";?>" 
               placeholder='€' 
               value="<?=isset($this->optionsGeneral["currency"]) ? esc_attr($this->optionsGeneral["currency"]) : '$';?>">
    <?php }
    
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
                        <td class="fieldName"><input type="text" oninput="removeDemo();" placeholder="Ex : Orientation"></td>
                        <td class="section"><select><option id="mainFeatures"><?php _e("Main features", "retxtdom"); ?></option><option id="additionalFeatures"><?php _e("Additional features", "retxtdom"); ?></option></select></td>
                        <td>
                            <span class="dashicons-before dashicons-arrow-up-alt fieldUp" onclick="moveRow(this, 'up');"></span>
                            <span class="dashicons-before dashicons-arrow-down-alt fieldDown" onclick="moveRow(this, 'down');"></span>
                        </td>
                        <td>
                            <span class="dashicons-before dashicons-trash fieldTrash" onclick="deleteRow(this);"></span>
                        </td>
                    </tr>
                    <?php 
                    if(isset($this->OptionsGeneral["customFields"])) {
                        $customFields = json_decode($this->OptionsGeneral["customFields"], true);
                        foreach($customFields as $field) { ?>
                            <tr>
                                <td class="fieldName"><input type="text" oninput="removeDemo();" value="<?=$field["name"];?>"></td>
                                <td class="section"><select><option id="mainFeatures" <?php selected($field["section"], "mainFeatures"); ?>><?php _e("Main features", "retxtdom"); ?></option><option id="additionalFeatures"  <?php selected($field["section"], "additionalFeatures"); ?>><?php _e("Additional features", "retxtdom"); ?></option></select></td>
                                <td>
                                    <span class="dashicons-before dashicons-arrow-up-alt fieldUp" onclick="moveRow(this, 'up');"></span>
                                    <span class="dashicons-before dashicons-arrow-down-alt fieldDown" onclick="moveRow(this, 'down');"></span>
                                </td>
                                <td>
                                    <span class="dashicons-before dashicons-trash fieldTrash" onclick="deleteRow(this);"></span>
                                </td>
                            </tr>
                    <?php               
                        } 
                    }?>
                </tbody>
            </table>
            <br />
            <span class="dashicons-before dashicons-plus fieldPlus"></span>
            <input type="hidden" name="<?=PLUGIN_RE_NAME."OptionsGeneral[customFields]";?>" id="customFieldsData">
    <?php }

    
    /* Importation setting */   
    /*public function templateUsedImportCallback() {
        ?> <select name="<?=PLUGIN_RE_NAME."OptionsImports[templateUsedImport]";?>" id="templateUsedImport">
            <option value="stdxml" <?php selected($this->optionsImports["templateUsedImport"], "stdxml"); ?>>XML</option>
            <option value="seloger" <?php selected($this->optionsImports["templateUsedImport"], "seloger"); ?>>Se Loger</option>
        </select> <?php
    }*/

    public function maxSavesImportsCallback() {
        $value = isset($this->optionsImports["maxSavesImports"]) ? absint($this->optionsImports["maxSavesImports"]) : '1'; ?>
        <input type="number" min="1" id="maxSavesImports" class="regular-text" required
                name="<?=PLUGIN_RE_NAME."OptionsImports[maxSavesImports];"?>" 
                value="<?=$value;?>">
        <?php
    }
    
    public function maxDimCallback() {
        ?> <select name="<?=PLUGIN_RE_NAME."OptionsImports[maxDim]";?>" id="maxDim">
            <option value="512" <?php selected($this->optionsImports["maxDim"], "512"); ?>>512px</option>
            <option value="1024" <?php selected($this->optionsImports["maxDim"], "1024"); ?>>1024px</option>
            <option value="1536" <?php selected($this->optionsImports["maxDim"], "1536"); ?>>1536px</option>
            <option value="2048" <?php selected($this->optionsImports["maxDim"], "2048"); ?>>2048px</option>
        </select> <?php
    }
    
    public function qualityPicturesCallback() { 
        $value = isset($this->optionsImports["qualityPictures"]) ? absint($this->optionsImports["qualityPictures"]) : '85'; ?>
        <input type="range" name="<?=PLUGIN_RE_NAME."OptionsImports[qualityPictures]";?>" 
               min="75" max="100" step="1" oninput="this.nextElementSibling.value = this.value" required 
               value="<?=$value?>">
        <output><?=$value;?></output>
    <?php }
    
    /*public function allowAutoImportCallback() { ?>
        <input type="checkbox" 
               name="<?=PLUGIN_RE_NAME."OptionsImports[allowAutoImport]";?>" id="allowAutoImport" 
                   <?php isset($this->optionsImports["allowAutoImport"])?checked($this->optionsImports["allowAutoImport"], true):''?>>&nbsp;
        <label for="allowAutoImport"><?php _e("Yes", "retxtdom"); ?></label>
    <?php }*/
      
    
    /* Exportation setting */
    public function templateUsedExportCallback() {
        ?> <select name="<?=PLUGIN_RE_NAME."OptionsExports[templateUsedExport]";?>" id="templateUsedExprot">
            <option value="stdxml" <?php selected($this->optionsExports["templateUsedExport"], "stdxml"); ?>>XML</option>
            <option value="seloger" <?php selected($this->optionsExports["templateUsedExport"], "seloger"); ?>>Se Loger</option>
        </select> <?php
    }
    
    public function maxSavesExportsCallback() {
        $value = isset($this->optionsExports["maxSavesExports"]) ? absint($this->optionsExports["maxSavesExports"]) : '1'; ?>
        <input type="number" min="1" id="maxSavesExports" class="regular-text" required
                name="<?=PLUGIN_RE_NAME."OptionsExports[maxSavesExports];"?>" 
                value="<?=$value;?>">
    <?php }
   
    public function allowAutoExportCallback() { ?>
        <input type="checkbox" 
               name="<?=PLUGIN_RE_NAME."OptionsExports[allowAutoExport]";?>" id="allowAutoExport" 
                   <?php isset($this->optionsExports["allowAutoExport"])?checked($this->optionsExports["allowAutoExport"], true):''?>>&nbsp;
        <label for="allowAutoExport"><?php _e("Yes", "retxtdom"); ?></label>
    <?php }
    
    
    /* Email setting */
    public function sendMailCallback() { ?>
        <input type="checkbox" 
               name="<?=PLUGIN_RE_NAME."OptionsEmail[sendMail]";?>" id="sendMail" 
                   <?php isset($this->optionsEmail["sendMail"])?checked($this->optionsEmail["sendMail"], true):''?>>&nbsp;
        <label for="sendMail"><?php _e("Yes", "retxtdom"); ?></label>
    <?php }

    public function emailErrorCallback() {      
        $value = isset($this->optionsEmail["emailError"]) ? esc_attr($this->optionsEmail["emailError"]) : '';
        ?>
        <input type="email" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsEmail[emailError]";?>" 
               id="emailError" placeholder="<?php _e("address@email.com", "retxtdom"); ?>" 
               value="<?=$value;?>">
    <?php }
    
    public function emailAdCallback() { ?>
        <input type="email" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsEmail[emailAd]";?>" id="emailAd" placeholder="<?php _e("address@email.com", "retxtdom"); ?>" 
               value="<?=isset($this->optionsEmail["emailAd"]) ? esc_attr($this->optionsEmail["emailAd"]) : '';?>">
    <?php }
       

    /* Fees schedule setting */    
    public function feesUrlCallback() { ?>
        <input type="text" id="feesUrl" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsFees[feesUrl]";?>" 
               placeholder="<?=$_SERVER["HTTP_HOST"].'/'. __("feesSchedule", "retxtdom").".pdf";?>" 
               value="<?=isset($this->optionsFees["feesUrl"]) ? esc_attr($this->optionsFees["feesUrl"]) : '';?>">
    <?php }
    
    public function feesFileCallback() {
        $name = PLUGIN_RE_NAME."OptionsFees[feesFile]";
        echo "<input type='file' name='$name' accept='.pdf, image/*'>";
    }
    
    
    /* APIs setting */
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
        
    public function apiLimitCountryCallback() {
        $value = isset($this->optionsApis["apiLimitCountry"]) ? esc_attr($this->optionsApis["apiLimitCountry"]) : ''; ?>
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
    
    
    /* SeLoger setting */
    /*public function idAgencyCallback() {
        $value = isset($this->optionsSeLoger["idAgency"]) ? esc_attr($this->optionsSeLoger["idAgency"]) : '';
        ?>
            <input type="text" class="regular-text" 
                   name="<?=PLUGIN_RE_NAME."OptionsSeLoger[idAgency]";?>" id="idAgency" placeholder="<?php _e("MyAgency", "retxtdom"); ?>" 
                   value="<?=$value;?>">         
    <?php }
    
    public function versionSeLogerCallback() {
        $value = isset($this->optionsSeLoger["versionSeLoger"]) ? esc_attr($this->optionsSeLoger["versionSeLoger"]) : '';
        ?>
            <input type="text" class="regular-text" 
                   name="<?=PLUGIN_RE_NAME."OptionsSeLoger[versionSeLoger]";?>" id="versionSeLoger" placeholder="4.08-007" 
                   value="<?=$value;?>">         
    <?php }*/
    
    /* Display an explanation about the field's purpose */
    private function fieldPurpose($text) {
        return '&nbsp;<abbr title="'.__($text, "retxtdom").'"><sup>?</sup></abbr>';
    }
    
}
