<?php

class Options {
    //private $options;
    public function showPage() {
        settings_errors();
        ?>
        <div class="wrap">
            <form method="post" action="options.php" enctype="multipart/form-data">
                <?php
                    if(isset($_GET["tab"])) { //Si on a sélectionné un onglet
                        $option = "Options".ucfirst($_GET["tab"]); //On affiche la page correspondante
                    }else{
                        $option = "OptionsImports"; //Page par défaut
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
        if($base === "edit-tags" || $base === "re-ad_page_bthoptions") { 
            if($base === "re-ad_page_bthoptions") { //Si on est sur la page des options custom
                if(isset($_GET["tab"])) { //S'il y a un $_GET tab
                    $tab = $_GET["tab"]; //On le prend
                }else{
                    $tab = "imports"; //Sinon par défaut
                }
            }else{ //Sinon on est sur la page edit-tags
                $tab = "tags";
            }?>
            <h2><?= PLUGIN_RE_NAME; ?></h2>
            <p>Interface de configuration - <?= PLUGIN_RE_NAME; ?></p>
            <h2 class="nav-tab-wrapper">
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=language" class="nav-tab <?= $tab === "language" ? "nav-tab-active" : ''; ?>">Langage</a>
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=imports" class="nav-tab <?= $tab === "imports" ? "nav-tab-active" : ''; ?>">Imports</a>
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=exports" class="nav-tab <?= $tab === "exports" ? "nav-tab-active" : ''; ?>">Exports</a>
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=email" class="nav-tab <?= $tab === "email" ? "nav-tab-active" : ''; ?>">Mail</a>
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=fees" class="nav-tab <?= $tab === "fees" ? "nav-tab-active" : ''; ?>">Barème des honoraires</a>
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=apis" class="nav-tab <?= $tab === "apis" ? "nav-tab-active" : ''; ?>">APIs</a>    
                <?php if($this->optionsImports["templateUsedImport"] == "seloger" || $this->optionsExports["templateUsedExport"] == "seloger") { ?>
                    <a href="edit.php?post_type=re-ad&page=bthoptions&tab=seloger" class="nav-tab <?= $tab === "seloger" ? "nav-tab-active" : ''; ?>">Modèle SeLoger</a>  
                <?php } ?>
            </h2>
        <?php   
        }
    }

    public function optionsPageInit() {
        $this->optionsLanguage = get_option(PLUGIN_RE_NAME."OptionsLanguage");
        $this->optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");
        $this->optionsExports = get_option(PLUGIN_RE_NAME."OptionsExports");
        $this->optionsAds = get_option(PLUGIN_RE_NAME."OptionsAds");
        $this->optionsEmail = get_option(PLUGIN_RE_NAME."OptionsEmail");
        $this->optionsFees = get_option(PLUGIN_RE_NAME."OptionsFees");
        $this->optionsStyle = get_option(PLUGIN_RE_NAME."OptionsStyle");
        $this->optionsApis = get_option(PLUGIN_RE_NAME."OptionsApis");
        $this->optionsSeLoger = get_option(PLUGIN_RE_NAME."OptionsSeloger");
        
        register_setting( //Enregistrement des options pour la langue
            PLUGIN_RE_NAME."OptionsLanguageGroup", // option_group
            PLUGIN_RE_NAME."OptionsLanguage", // option_name
            array($this, "optionsSanitizeLanguage") // sanitizeCallback
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
            "Langage", // title
            //array($this, "infoImports"), // callback
            null,
            PLUGIN_RE_NAME."OptionsLanguagePage" // page
        );
        
        add_settings_section( //Section pour les options d'imports
            PLUGIN_RE_NAME."optionsSection", // id
            "Importation", // title
            //array($this, "infoImports"), // callback
            null,
            PLUGIN_RE_NAME."OptionsImportsPage" // page
        );
        
        add_settings_section( //Section pour les options d'exports
            PLUGIN_RE_NAME."optionsSection", // id
            "Exportation", // title
            //array($this, "infoExports"), // callback
            null,
            PLUGIN_RE_NAME."OptionsExportsPage" // page
        );
                
        add_settings_section( //Section pour les options des mails
            PLUGIN_RE_NAME."optionsSection", // id
            "Mail", // title
            //array($this, "infoDivers"), // callback
            null,
            PLUGIN_RE_NAME."OptionsEmailPage" // page
        );
        
        add_settings_section( //Section pour les options d'honoraires
            PLUGIN_RE_NAME."optionsSection", // id
            "Honoraires", // title
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
        
        /* Langage */
        add_settings_field(
            "language", // id
            'Langue', // title
            array($this, "languageCallback"), // callback
            PLUGIN_RE_NAME."OptionsLanguagePage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "currency", // id
            'Devise', // title
            array($this, "currencyCallback"), // callback
            PLUGIN_RE_NAME."OptionsLanguagePage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        /* Imports */
     
        add_settings_field(
            "templateUsedImport", // id
            'Modèle d\'importation <abbr title="Modèle à utiliser pour les importations"><sup>?</sup></abbr>', // title
            array($this, "templateUsedImportCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
             
        add_settings_field(
            "maxSavesImports", // id
            'Nombre de sauvegardes <abbr title="Nombre de copies des fichiers contenant les annonces importées à conserver"><sup>?</sup></abbr>', // title
            array($this, "maxSavesImportsCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "maxDim", // id
            'Taille des images <abbr title="Dimension maximale des images importées"><sup>?</sup></abbr>', // title
            array($this, "maxDimCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "qualityPictures", // id
            'Qualité des images <abbr title="Plus la valeur est elevée, plus la qualité est fidèle à l\'original, au dépend du poids de l\'image"><sup>?</sup></abbr>', // title
            array($this, "qualityPicturesCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "allowAutoImport", // id
            'Autoriser l\'import automatique des annonces <abbr title=""><sup>?</sup></abbr>', // title
            array($this, "allowAutoImportCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        

        /* Exports */
        
        add_settings_field(
            "templateUsedExport", // id
            'Modèle d\'exportation <abbr title="Modèle à utiliser pour les exportations"><sup>?</sup></abbr>', // title
            array($this, "templateUsedExportCallback"), // callback
            PLUGIN_RE_NAME."OptionsExportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "maxSavesExports", // id
            'Nombre de sauvegardes <abbr title="Nombre de copies des fichiers contenant les annonces exportées à conserver"><sup>?</sup></abbr>', // title
            array($this, "maxSavesExportsCallback"), // callback
            PLUGIN_RE_NAME."OptionsExportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "allowAutoExport", // id
            'Autoriser l\'export automatique des annonces <abbr title=""><sup>?</sup></abbr>', // title
            array($this, "allowAutoExportCallback"), // callback
            PLUGIN_RE_NAME."OptionsExportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
       
        /* Mail */

        add_settings_field(
            "sendMail", // id
            'Envoi mail si erreur <abbr title="Un mail sera envoyé à l\'adresse indiquée si le plugin détecte une erreur lors d\'une exportation ou d\'une importation"><sup>?</sup></abbr>', // title
            array($this, "sendMailCallback"), // callback
            PLUGIN_RE_NAME."OptionsEmailPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );

        add_settings_field(
            "emailError", // id
            "Adresse mail à contacter en cas d'erreur", // title
            array($this, "emailErrorCallback"), // callback
            PLUGIN_RE_NAME."OptionsEmailPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "emailAd", // id
            'Adresse mail à contacter pour les annonces par défaut <abbr title="Adresse mail à contacter s\'il n\'est pas possible de contacter un agent ou une agence pour une annonce"><sup>?</sup></abbr>', // title
            array($this, "emailAdCallback"), // callback
            PLUGIN_RE_NAME."OptionsEmailPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        /* Barème des honoaires*/
        
        add_settings_field(
            "feesUrl", // id
            "Adresse URL vers le barème des honoraires", // title
            array($this, "feesUrlCallback"), // callback
            PLUGIN_RE_NAME."OptionsFeesPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "feesFile", // id
            "Fichier avec le barème des honoraires", // title
            array($this, "feesFileCallback"), // callback
            PLUGIN_RE_NAME."OptionsFeesPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
       
        
        /* APIs */
        
        add_settings_field(
            "apiUsed", // id
            "API à utiliser", // title
            array($this, "apiUsedCallback"), // callback
            PLUGIN_RE_NAME."OptionsApisPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "apiKeyGoogle", // id
            "Clé API Google", // title
            array($this, "apiKeyGoogleCallback"), // callback
            PLUGIN_RE_NAME."OptionsApisPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "apiLimitCountry", // id
            "Limiter les recherches à un pays", // title
            array($this, "apiLimitCountryCallback"), // callback
            PLUGIN_RE_NAME."OptionsApisPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        /* Modèle SeLoger */
        
        add_settings_field(
            "versionSeLoger", // id
            'Version SeLoger <abbr title="Version et révision du format SeLoger utilisé"><sup>?</sup></abbr>', // title
            array($this, "versionSeLogerCallback"), // callback
            PLUGIN_RE_NAME."OptionsSelogerPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "idAgency", // id
            'Identifiant agence <abbr title="Identifiant pour utiliser le format SeLoger"><sup>?</sup></abbr>', // title
            array($this, "idAgencyCallback"), // callback
            PLUGIN_RE_NAME."OptionsSelogerPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
    }
    
    public function optionsSanitizeLanguage($input) {
        $sanitaryValues = array();
        
        if(isset($input["language"]) && in_array($input["language"], array("fr", "en", "es", "de", "it"))) {
            $sanitaryValues["language"] = $input["language"];
        }
        
        if(isset($input["currency"])) {
            $sanitaryValues["currency"] = sanitize_text_field($input["currency"]);
        }
        
        return $sanitaryValues;
    }

    public function optionsSanitizeImport($input) {
        $sanitaryValues = array();
        
        if(isset($input["templateUsedImport"])) {
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
        
        if(isset($input["allowAutoImport"])) {
            $sanitaryValues["allowAutoImport"] = true;
        }else{
            $sanitaryValues["allowAutoImport"] = false;
        }
        
        return $sanitaryValues;
}

    public function optionsSanitizeExport($input) {
        $sanitaryValues = array();
        
        if(isset($input["templateUsedExport"])) {
            $sanitaryValues["templateUsedExport"] = sanitize_text_field($input["templateUsedExport"]);
        }
        
        if(isset($input["allowAutoExport"])) {
            $sanitaryValues["allowAutoExport"] = true;
        }else{
            $sanitaryValues["allowAutoExport"] = false;
        }
        
        return $sanitaryValues;
    }
    
    
    public function optionsSanitizeEmail($input) {
        $sanitaryValues = array();
        
        if(isset($input["sendMail"])) {
            $sanitaryValues["sendMail"] = true;
        }else{
            $sanitaryValues["sendMail"] = false;
        }

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
        
        if(isset($input["apiLimitCountry"]) && !ctype_space($input["apiLimitCountry"])) {
            $sanitaryValues["apiLimitCountry"] = sanitize_text_field($input["apiLimitCountry"]);
        }
        
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
    
    
    public function infoImports() {
        
    }
    
    public function infoExports() {
        
    }
    
    /* LANGAGE */
    
    public function languageCallback() { ?>
        <select name="<?=PLUGIN_RE_NAME."OptionsLanguage[language]";?>" id="langage">
            <option value="fr" <?php selected($this->optionsLanguage["language"], "fr"); ?>>Français</option>
            <option value="en" <?php selected($this->optionsLanguage["language"], "en"); ?>>English</option>
            <option value="es" <?php selected($this->optionsLanguage["language"], "es"); ?>>Español</option>
            <option value="de" <?php selected($this->optionsLanguage["language"], "de"); ?>>Deutsch</option>
            <option value="it" <?php selected($this->optionsLanguage["language"], "it"); ?>>Italiano</option>
        </select>
    <?php }
    
    public function currencyCallback() { 
        isset($this->optionsLanguage["currency"]) ? absint($this->optionsLanguage["currency"]) : '1'; ?>
            <input type="text" id="currency" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsLanguage[currency]";?>" 
               placeholder='€' 
               value="<?=isset($this->optionsLanguage["currency"]) ? esc_attr($this->optionsLanguage["currency"]) : '$';?>">
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
        <label for="allowAutoImport">Oui</label>
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
        <label for="allowAutoExport">Oui</label>
    <?php }
    
    /* EMAIL */

    public function sendMailCallback() { ?>
        <input type="checkbox" 
               name="<?=PLUGIN_RE_NAME."OptionsEmail[sendMail]";?>" id="sendMail" 
                   <?php isset($this->optionsEmail["sendMail"])?checked($this->optionsEmail["sendMail"], true):''?>>&nbsp;
        <label for="sendMail">Oui</label>
    <?php }

    public function emailErrorCallback() {      
        $value = isset($this->optionsEmail["emailError"]) ? esc_attr($this->optionsEmail["emailError"]) : '';
        ?>
        <input type="email" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsEmail[emailError]";?>" 
               id="emailError" placeholder="adresse@mail.com" 
               value="<?=$value;?>">
    <?php }
    
    public function emailAdCallback() { ?>
        <input type="email" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsEmail[emailAd]";?>" id="emailAd" placeholder="adresse@mail.com" 
               value="<?=isset($this->optionsEmail["emailAd"]) ? esc_attr($this->optionsEmail["emailAd"]) : '';?>">
    <?php }
       

    /* HONORAIRES */    
    public function feesUrlCallback() { ?>
        <input type="text" id="feesUrl" class="regular-text" 
               name="<?=PLUGIN_RE_NAME."OptionsFees[feesUrl]";?>" 
               placeholder="<?=$_SERVER["HTTP_HOST"]."/honoraires.pdf";?>" 
               value="<?=isset($this->optionsFees["feesUrl"]) ? esc_attr($this->optionsFees["feesUrl"]) : '';?>">
    <?php }
    
    public function feesFileCallback() {
        $name = PLUGIN_RE_NAME."OptionsFees[feesFile]";
        echo "<input type='file' name='$name' accept='.pdf, image/*'>";
    }
    
    /* APIs */
    
    public function apiUsedCallback() {         
        $name = PLUGIN_RE_NAME."OptionsApis[apiUsed]";
        ?>
            <input type="radio" name="<?=$name;?>" id="govFr" value="govFr" <?php isset($this->optionsApis["apiUsed"])?checked($this->optionsApis["apiUsed"], "govFr"):'';?>><label for="govFr">Api adresse.data.gouv.fr&nbsp;</label><br />
            <input type="radio" name="<?=$name;?>" value="google" id="google" <?php isset($this->optionsApis["apiUsed"])?checked($this->optionsApis["apiUsed"], "google"):'';?>><label for="google">Api Google&nbsp;</label>
        <?php
    }
    
    public function apiKeyGoogleCallback() {
        $value = isset($this->optionsApis["apiKeyGoogle"]) ? esc_attr($this->optionsApis["apiKeyGoogle"]) : '';
        ?>
            <input type="text" class="regular-text" 
                   name="<?=PLUGIN_RE_NAME."OptionsApis[apiKeyGoogle]";?>" id="apiKeyGoogle" placeholder="123" 
                   value="<?=$value;?>">
    <?php }
    
    public function apiLimitCountryCallback() {
        $value = isset($this->optionsApis["apiLimitCountry"]) ? esc_attr($this->optionsApis["apiLimitCountry"]) : '';
        ?>
            <input type="text" class="regular-text" 
                   name="<?=PLUGIN_RE_NAME."OptionsApis[apiLimitCountry]";?>" id="apiLimitCountry" placeholder="fr" 
                   value="<?=$value;?>">
    <?php }
    
    /* Modèle SeLoger */
    
    public function idAgencyCallback() {
        $value = isset($this->optionsSeLoger["idAgency"]) ? esc_attr($this->optionsSeLoger["idAgency"]) : '';
        ?>
            <input type="text" class="regular-text" 
                   name="<?=PLUGIN_RE_NAME."OptionsSeLoger[idAgency]";?>" id="idAgency" placeholder="MonAgence" 
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
