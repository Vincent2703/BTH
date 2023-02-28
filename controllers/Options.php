<?php

class Options {
    //private $options;
    public function showPage() {
        settings_errors();
        ?>
        <div class="wrap" id="REOptions">
            <form method="post" action="options.php" enctype="multipart/form-data">
                <?php
                    if(isset($_GET["tab"])) { //Si on a sélectionné un onglet
                        $option = "Options".ucfirst($_GET["tab"]); //On affiche la page correspondante
                    }else{
                        $option = "OptionsLanguage"; //Page par défaut
                    }
                    settings_fields(PLUGIN_RE_NAME.$option."Group");
                    do_settings_sections(PLUGIN_RE_NAME.$option."Page");
                    submit_button();
                ?>
            </form>
        </div>
    <?php }
    
    public function tabsOption() { 
        $base = get_current_screen()->base;
        if($base === "edit-tags" || $base === "re-ad_page_repoptions") { 
            if($base === "re-ad_page_repoptions") { //Si on est sur la page des options custom
                if(isset($_GET["tab"])) { //S'il y a un $_GET tab
                    $tab = $_GET["tab"]; //On le prend
                }else{
                    $tab = "displayads"; //Sinon par défaut
                }
            }else{ //Sinon on est sur la page edit-tags
                $tab = "displayads";
            }?>
            <h2><?= PLUGIN_RE_NAME; ?></h2>
            <p>Interface de configuration - <?= PLUGIN_RE_NAME; ?></p>
            <h2 class="nav-tab-wrapper">
                <a href="edit.php?post_type=re-ad&page=repoptions&tab=displayads" class="nav-tab <?= $tab === "displayads" ? "nav-tab-active" : ''; ?>"><?php _e("Ads", "retxtdom"); ?></a>
                <a href="edit.php?post_type=re-ad&page=repoptions&tab=imports" class="nav-tab <?= $tab === "imports" ? "nav-tab-active" : ''; ?>"><?php _e("Imports", "retxtdom"); ?></a>
                <a href="edit.php?post_type=re-ad&page=repoptions&tab=exports" class="nav-tab <?= $tab === "exports" ? "nav-tab-active" : ''; ?>"><?php _e("Exports", "retxtdom"); ?></a>
                <a href="edit.php?post_type=re-ad&page=repoptions&tab=email" class="nav-tab <?= $tab === "email" ? "nav-tab-active" : ''; ?>"><?php _e("Email", "retxtdom"); ?></a>
                <a href="edit.php?post_type=re-ad&page=repoptions&tab=fees" class="nav-tab <?= $tab === "fees" ? "nav-tab-active" : ''; ?>"><?php _e("Fees schedule", "retxtdom"); ?></a>
                <a href="edit.php?post_type=re-ad&page=repoptions&tab=apis" class="nav-tab <?= $tab === "apis" ? "nav-tab-active" : ''; ?>">APIs</a>    
                <?php if($this->optionsImports["templateUsedImport"] == "seloger" || $this->optionsExports["templateUsedExport"] == "seloger") { ?>
                    <a href="edit.php?post_type=re-ad&page=repoptions&tab=seloger" class="nav-tab <?= $tab === "seloger" ? "nav-tab-active" : ''; ?>"><?php _e("Template", "retxtdom"); ?> SeLoger</a>  
                <?php } ?>
            </h2>
        <?php   
        }
    }

