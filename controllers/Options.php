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
        if($base === "edit-tags" || $base === "ad_page_bthoptions") { 
            if($base === "ad_page_bthoptions") { //Si on est sur la page des options custom
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
                <a href="edit.php?post_type=ad&page=bthoptions&tab=imports" class="nav-tab <?= $tab === "imports" ? "nav-tab-active" : ''; ?>">Imports</a>
                <a href="edit.php?post_type=ad&page=bthoptions&tab=exports" class="nav-tab <?= $tab === "exports" ? "nav-tab-active" : ''; ?>">Exports</a>
                <!--<a href="edit.php?post_type=ad&page=bthoptions&tab=mapping" class="nav-tab <?/= $tab === "mapping" ? "nav-tab-active" : ''; ?>">Mapping</a>-->
                <!--<a href="edit-tags.php?taxonomy=adTypeProperty&post_type=ad" class="nav-tab <?/= $tab === "tags" ? "nav-tab-active" : ''; ?>">Catégories</a>-->
                <a href="edit.php?post_type=ad&page=bthoptions&tab=email" class="nav-tab <?= $tab === "email" ? "nav-tab-active" : ''; ?>">Mail</a>
                <a href="edit.php?post_type=ad&page=bthoptions&tab=fees" class="nav-tab <?= $tab === "fees" ? "nav-tab-active" : ''; ?>">Barème des honoraires</a>                
            </h2>
        <?php   
        }
    }

    public function optionsPageInit() {
        $this->optionsImports = get_option(PLUGIN_RE_NAME."OptionsImports");
        $this->optionsExports = get_option(PLUGIN_RE_NAME."OptionsExports");
        $this->optionsMapping = get_option(PLUGIN_RE_NAME."OptionsMapping");
        $this->optionsEmail = get_option(PLUGIN_RE_NAME."OptionsEmail");
        $this->optionsFees = get_option(PLUGIN_RE_NAME."OptionsFees");
        
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
        
        register_setting( //Enregistrement du mapping des champs
            PLUGIN_RE_NAME."OptionsMappingGroup", // option_group
            PLUGIN_RE_NAME."OptionsMapping", // option_name
            array($this, "optionsSanitizeMapping") // sanitizeCallback
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
            "Mapping", // title
            //array($this, "infoExports"), // callback
            null,
            PLUGIN_RE_NAME."OptionsMappingPage" // page
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
            "Fees", // title
            //array($this, "infoDivers"), // callback
            null,
            PLUGIN_RE_NAME."OptionsFeesPage" // page
        );
        
        
        /* Imports */
        
        add_settings_field(
            "autoImport", // id
            'Import automatique <abbr title="Les annonces stockées dans le répertoire d\'importation pourront être importées automatiquement sur ce site via un cron job"><sup>?</sup></abbr>', // title
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
        
        /* Exports */
                    
        add_settings_field(
            "dirExportPath", //id
            'Répertoire d\'exportation <abbr title"Chemin où seront exportées localement les annonces"><sup>?</sup></abbr>', //title
            array($this, "dirExportPathCallback"), //callback
            PLUGIN_RE_NAME."OptionsExportsPage", //page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        /*add_settings_field(
            "autoExport", // id
            'Export automatique <abbr title="Les annonces seront exportées automatiquement vers les sites partenaires - voir l\'onglet \'Exports\'"><sup>?</sup></abbr>', // title
            array($this, "autoExportCallback"), // callback
            PLUGIN_RE_NAME."OptionsExportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );*/
        
        add_settings_field(
            "idAgency", // id
            'Identifiant agence <abbr title="Identifiant pour utiliser le format SeLoger"><sup>?</sup></abbr>', // title
            array($this, "idAgencyCallback"), // callback
            PLUGIN_RE_NAME."OptionsExportsPage", // page
            PLUGIN_RE_NAME."optionsSection" // section
        );
        
        /* Mapping */
        
        add_settings_field(
            "mapping", // id
            null,
            array($this, "mappingCallback"), // callback
            PLUGIN_RE_NAME."OptionsMappingPage", // page
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
            "email", // id
            "Adresse mail à contacter en cas d'erreur", // title
            array($this, "emailCallback"), // callback
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
    }

    public function optionsSanitizeImport($input) {
        $sanitaryValues = array();

        if(isset($input["dirSavesPath"])) {
            $sanitaryValues["dirSavesPath"] = sanitize_text_field($input["dirSavesPath"]);
        }
        
        if(isset($input["maxDim"])) {
            $sanitaryValues["maxDim"] = $input["maxDim"];
        }
        
        if(isset($input["dirImportPath"])) {
            $sanitaryValues["dirImportPath"] = sanitize_text_field($input["dirImportPath"]);
        }

        if(isset($input["maxSaves"])) {
            $sanitaryValues["maxSaves"] = absint($input["maxSaves"]);
        }
        
        if(isset($input["autoImport"])) {
            $sanitaryValues["autoImport"] = $input["autoImport"];
        }
        
        if(isset($input["saveCSVImport"])) {
            $sanitaryValues["saveCSVImport"] = $input["saveCSVImport"];
        }

        return $sanitaryValues;
}

    public function optionsSanitizeExport($input) {
        $sanitaryValues = array();
        
        if(isset($input["dirExportPath"])) {
            $sanitaryValues["dirExportPath"] = sanitize_text_field($input["dirExportPath"]);
        }
        if(isset($input["autoExport"])) {
            $sanitaryValues["autoExport"] = $input["autoExport"];
        }
        if(isset($input["idAgency"])) {
            $sanitaryValues["idAgency"] = sanitize_text_field($input["idAgency"]);
        }
        
        return $sanitaryValues;
    }
    
    public function optionsSanitizeMapping($input) {
        //$sanitaryValues = array();
        if(isset($input["mappingFields"]) && is_string($input["mappingFields"]) && is_array(json_decode($input["mappingFields"], true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false) {
            $sanitaryValues = json_decode($input["mappingFields"], true);
        }      
        
        return $sanitaryValues;
    }
    
    public function optionsSanitizeEmail($input) {
        $sanitaryValues = array();
        
        if(isset($input["sendMail"])) {
            $sanitaryValues["sendMail"] = $input["sendMail"];
        }

        if(isset($input["email"])) {
            $sanitaryValues["email"] = sanitize_text_field($input["email"]);
        }
        
        return $sanitaryValues;
    }
    
    public function optionsSanitizeFees($input) {
        $sanitaryValues = array();
        $inputFile = $_FILES[PLUGIN_RE_NAME."OptionsFees"];
        
        foreach($inputFile as $key => $value) {
            $inputFile[$key] = $value["feesFile"]; 
        }
        
        if(isset($input["feesUrl"])) {
            $sanitaryValues["feesUrl"] = sanitize_url($input["feesUrl"], array("https", "http"));
        }
                
        if(isset($inputFile)) {
            $upload = wp_handle_upload($inputFile, array("test_form" => false));
            echo "ok1";
            print_r($upload);
            if(isset($upload["url"]) && !empty($upload["url"])) {
                echo "ok2";
                $sanitaryValues["feesUrl"] = $upload["url"];
            }
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
            "value" => isset($this->optionsExports["idAgency"]) ? esc_attr($this->optionsExports["idAgency"]) : ''
        );
        if(isset($this->optionsExports["autoExport"])) {
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
        if(isset($this->optionsImports['saveCSVImport'])) {
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
            <?php $selected = (isset($this->optionsImports['maxDim'] ) && $this->optionsImports['maxDim'] == '512') ? 'selected' : '' ; ?>
            <option value="512" <?php echo $selected; ?>>512px</option>
            <?php $selected = (isset($this->optionsImports['maxDim'] ) && $this->optionsImports['maxDim'] == '1024') ? 'selected' : '' ; ?>
            <option value="1024" <?php echo $selected; ?>>1024px</option>
            <?php $selected = (isset($this->optionsImports['maxDim'] ) && $this->optionsImports['maxDim'] == '1536') ? 'selected' : '' ; ?>
            <option value="1536" <?php echo $selected; ?>>1536px</option>
            <?php $selected = (isset($this->optionsImports['maxDim'] ) && $this->optionsImports['maxDim'] == '2048') ? 'selected' : '' ; ?>
            <option value="2048" <?php echo $selected; ?>>2048px</option>
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
        if(isset($this->optionsImports['autoImport'])) {
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
        if(isset($this->optionsImports['saveCSVImport'])) {
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
        if(isset($this->optionsImports['saveCSVImport'])) {
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
        if(isset($this->optionsImports['autoImport'])) {
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
    
    public function autoExportCallback() {
        $args = array(
            "type" => "checkbox",
            "name" => PLUGIN_RE_NAME."OptionsExports[autoExport]",
            "id" => "autoExport",
            "onchange" => "readOnlyFields(this,['idAgency']);"
        );
        if(isset($this->optionsExports['autoExport'])) {
            $args["checked"] = "checked";
        }
        echo "<input ";
        foreach($args as $key => $value) {
            if(!empty($value)) {                
                echo "$key=$value ";            
            }
        }
        echo '><label for="autoExport"> Oui</label>';
    }

    public function sendMailCallback() {
        $args = array(
            "type" => "checkbox",
            "name" => PLUGIN_RE_NAME."OptionsEmail[sendMail]",
            "id" => "sendMail",
            "onchange" => "readOnlyFields(this);"
        );
        if(isset($this->optionsEmail['sendMail'])) {
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

    public function emailCallback() {
        $args = array(
            "type" => "email",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsEmail[email]",
            "id" => "email",
            "placeholder" => "adresse@mail.com",
            "value" => isset($this->optionsEmail["email"]) ? esc_attr($this->optionsEmail["email"]) : ''
        );
        if(isset($this->optionsEmail['sendMail'])) {
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
    
    public function feesUrlCallback() {
        $args = array(
            "type" => "text",
            "class" => "regular-text",
            "name" => PLUGIN_RE_NAME."OptionsFees[feesUrl]",
            "id" => "feesUrl",
            "placeholder" => $_SERVER["HTTP_HOST"]."/honoraires.pdf",
            "value" => isset($this->optionsFees["feesUrl"]) ? esc_attr($this->optionsFees["feesUrl"]) : '',
            "required" => true
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
}
