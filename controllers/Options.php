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
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=imports" class="nav-tab <?= $tab === "imports" ? "nav-tab-active" : ''; ?>">Imports</a>
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=exports" class="nav-tab <?= $tab === "exports" ? "nav-tab-active" : ''; ?>">Exports</a>
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=ads" class="nav-tab <?= $tab === "ads" ? "nav-tab-active" : ''; ?>">Affichage annonces</a>
                <!--<a href="edit-tags.php?taxonomy=adTypeProperty&post_type=ad" class="nav-tab <?/= $tab === "tags" ? "nav-tab-active" : ''; ?>">Catégories</a>-->
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=email" class="nav-tab <?= $tab === "email" ? "nav-tab-active" : ''; ?>">Mail</a>
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=fees" class="nav-tab <?= $tab === "fees" ? "nav-tab-active" : ''; ?>">Barème des honoraires</a>
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=style" class="nav-tab <?= $tab === "style" ? "nav-tab-active" : ''; ?>">Style</a>
                <a href="edit.php?post_type=re-ad&page=bthoptions&tab=apis" class="nav-tab <?= $tab === "apis" ? "nav-tab-active" : ''; ?>">APIs</a>                                
            </h2>
        <?php   
        }
    }

    public function optionsPageInit() {
        $this->optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");
        $this->optionsExports = get_option(PLUGIN_RE_NAME."OptionsExports");
        $this->optionsMapping = get_option(PLUGIN_RE_NAME."OptionsMapping");
        $this->optionsAds = get_option(PLUGIN_RE_NAME."OptionsAds");
        $this->optionsEmail = get_option(PLUGIN_RE_NAME."OptionsEmail");
        $this->optionsFees = get_option(PLUGIN_RE_NAME."OptionsFees");
        $this->optionsStyle = get_option(PLUGIN_RE_NAME."OptionsStyle");
        $this->optionsApis = get_option(PLUGIN_RE_NAME."OptionsApis");
        
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
        
        register_setting( //Enregistrement des options pour l'affichage des champs
            PLUGIN_RE_NAME."OptionsAdsGroup", // option_group
            PLUGIN_RE_NAME."OptionsAds", // option_name
            array($this, "optionsSanitizeAds") // sanitizeCallback
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
            PLUGIN_RE_NAME."OptionsStyleGroup", // option_group
            PLUGIN_RE_NAME."OptionsStyle", // option_name
            array($this, "optionsSanitizeStyle") // sanitizeCallback
        );
        
        register_setting( //Enregistrement des options pour le style
            PLUGIN_RE_NAME."OptionsApisGroup", // option_group
            PLUGIN_RE_NAME."OptionsApis", // option_name
            array($this, "optionsSanitizeApis") // sanitizeCallback
        );
        
        
        add_settings_section( //Section pour les imports
            PLUGIN_RE_NAME."optionsSection", // id
            "Importation", // title
            //array($this, "infoImports"), // callback
            null,
            PLUGIN_RE_NAME."OptionsImportsPage" // page
        );
        
        add_settings_section( //Section pour les exports
            PLUGIN_RE_NAME."optionsSection", // id
            "Exportation", // title
            //array($this, "infoExports"), // callback
            null,
            PLUGIN_RE_NAME."OptionsExportsPage" // page
        );
        
        add_settings_section( //Section pour le mapping des champs
            PLUGIN_RE_NAME."optionsSection", // id
            "Annonces", // title
            //array($this, "infoExports"), // callback
            null,
            PLUGIN_RE_NAME."OptionsAdsPage" // page
        );     
        
        add_settings_section( //Section pour les diverses options
            PLUGIN_RE_NAME."optionsSection", // id
            "Mail", // title
            //array($this, "infoDivers"), // callback
            null,
            PLUGIN_RE_NAME."OptionsEmailPage" // page
        );
        
        add_settings_section( //Section pour les diverses options
            PLUGIN_RE_NAME."optionsSection", // id
            "Honoraires", // title
            //array($this, "infoDivers"), // callback
            null,
            PLUGIN_RE_NAME."OptionsFeesPage" // page
        );
        
        add_settings_section( //Section pour les diverses options
            PLUGIN_RE_NAME."optionsSection", // id
            "Style", // title
            //array($this, "infoDivers"), // callback
            null,
            PLUGIN_RE_NAME."OptionsStylePage" // page
        );
        
        add_settings_section( //Section pour les diverses options
            PLUGIN_RE_NAME."optionsSection", // id
            "APIs", // title
            null,
            PLUGIN_RE_NAME."OptionsApisPage" // page
        );
        
        
        /* Imports */
        
        add_settings_field(
            "autoImport", // id
            'Import automatique <abbr title="Les annonces stockées dans le répertoire d\'importation POURRONT être importées automatiquement sur ce site via un cron job"><sup>?</sup></abbr>', // title
            array($this, "autoImportCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "dirImportPath", // id
            'Répertoire d\'importation <abbr title="Chemin vers les fichiers à importer automatiquement"><sup>?</sup></abbr>', // title
            array($this, "dirImportPathCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "saveCSVImport", // id
            'Sauvegarde des importations <abbr title="Une copie du fichier contenant les annonces importées sera stockée sur le serveur"><sup>?</sup></abbr>', // title
            array($this, "saveCSVImportCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "dirSavesPath", // id
            'Répertoire de sauvegarde <abbr title="Chemin où sauvegarder les annonces importées"><sup>?</sup></abbr>', // title
            array($this, "dirSavesPathCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );     
        
        add_settings_field(
            "maxSaves", // id
            'Nombre de sauvegardes <abbr title="Nombre de copies des fichiers contenant les annonces importées à conserver"><sup>?</sup></abbr>', // title
            array($this, "maxSavesCallback"), // callback
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
            "adressPrecision", // id
            'Affichage de l\'adresse <abbr title="Il est possible de faire apparaître l\'adresse complète ou seulement la commune/l\'arrondissement"><sup>?</sup></abbr>', // title
            array($this, "addressPrecisionCallback"), // callback
            PLUGIN_RE_NAME."OptionsImportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        /* Exports */
                    
        add_settings_field(
            "dirExportPath", //id
            'Répertoire d\'exportation <abbr title="Chemin où seront exportées localement les annonces"><sup>?</sup></abbr>', //title
            array($this, "dirExportPathCallback"), //callback
            PLUGIN_RE_NAME."OptionsExportsPage", //page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "versionSeLoger", // id
            'Version SeLoger <abbr title="Version et révision du format SeLoger utilisé"><sup>?</sup></abbr>', // title
            array($this, "versionSeLogerCallback"), // callback
            PLUGIN_RE_NAME."OptionsExportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "idAgency", // id
            'Identifiant agence <abbr title="Identifiant pour utiliser le format SeLoger"><sup>?</sup></abbr>', // title
            array($this, "idAgencyCallback"), // callback
            PLUGIN_RE_NAME."OptionsExportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        add_settings_field(
            "maxCSVColumn", // id
            'Dernier rang du champ possible <abbr title="Ce nombre correspond au nombre de champs possible dans le format SeLoger +1"><sup>?</sup></abbr>', // title
            array($this, "maxCSVColumnCallback"), // callback
            PLUGIN_RE_NAME."OptionsExportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        /* Ads */
        
        add_settings_field(
            /*"mapping", // id
            null,
            array($this, "mappingCallback"), // callback
            PLUGIN_RE_NAME."OptionsMappingPage", // page
            PLUGIN_RE_NAME."optionsSection" // section*/
            "displayAdsUnavailableAds",
            'Afficher les annonces avec des biens indisponibles <abbr title="Un bien est indisponible quand il n\'est plus à la vente ou à la location"><sup>?</sup></abbr>',
            array($this, "displayAdsUnavailableAdsCallback"),
            PLUGIN_RE_NAME."OptionsAdsPage",
            PLUGIN_RE_NAME."optionsSection"
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
        
        /* Style */
        
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
        
    }

    public function optionsSanitizeImport($input) {
        $sanitaryValues = array();

        if(isset($input["dirSavesPath"]) && !ctype_space($input["dirSavesPath"])) {
            $sanitaryValues["dirSavesPath"] = sanitize_text_field($input["dirSavesPath"]);
        }
        
        if(isset($input["maxDim"]) && !ctype_space($input["maxDim"])) {
            $sanitaryValues["maxDim"] = $input["maxDim"];
        }
        
        if(isset($input["dirImportPath"]) && !ctype_space($input["dirImportPath"])) {
            $sanitaryValues["dirImportPath"] = sanitize_text_field($input["dirImportPath"]);
        }

        if(isset($input["maxSaves"]) && !ctype_space($input["maxSaves"])) {
            $sanitaryValues["maxSaves"] = absint($input["maxSaves"]);
        }
        
        if(isset($input["autoImport"])) {
            $sanitaryValues["autoImport"] = true;
        }else{
            $sanitaryValues["autoImport"] = false;
        }
        
        if(isset($input["saveCSVImport"])) {
            $sanitaryValues["saveCSVImport"] = true;
        }else{
            $sanitaryValues["saveCSVImport"] = false;
        }
        
        if(isset($input["addressPrecision"]) && !ctype_space($input["addressPrecision"])) {
            $sanitaryValues["addressPrecision"] = $input["addressPrecision"];
        }

        return $sanitaryValues;
}

    public function optionsSanitizeExport($input) {
        $sanitaryValues = array();
        
        if(isset($input["dirExportPath"]) && !ctype_space($input["dirExportPath"])) {
            $sanitaryValues["dirExportPath"] = sanitize_text_field($input["dirExportPath"]);
        }
        if(isset($input["versionSeLoger"]) && !ctype_space($input["versionSeLoger"])) {
            $sanitaryValues["versionSeLoger"] = sanitize_text_field($input["versionSeLoger"]);
        }
        if(isset($input["idAgency"]) && !ctype_space($input["idAgency"])) {
            $sanitaryValues["idAgency"] = sanitize_text_field($input["idAgency"]);
        }
        if(isset($input["maxCSVColumn"]) && !ctype_space($input["maxCSVColumn"])) {
            $sanitaryValues["maxCSVColumn"] = sanitize_text_field($input["maxCSVColumn"]);
        }
        
        return $sanitaryValues;
    }
    
    public function optionsSanitizeMapping($input) {
        if(isset($input["mappingFields"]) && is_string($input["mappingFields"]) && is_array(json_decode($input["mappingFields"], true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false) {
            $sanitaryValues = json_decode($input["mappingFields"], true);
        }      
        
        return $sanitaryValues;
    }
    
    public function optionsSanitizeAds($input) {
        $sanitaryValues = array();
        
        if(isset($input["displayAdsUnavailable"])) {
            $sanitaryValues["displayAdsUnavailable"] = true;
        }else{
            $sanitaryValues["displayAdsUnavailable"] = false;
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
    

    public function infoImports() {
        /*//PLUGIN_RE_NAME = basename(plugin_dir_path(dirname(__FILE__, 1)));
        $logsURL = get_site_url()."/wp-content/plugins/".PLUGIN_RE_NAME."/logs.txt";
        echo '<h2><a href="tools.php?page='.PLUGIN_RE_NAME.'Options&import">Lancer une exportation</a></h2>'; 
        if(isset($_GET["import"]) && is_admin()) {
            $this->import();
            if(SELF::$newAds > 0 || SELF::$updatedAds > 0) {
                echo "Importation réussie : ".SELF::$newAds." annonce(s) créée(s) et ".SELF::$updatedAds." annonce(s) mise(s) à jour.";
            }
            if(SELF::$errorAds !== 0) {
                echo " Dont ".SELF::$errorAds." erreur(s). Consultez les logs pour en savoir plus.";
            }
        }
        if(file_exists(plugin_dir_path(__FILE__)."logs.txt")) {
            echo '<br /><a target="_blank" href="'.$logsURL.'">Voir les logs</a>';
        }*/
    }
    
    public function infoExports() {
        
    }
    
    public function infoDivers() {
        
    }


    public function idAgencyCallback() {
        $args = array(
            "type" => "text",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsExports[idAgency]",
            "id" => "idAgency",
            "placeholder" => "monAgence",
            "value" => isset($this->optionsExports["idAgency"]) ? esc_attr($this->optionsExports["idAgency"]) : ''
        );

        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {
                if(!empty($value)) {                
                    echo "$key=$value ";            
                }
            }
        }
        echo ">";
    }

    public function dirSavesPathCallback() {
        $args = array(
            "type" => "text",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsImports[dirSavesPath]",
            "id" => "dirSavesPath",
            "pattern" => "^[^\/].+\/$",
            "placeholder" => "wp-content/plugins/".PLUGIN_RE_NAME."/saves/",
            "value" => isset($this->optionsImports["dirSavesPath"]) ? esc_attr($this->optionsImports["dirSavesPath"]) : ''
        );
        if(isset($this->optionsImports["saveCSVImport"]) && $this->optionsImports["saveCSVImport"] === true) {
            $args["required"] = "required";
        }else{
            $args["readonly"] = "readonly";
        }
        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo ">";
    }
    
    public function maxDimCallback() {
        ?> <select name="<?= PLUGIN_RE_NAME; ?>OptionsImports[maxDim]" id="maxDim">
            <?php $selected = (isset($this->optionsImports['maxDim']) && $this->optionsImports['maxDim'] == '512') ? 'selected' : '' ; ?>
            <option value="512" <?php echo $selected; ?>>512px</option>
            <?php $selected = (isset($this->optionsImports['maxDim']) && $this->optionsImports['maxDim'] == '1024') ? 'selected' : '' ; ?>
            <option value="1024" <?php echo $selected; ?>>1024px</option>
            <?php $selected = (isset($this->optionsImports['maxDim']) && $this->optionsImports['maxDim'] == '1536') ? 'selected' : '' ; ?>
            <option value="1536" <?php echo $selected; ?>>1536px</option>
            <?php $selected = (isset($this->optionsImports['maxDim']) && $this->optionsImports['maxDim'] == '2048') ? 'selected' : '' ; ?>
            <option value="2048" <?php echo $selected; ?>>2048px</option>
        </select> <?php
    }
    
    public function addressPrecisionCallback() {
        ?> <select name="<?= PLUGIN_RE_NAME; ?>OptionsImports[addressPrecision]" id="addressPrecision">
            <?php $selected = (isset($this->optionsImports['addressPrecision']) && $this->optionsImports['addressPrecision'] == 'all') ? 'selected' : '' ; ?>
            <option value="all" <?php echo $selected; ?>>Adresse complète</option>
            <?php $selected = (isset($this->optionsImports['addressPrecision']) && $this->optionsImports['addressPrecision'] == 'onlyPC') ? 'selected' : '' ; ?>
            <option value="onlyPC" <?php echo $selected; ?>>Commune</option>
        </select> <?php
    }
    
    public function dirImportPathCallback() {
        $args = array(
            "type" => "text",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsImports[dirImportPath]",
            "id" => "dirImportPath",
            "pattern" => "^[^\/].+\/$",
            "placeholder" => "wp-content/plugins/".PLUGIN_RE_NAME."/import/",
            "value" => isset($this->optionsImports["dirImportPath"]) ? esc_attr($this->optionsImports["dirImportPath"]) : ''
        );
        if(isset($this->optionsImports["autoImport"]) && $this->optionsImports["autoImport"] === true) {
            $args["required"] = "required";
        }else{
            $args["readonly"] = "readonly";
        }
        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {
                if(!empty($value)) {                
                    echo "$key=$value ";            
                }
            }
        }
        echo ">";
    }
    
    public function saveCSVImportCallback() {
        $args = array(
            "type" => "checkbox",
            "name" => PLUGIN_RE_NAME."OptionsImports[saveCSVImport]",
            "id" => "saveCSVImport",
            "onchange" => "readOnlyFields(this,['dirSavesPath','maxSaves']);",
        );
        if(isset($this->optionsImports["saveCSVImport"]) && $this->optionsImports["saveCSVImport"] === true) {
            $args["checked"] = "checked";
        }
        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo '><label for="saveCSVImport"> Oui</label>';
    }

    public function maxSavesCallback() {
        $args = array(
            "type" => "number",
            "min" => 1,
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsImports[maxSaves]",
            "id" => "maxSaves",
            "value" => isset($this->optionsImports["maxSaves"]) ? absint($this->optionsImports["maxSaves"]) : ''
        );
        if(isset($this->optionsImports["saveCSVImport"]) && $this->optionsImports["saveCSVImport"] === true) {
            $args["required"] = "required";
        }else{
            $args["readonly"] = "readonly";
        }
        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo ">";
    }
    
    public function autoImportCallback() {
        $args = array(
            "type" => "checkbox",
            "name" => PLUGIN_RE_NAME."OptionsImports[autoImport]",
            "id" => "autoImport",
            "onchange" => "readOnlyFields(this,['dirImportPath']);",
        );
        if(isset($this->optionsImports["autoImport"]) && $this->optionsImports["autoImport"] === true) {
            $args["checked"] = "checked";
        }
        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo '><label for="autoImport"> Oui</label>';
    }
    
    public function dirExportPathCallback() {
        $args = array(
            "type" => "text",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsExports[dirExportPath]",
            "id" => "dirExportPath",
            "pattern" => "^[^\/].+\/$",
            "placeholder" => "wp-content/plugins/".PLUGIN_RE_NAME."/export/",
            "value" => isset($this->optionsExports["dirExportPath"]) ? esc_attr($this->optionsExports["dirExportPath"]) : '',
            "required" => "true"
        );

        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {
                if(!empty($value)) {                
                    echo "$key=$value ";            
                }
            }
        }
        echo ">";
    }
    
    public function versionSeLogerCallback() {
        $args = array(
            "type" => "text",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsExports[versionSeLoger]",
            "id" => "versionSeloger",
            "pattern" => "\d+\.\d+-\d+",
            "placeholder" => "4.08-007",
            "value" => isset($this->optionsExports["versionSeLoger"]) ? esc_attr($this->optionsExports["versionSeLoger"]) : '',
            "required" => "true"
        );

        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
    }
    
    public function maxCSVColumnCallback() {
        $args = array(
            "type" => "number",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsExports[maxCSVColumn]",
            "id" => "maxCSVColumn",
            "placeholder" => 328,
            "value" => isset($this->optionsExports["maxCSVColumn"]) ? esc_attr($this->optionsExports["maxCSVColumn"]) : '',
            "required" => "true"
        );

        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
    }

    public function sendMailCallback() {
        $args = array(
            "type" => "checkbox",
            "name" => PLUGIN_RE_NAME."OptionsEmail[sendMail]",
            "id" => "sendMail",
            "onchange" => "readOnlyFields(this,['emailError']);"
        );
        if(isset($this->optionsEmail["sendMail"]) && $this->optionsEmail["sendMail"] === true) {
            $args["checked"] = "checked";
        }
        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo '><label for="sendMail"> Oui</label>';
    }

    public function emailErrorCallback() {
        $args = array(
            "type" => "email",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsEmail[emailError]",
            "id" => "emailError",
            "placeholder" => "adresse@mail.com",
            "value" => isset($this->optionsEmail["emailError"]) ? esc_attr($this->optionsEmail["emailError"]) : ''
        );
        if(isset($this->optionsEmail["sendMail"]) && $this->optionsEmail["sendMail"] === true) {
            $args["required"] = "required";
        }else{
            $args["readonly"] = "readonly";
        }
        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo ">";
    }
    
    public function emailAdCallback() {
        $args = array(
            "type" => "email",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsEmail[emailAd]",
            "id" => "emailAd",
            "placeholder" => "adresse@mail.com",
            "value" => isset($this->optionsEmail["emailAd"]) ? esc_attr($this->optionsEmail["emailAd"]) : ''
        );
        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo ">";
    }
    
    
    public function mappingCallback() {
        $mappingFields = get_option(PLUGIN_RE_NAME."OptionsMapping");
        ?>       
                <div id="table" class="table-editable">
                    <a href="#down"><span class="table-add dashicons-before dashicons-plus up"></span></a>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Numéro du champ (CSV)</th>
                                <th>Nom de la valeur (<?= PLUGIN_RE_NAME;?>)</th>
                                <th>Type du champ</th>
                                <th>Section du champ</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                foreach($mappingFields as $field) {
                                    ?>
                                    <tr <?php if($field["hidden"] === "true") { echo "class='hiddenField'"; } ?>>
                                        <td class="editable" contenteditable="true"><span class="id contentTD"><?= $field["id"]; ?></span></td>
                                        <td <?php if($field["perso"] === "true") { echo "class='editable' contenteditable='true'"; }?>><span class="name contentTD"><?= $field["name"]; ?></span></td>
                                        <td class="<?php if($field["perso"] === "true") { echo "editable"; }?>">
                                            <select class="kindField" <?php if($field["perso"] === "false"){echo "disabled";}?>>
                                                <option<?php if($field["kindField"] === "text"){echo " selected";} ?>>text</option>
                                                <option<?php if($field["kindField"] === "number"){echo " selected";} ?>>number</option>
                                                <option<?php if($field["kindField"] === "radio"){echo " selected";} ?>>radio</option>
                                                <option<?php if($field["kindField"] === "select"){echo " selected";} ?>>select</option>
                                                <option<?php if($field["kindField"] === "checkbox"){echo " selected";} ?>>checkbox</option>
                                                <option<?php if($field["kindField"] === "picture"){echo " selected";} ?>>picture</option>
                                            </select>
                                        </td>
                                        <td class="<?php if($field["unchangeable"] === "false") { echo "editable"; }?>">
                                            <select class="section" <?php if($field["unchangeable"] === "true" || $field["hidden"] === "true"){echo "disabled";}?>>
                                                <option<?php if($field["section"] === "basics"){echo " selected";} ?>>basics</option>
                                                <option<?php if($field["section"] === "complementary"){echo " selected";} ?>>complementary</option>
                                                <option<?php if($field["section"] === "category"){echo " selected";} ?>>category</option>
                                                <option<?php if($field["section"] === "title"){echo " selected";} ?>>title</option>
                                                <option<?php if($field["section"] === "description"){echo " selected";} ?>>description</option>
                                                <option<?php if($field["section"] === "status"){echo " selected";} ?>>status</option>
                                            </select>
                                        </td>
                                        <td>
                                            <span class="config-toggle dashicons-before dashicons-arrow-down-alt2"></span>
                                        </td>
                                        <?php if($field["perso"] === "true") { ?>
                                        <td>
                                            <span class="table-up dashicons-before dashicons-arrow-up-alt"></span>
                                            <span class="table-down dashicons-before dashicons-arrow-down-alt"></span>
                                        </td>
                                        <?php } ?>
                                        <?php if($field["perso"] === "true") { ?>
                                            <td>
                                                <span class="table-remove dashicons-before dashicons-trash"></span>
                                            </td>
                                        <?php }else if($field["hidden"] === "true") { ?>
                                            <td>
                                                <span class="table-visible dashicons-before dashicons-visibility"></span>
                                            </td>
                                        <?php }else{ ?>
                                            <td>
                                                <span class="table-hidden dashicons-before dashicons-hidden"></span>
                                            </td>
                                        <?php } ?>
                                    </tr>
                                    <tr class="collapse">
                                        <td class="editable">Style CSS : <textarea class="css"></textarea></td>
                                        <td class="editable writeOptions">Options possibles : <textarea class="options"></textarea></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            <tr class="table-separator">
                                <td colspan="4">Champs personnalisés</td>
                            </tr>
                        </tbody>
                    </table>
                    <span id="down" class="table-add dashicons-before dashicons-plus down"></span>
                </div>                
            
            <textarea id="mappingFields" name="<?=PLUGIN_RE_NAME."OptionsMapping[mappingFields]";?>"></textarea>
        <?php
    }
    
    public function displayAdsUnavailableAdsCallback() {
        $args = array(
            "type" => "checkbox",
            "name" => PLUGIN_RE_NAME."OptionsAds[displayAdsUnavailable]",
            "id" => "displayAdsUnavailable",
        );
        if(isset($this->optionsAds["displayAdsUnavailable"]) && $this->optionsAds["displayAdsUnavailable"] === true) {
            $args["checked"] = "checked";
        }
        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo '><label for="displayAdsUnavailable"> Oui</label>';
    }
    
    public function feesUrlCallback() {
        $args = array(
            "type" => "text",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsFees[feesUrl]",
            "id" => "feesUrl",
            "placeholder" => $_SERVER["HTTP_HOST"]."/honoraires.pdf",
            "value" => isset($this->optionsFees["feesUrl"]) ? esc_attr($this->optionsFees["feesUrl"]) : '',
        );

        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo ">";   
    }
    
    public function feesFileCallback() {
        $name = PLUGIN_RE_NAME."OptionsFees[feesFile]";
        echo "<input type='file' name='$name' accept='.pdf, image/*'>";
    }
    
    public function apiUsedCallback() {         
        $name = PLUGIN_RE_NAME."OptionsApis[apiUsed]";
        ?>
            <input type="radio" name="<?=$name;?>" id="govFr" value="govFr" <?= isset($this->optionsApis["apiUsed"]) && $this->optionsApis["apiUsed"] == 'govFr' ? "checked" : '';?>><label for="govFr">Api adresse.data.gouv.fr&nbsp;</label><br />
            <input type="radio" name="<?=$name;?>" value="google" id="google" <?= isset($this->optionsApis["apiUsed"]) && $this->optionsApis["apiUsed"] == 'google' ? "checked" : '';?>><label for="google">Api Google&nbsp;</label>
        <?php
    }
    
    public function apiKeyGoogleCallback() {
        $args = array(
            "type" => "text",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsApis[apiKeyGoogle]",
            "id" => "apiKeyGoogle",
            "placeholder" => "123",
            "value" => isset($this->optionsApis["apiKeyGoogle"]) ? esc_attr($this->optionsApis["apiKeyGoogle"]) : '',
        );

        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo ">";   
    }
    
    public function apiLimitCountryCallback() {
        $args = array(
            "type" => "text",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsApis[apiLimitCountry]",
            "id" => "apiLimitCountry",
            "placeholder" => "fr",
            "value" => isset($this->optionsApis["apiLimitCountry"]) ? esc_attr($this->optionsApis["apiLimitCountry"]) : '',
        );

        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo ">";   
    }
}