    public function optionsPageInit() {
        $this->optionsDisplayads = get_option(PLUGIN_RE_NAME."OptionsLanguage"); //TODO : Enlever this ?
        $this->optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");
        $this->optionsExports = get_option(PLUGIN_RE_NAME."OptionsExports");
        $this->optionsAds = get_option(PLUGIN_RE_NAME."OptionsAds");
        $this->optionsEmail = get_option(PLUGIN_RE_NAME."OptionsEmail");
        $this->optionsFees = get_option(PLUGIN_RE_NAME."OptionsFees");
        $this->optionsStyle = get_option(PLUGIN_RE_NAME."OptionsStyle");
        $this->optionsApis = get_option(PLUGIN_RE_NAME."OptionsApis");
        $this->optionsSeLoger = get_option(PLUGIN_RE_NAME."OptionsSeloger");
        
        register_setting( //Enregistrement des options pour l'affichage des annonces
            PLUGIN_RE_NAME."OptionsLanguageGroup", // option_group
            PLUGIN_RE_NAME."OptionsLanguage", // option_name
            array($this, "optionsSanitizeDisplayads") // sanitizeCallback
        );
        
        register_setting( //Enregistrement des options pour les importations
            PLUGIN_RE_NAME."OptionsImportsGroup", // option_group
            PLUGIN_RE_NAME."OptionsImports", // option_name
            array($this, "optionsSanitizeImport") // sanitizeCallback
        );
        
        register_setting( //Enregistrement des options pour les exportations
            PLUGIN_RE_NAME."OptionsExportsGroup", // option_group
            PLUGIN_RE_NAME."OptionsExports", // option_name
            array($this, "optionsSanitizeExport") // sanitizeCallback
        );
                       
        register_setting( //Enregistrement des options mails
            PLUGIN_RE_NAME."OptionsEmailGroup", // option_group
            PLUGIN_RE_NAME."OptionsEmail", // option_name
            array($this, "optionsSanitizeEmail") // sanitizeCallback
        );
        
        register_setting( //Enregistrement des options pour le barème des honoraires
            PLUGIN_RE_NAME."OptionsFeesGroup", // option_group
            PLUGIN_RE_NAME."OptionsFees", // option_name
            array($this, "optionsSanitizeFees") // sanitizeCallback
        );
        
        register_setting( //Enregistrement des options pour le style
            PLUGIN_RE_NAME."OptionsApisGroup", // option_group
            PLUGIN_RE_NAME."OptionsApis", // option_name
            array($this, "optionsSanitizeApis") // sanitizeCallback
        );
        
        register_setting( //Enregistrement des options pour le modèle SeLoger
            PLUGIN_RE_NAME."OptionsSeLogerGroup", // option_group
            PLUGIN_RE_NAME."OptionsSeloger", // option_name
            array($this, "optionsSanitizeSeLoger") // sanitizeCallback
        );
        
        add_settings_section( //Section pour les options de la langue
            PLUGIN_RE_NAME."optionsSection", // id
            __("Ads", "retxtdom"), // title
            array($this, "infoAds"), // callback
            //null,
            PLUGIN_RE_NAME."OptionsLanguagePage" // page
        );
        
        add_settings_section( //Section pour les options d'imports
            PLUGIN_RE_NAME."optionsSection", // id
            __("Imports", "retxtdom"), // title
            //array($this, "infoImports"), // callback
            null,
            PLUGIN_RE_NAME."OptionsImportsPage" // page
        );
        
        add_settings_section( //Section pour les options d'exports
            PLUGIN_RE_NAME."optionsSection", // id
            __("Exports", "retxtdom"), // title
            //array($this, "infoExports"), // callback
            null,
            PLUGIN_RE_NAME."OptionsExportsPage" // page
        );
                
        add_settings_section( //Section pour les options des mails
            PLUGIN_RE_NAME."optionsSection", // id
            __("Email", "retxtdom"), // title
            //array($this, "infoDivers"), // callback
            null,
            PLUGIN_RE_NAME."OptionsEmailPage" // page
        );
        
        add_settings_section( //Section pour les options d'honoraires
            PLUGIN_RE_NAME."optionsSection", // id
            __("Fees", "retxtdom"), // title
            //array($this, "infoDivers"), // callback
            null,
            PLUGIN_RE_NAME."OptionsFeesPage" // page
        );
        
        add_settings_section( //Section pour les options d'APIs
            PLUGIN_RE_NAME."optionsSection", // id
            "APIs", // title
            null,
            PLUGIN_RE_NAME."OptionsApisPage" // page
        );
        
        add_settings_section( //Section pour les options de SeLoger
            PLUGIN_RE_NAME."optionsSection", // id
            "SeLoger", // title
            null,
            PLUGIN_RE_NAME."OptionsSelogerPage" // page
        );
        
        /* Affichage annonces */
        
        add_settings_field(
            "currency", // id
            __("Currency", "retxtdom"), // title
            array($this, "currencyCallback"), // callback
            PLUGIN_RE_NAME."OptionsLanguagePage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "customFields", // id
            __("Customs fields", "retxtdom"), // title
            array($this, "customFieldsCallback"), // callback
            PLUGIN_RE_NAME."OptionsLanguagePage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        /* Imports */
     
        add_settings_field(
            "templateUsedImport", // id
            __("Import template", "retxtdom")." <abbr title=\"".__("Template to use for imports", "retxtdom")."\"><sup>?</sup></abbr>",
            array($this, "templateUsedImportCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
             
        add_settings_field(
            "maxSavesImports", // id
            __("Backups number", "retxtdom")." <abbr title=\"".__("Number of copies of files containing imported ads to keep", "retxtdom")."\"><sup>?</sup></abbr>",
            array($this, "maxSavesImportsCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "maxDim", // id
            __("Pictures size", "retxtdom")." <abbr title=\"".__("Maximum size of imported pictures", "retxtdom")."\"><sup>?</sup></abbr>",
            array($this, "maxDimCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "qualityPictures", // id
            __("Pictures quality", "retxtdom")." <abbr title=\"".__("The higher the value, the more the quality is faithful to the original, at the expense of the weight of the image", "retxtdom")."\"><sup>?</sup></abbr>",
            array($this, "qualityPicturesCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "allowAutoImport", // id
            __("Automatic ads import", "retxtdom")." <abbr title=\""."\"><sup>?</sup></abbr>",
            array($this, "allowAutoImportCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        

        /* Exports */
        
        add_settings_field(
            "templateUsedExport", // id
            __("Export template", "retxtdom")." <abbr title=\"".__("Template to use for exports", "retxtdom")."\"><sup>?</sup></abbr>",
            array($this, "templateUsedExportCallback"), // callback
            PLUGIN_RE_NAME."OptionsExportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "maxSavesExports", // id
            __("Backups number", "retxtdom")." <abbr title=\"".__("Number of copies of files containing exported ads to keep", "retxtdom")."\"><sup>?</sup></abbr>",
            array($this, "maxSavesExportsCallback"), // callback
            PLUGIN_RE_NAME."OptionsExportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "allowAutoExport", // id
            __("Automatic ads export", "retxtdom")." <abbr title=\""."\"><sup>?</sup></abbr>",
            array($this, "allowAutoExportCallback"), // callback
            PLUGIN_RE_NAME."OptionsExportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
       
        /* Mail */

        add_settings_field(
            "sendMail", // id
            __("Send email in case of error", "retxtdom")." <abbr title=\"".__("An email will be sent to the following email address if the plugin detects an error during an export or import", "retxtdom")."\"><sup>?</sup></abbr>",
            array($this, "sendMailCallback"), // callback
            PLUGIN_RE_NAME."OptionsEmailPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );

        add_settings_field(
            "emailError", // id
            __("Email address to contact in case of error", "retxtdom"), // title
            array($this, "emailErrorCallback"), // callback
            PLUGIN_RE_NAME."OptionsEmailPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "emailAd", // id
            __("Email address to contact by default for ads", "retxtdom")." <abbr title=\"".__("Email address to contact if it is not possible to contact an agent or agency about an ad", "retxtdom")."\"><sup>?</sup></abbr>",
            array($this, "emailAdCallback"), // callback
            PLUGIN_RE_NAME."OptionsEmailPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        /* Barème des honoaires*/
        
        add_settings_field(
            "feesUrl", // id
            __("URL address to the fees schedule", "retxtdom"), // title
            array($this, "feesUrlCallback"), // callback
            PLUGIN_RE_NAME."OptionsFeesPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "feesFile", // id
            __("File with the fees schedule", "retxtdom"), // title
            array($this, "feesFileCallback"), // callback
            PLUGIN_RE_NAME."OptionsFeesPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
       
        
        /* APIs */
        
        add_settings_field(
            "apiUsed", // id
            __("API to use", "retxtdom"), // title
            array($this, "apiUsedCallback"), // callback
            PLUGIN_RE_NAME."OptionsApisPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "apiKeyGoogle", // id
            __("Google API key", "retxtdom"), // title, // title
            array($this, "apiKeyGoogleCallback"), // callback
            PLUGIN_RE_NAME."OptionsApisPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "apiLimitNbRequests",
            __("Limit number of requests per day and user", "retxtdom"),
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
            "apiLimitCountry", // id
            __("Limit search to one country", "retxtdom"), // title
            array($this, "apiLimitCountryCallback"), // callback
            PLUGIN_RE_NAME."OptionsApisPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "apiAdminAreaLvl1", // id
            __("Display addresses with the administration area level 1 (generally state or prefecture)", "retxtdom"), // title
            array($this, "apiAdminAreaLvl1Callback"), // callback
            PLUGIN_RE_NAME."OptionsApisPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "apiAdminAreaLvl2", // id
            __("Display addresses with the administration area level 2 (generally countries or districts)", "retxtdom"), // title
            array($this, "apiAdminAreaLvl2Callback"), // callback
            PLUGIN_RE_NAME."OptionsApisPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        /* Modèle SeLoger */
        
        add_settings_field(
            "versionSeLoger", // id
            __("Version", "retxtdom").' SeLoger <abbr title="'.__("Version and revision of the template used", "retxtdom").'"><sup>?</sup></abbr>', // title
            array($this, "versionSeLogerCallback"), // callback
            PLUGIN_RE_NAME."OptionsSelogerPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "idAgency", // id
            __("Agency ID ", "retxtdom").' <abbr title="'.__("ID for using the template", "retxtdom").'"><sup>?</sup></abbr>', // title    
            array($this, "idAgencyCallback"), // callback
            PLUGIN_RE_NAME."OptionsSelogerPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
    }
    
    public function optionsSanitizeDisplayads($input) {
        $sanitaryValues = array();
        
        if(isset($input["currency"]) && !ctype_space($input["currency"])) {
            $sanitaryValues["currency"] = sanitize_text_field($input["currency"]);
        }
        
        if(isset($input["customFields"]) && $input["customFields"][0] === '[' && $input["customFields"][-1] === ']') {
           $sanitaryValues["customFields"] = sanitize_text_field($input["customFields"]);
        }   
        
        return $sanitaryValues;
    }

    public function optionsSanitizeImport($input) {
        $sanitaryValues = array();
        
        if(isset($input["templateUsedImport"]) && !ctype_space($input["templateUsedImport"])) {
            $sanitaryValues["templateUsedImport"] = sanitize_text_field($input["templateUsedImport"]);
        }

        if(isset($input["maxDim"]) && is_numeric($input["maxDim"])) {
            $sanitaryValues["maxDim"] = absint($input["maxDim"]);
        }
        
        if(isset($input["maxSaves"]) && is_numeric($input["maxSaves"])) {
            $sanitaryValues["maxSaves"] = absint($input["maxSaves"]);
        }
        
        if(isset($input["qualityPictures"]) && is_numeric($input["qualityPictures"])) {
            $sanitaryValues["qualityPictures"] = absint($input["qualityPictures"]);
        }
        
        $sanitaryValues["allowAutoImport"] = isset($input["allowAutoImport"]);

        
        return $sanitaryValues;
}

    public function optionsSanitizeExport($input) {
        $sanitaryValues = array();
        
        if(isset($input["templateUsedExport"])) {
            $sanitaryValues["templateUsedExport"] = sanitize_text_field($input["templateUsedExport"]);
        }
        
        $sanitaryValues["allowAutoExport"] = isset($input["allowAutoExport"]);

        
        return $sanitaryValues;
    }
    
    
    public function optionsSanitizeEmail($input) {
        $sanitaryValues = array();
        
        $sanitaryValues["sendMail"] = isset($input["sendMail"]);


        if(isset($input["emailError"]) && is_email($input["emailError"])) {
            $sanitaryValues["emailError"] = sanitize_text_field($input["emailError"]);
        }
        
        if(isset($input["emailAd"]) && is_email($input["emailAd"])) {
            $sanitaryValues["emailAd"] = sanitize_text_field($input["emailAd"]);
        }
        
        return $sanitaryValues;
    }
    
    public function optionsSanitizeFees($input) {
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
    
    public function optionsSanitizeApis($input) {
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
    
    public function optionsSanitizeSeLoger($input) {
        $sanitaryValues = array();
        
        if(isset($input["versionSeLoger"]) && !ctype_space($input["versionSeLoger"])) {
            $sanitaryValues["versionSeLoger"] = sanitize_text_field($input["versionSeLoger"]);
        }
        
        if(isset($input["idAgency"]) && !ctype_space($input["idAgency"])) {
            $sanitaryValues["idAgency"] = sanitize_text_field($input["idAgency"]);
        }
        
        return $sanitaryValues;
    }
    
    
    public function infoAds() { ?>
        <p>
            <a target="_blank" href="edit-tags.php?taxonomy=adTypeProperty&post_type=re-ad"><?php _e("Click here to update the property types"); ?></a><br />
            <br />
            <a target="_blank" href="edit-tags.php?taxonomy=adTypeAd&post_type=re-ad"><?php _e("Click here to update the ad types"); ?></a><br />
        </p>
    <?php }
    
    /* AFFICHAGE ANNONCES */
    
    public function currencyCallback() { 
        isset($this->optionsDisplayads["currency"]) ? absint($this->optionsDisplayads["currency"]) : '1'; ?>
            <input type="text" id="currency" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsLanguage[currency]";?>" 
               placeholder='€' 
               value="<?=isset($this->optionsDisplayads["currency"]) ? esc_attr($this->optionsDisplayads["currency"]) : '$';?>">
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
                        <td class="section"><select><option id="mainFeatures"><?php _e("Main characteristics", "retxtdom"); ?></option><option id="complementaryFeatures"><?php _e("Complementary characteristics", "retxtdom"); ?></option></select></td>
                        <td>
                            <span class="dashicons-before dashicons-arrow-up-alt fieldUp" onclick="moveRow(this, 'up');"></span>
                            <span class="dashicons-before dashicons-arrow-down-alt fieldDown" onclick="moveRow(this, 'down');"></span>
                        </td>
                        <td>
                            <span class="dashicons-before dashicons-trash fieldTrash" onclick="deleteRow(this);"></span>
                        </td>
                    </tr>
                    <?php 
                    if(isset($this->optionsDisplayads["customFields"])) {
                        $customFields = json_decode($this->optionsDisplayads["customFields"], true);
                        foreach($customFields as $field) { ?>
                            <tr>
                                <td class="fieldName"><input type="text" oninput="removeDemo();" value="<?=$field["name"];?>"></td>
                                <td class="section"><select><option id="mainFeatures" <?php selected($field["section"], "mainFeatures"); ?>><?php _e("Main characteristics", "retxtdom"); ?></option><option id="complementaryFeatures"  <?php selected($field["section"], "complementaryFeatures"); ?>><?php _e("Complementary characteristics", "retxtdom"); ?></option></select></td>
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
            <input type="hidden" name="<?=PLUGIN_RE_NAME."OptionsLanguage[customFields]";?>" id="customFieldsData">
    <?php }

    /* IMPORTS */   
    
    public function templateUsedImportCallback() {
        ?> <select name="<?=PLUGIN_RE_NAME."OptionsImports[templateUsedImport]";?>" id="templateUsedImport">
            <option value="stdxml" <?php selected($this->optionsImports["templateUsedImport"], "stdxml"); ?>>XML</option>
            <option value="seloger" <?php selected($this->optionsImports["templateUsedImport"], "seloger"); ?>>Se Loger</option>
        </select> <?php
    }

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
    
    public function allowAutoImportCallback() { ?>
        <input type="checkbox" 
               name="<?=PLUGIN_RE_NAME."OptionsImports[allowAutoImport]";?>" id="allowAutoImport" 
                   <?php isset($this->optionsImports["allowAutoImport"])?checked($this->optionsImports["allowAutoImport"], true):''?>>&nbsp;
        <label for="allowAutoImport"><?php _e("Yes", "retxtdom"); ?></label>
    <?php }
      
    
    /* EXPORTS */
    
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
    
    /* EMAIL */

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
       

    /* HONORAIRES */    
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
    
    /* APIs */
    
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
    
    
    /* Modèle SeLoger */
    
    public function idAgencyCallback() {
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
    <?php }
    
}
