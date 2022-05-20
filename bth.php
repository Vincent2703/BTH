<?php
/*
Plugin Name: bth
Description: Créez des annonces immobilières et exportez les.
Version: Dev
Author: Vincent Bourdon
License:  GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
require_once('bth.php'); //Pour installer le plugin depuis l'interface

class Bth {
    public function __construct() {
        require_once("objects/Ad.php");
        require_once("objects/Agent.php");
        require_once("objects/Agency.php");
        require_once("controllers/Options.php");
        require_once("controllers/EditAd.php");
        require_once("controllers/EditAgent.php");
        require_once("controllers/EditAgency.php");
        require_once("controllers/Export.php");
        require_once("controllers/Import.php");
        
        $this->Ad           = new Ad;
        $this->Agent        = new Agent;
        $this->Agency       = new Agency;
        $this->Options      = new Options;
        $this->EditAd       = new EditAd;
        $this->EditAgent    = new EditAgent;
        $this->EditAgency   = new EditAgency;
        $this->Export       = new Export;
        $this->Import       = new Import;
        
        add_action("init", array($this, "initObjects"));

	register_activation_hook(__FILE__, array($this, 'activationPlugin')); //A l'activation du plugin...
        //Ajouter à la désactivation
        
        add_action("admin_init", array($this, "initAdmin"));
        
        add_action("admin_menu", array($this, "completeMenu"));
                
        add_action("admin_enqueue_scripts", array($this, "registerPluginStylesAdmin"));
        add_action("admin_enqueue_scripts", array($this, "registerPluginScriptsAdmin"));        
        
        add_action("wp_dashboard_setup", array($this->Import, "widgetImport"));
        add_action("wp_dashboard_setup", array($this->Export, "widgetExport"));
        
        add_action("save_post_ad", array($this->EditAd, "savePost"), 10, 2);
        add_action("save_post_agent", array($this->EditAgent, "savePost"), 10, 2);
        add_action("save_post_agency", array($this->EditAgency, "savePost"), 10, 2);
        
        add_action("restrict_manage_posts", array($this->Ad, "filterAdsByTaxonomies"));
        
        add_filter("template_include", array($this->Ad, "templatePostAd"), 1);
        add_filter("template_include", array($this->Agency, "templatePostAgency"), 1);
        add_filter("template_include", array($this->Agent, "templatePostAgent"), 1);

        add_filter("the_title", array($this, "maxLengthTitle"));
        add_filter("enter_title_here", array($this, "changeTitle"));
        add_filter("pre_get_posts", array($this->Ad, "convertIdToTermInQuery"));
        
        add_action("in_admin_header", array($this->Options, "tabsOption"));
        
        add_action("wp_ajax_nopriv_import", array($this->Import, "startImport")); //Cron
             
        
        SELF::defineGlobalConsts();
    }
    
    private static function defineGlobalConsts() {
        $configFile = fopen(__DIR__."\config.json", 'r');
        $config = json_decode(fread($configFile, filesize(__DIR__."\config.json")), true);
        fclose($configFile);
        foreach($config as $key=>$value) {
            if(!is_array($value)) {
                define($key, $value);
            }
        }
    }

    
    public function activationPlugin() { //A l'activation du plugin
        //Enregistrer les valeurs par défaut
        $defaultValuesImports = array(
                "autoImport" 		=> "on", 
                "dirImportPath"         => "wp-content/plugins/".PLUGIN_RE_NAME."/import/",
                "saveCSVImport"         => "on",
                "dirSavesPath"          => "wp-content/plugins/".PLUGIN_RE_NAME."/saves/",
                "maxSaves" 		=> 2,
                "maxDim"		=> 1024,
        );
        update_option(PLUGIN_RE_NAME."OptionsImports", $defaultValuesImports); 
        
        $defaultValuesExports = array(
                "dirExportPath"     => "wp-content/plugins/".PLUGIN_RE_NAME."/export/",
                "versionSeLoger"    => "4.08-007",
                "idAgency"          => "monAgence",
                "maxCSVColumn"       => 328
        );
        update_option(PLUGIN_RE_NAME."OptionsExports", $defaultValuesExports); 
        
        $configFile = fopen(__DIR__."\config.json", 'r');
        $config = json_decode(fread($configFile, filesize(__DIR__."\config.json")), true);
        fclose($configFile);
        $defaultMapping = $config["DEFAULT_MAPPING"];
        update_option(PLUGIN_RE_NAME."OptionsMapping", $defaultMapping); //Enregistrer le mapping par défaut des champs        
    }
    
    public function initObjects() {
        $this->Ad->createAd();
        $this->Agent->createAgent();
        $this->Agency->createAgency();
    }
    
    public function initAdmin() {
        $this->EditAd->addMetaBoxes();
        $this->EditAgent->addMetaBoxes();
        $this->EditAgency->addMetaBox();
        $this->Options->optionsPageInit();
    }
        
    public function registerPluginStylesAdmin() {
        $postType = get_current_screen()->post_type;
        $base = get_current_screen()->base;
        if($postType === "ad" && $base === "post") {
            wp_register_style("editAd", plugins_url("bth/includes/css/editAd.css"), array(), PLUGIN_RE_VERSION);
            wp_register_style("autocompleteAddress", plugins_url("bth/includes/css/autocompleteAddress.css"), array(), PLUGIN_RE_VERSION);

            wp_enqueue_style("editAd");
            wp_enqueue_style("autocompleteAddress");
        }else if($postType === "ad" && $base === "ad_page_bthoptions") {
            wp_register_style("options", plugins_url("bth/includes/css/options.css"), array(), PLUGIN_RE_VERSION);
            
            wp_enqueue_style("options");
        }else if($postType === "ad" && $base === "edit-tags") {
            wp_register_style("editTagsAd", plugins_url("bth/includes/css/editTagsAd.css"), array(), PLUGIN_RE_VERSION);
            
            wp_enqueue_style("editTagsAd");
        }else if($postType === "agent" && $base === "post") {
            wp_register_style("editAgent", plugins_url("bth/includes/css/editAgent.css"), array(), PLUGIN_RE_VERSION);
            
            wp_enqueue_style("editAgent");
        }else if($postType === "agency" && $base === "post") {
            wp_register_style("editAgency", plugins_url("bth/includes/css/editAgency.css"), array(), PLUGIN_RE_VERSION);
            wp_register_style("autocompleteAddress", plugins_url("bth/includes/css/autocompleteAddress.css"), array(), PLUGIN_RE_VERSION);

            wp_enqueue_style("editAgency");
            wp_enqueue_style("autocompleteAddress");
        }
    }
    
    public function registerPluginScriptsAdmin() {
        $postType = get_current_screen()->post_type;
        $base = get_current_screen()->base;
        
        if($base === "post") {
            if($postType === "ad") {
                wp_enqueue_media();
                wp_register_script("mediaButton", plugins_url(PLUGIN_RE_NAME."/includes/js/editAd.js"), array('jquery'), PLUGIN_RE_VERSION);
                wp_register_script("autocompleteAddress", plugins_url(PLUGIN_RE_NAME."/includes/js/autocompleteAddress.js"), array('jquery'), PLUGIN_RE_VERSION);
                wp_register_script("reloadAgents", plugins_url(PLUGIN_RE_NAME."/includes/js/reloadAgents.js"), array('jquery'), PLUGIN_RE_VERSION);

                wp_enqueue_script("mediaButton");
                wp_enqueue_script("autocompleteAddress");
                wp_enqueue_script("reloadAgents");
                
                wp_add_inline_script("reloadAgents", 'let pluginName="'.PLUGIN_RE_NAME.'";');
            }else if($postType === "agency") {
                wp_register_script("autocompleteAddress", plugins_url(PLUGIN_RE_NAME."/includes/js/autocompleteAddress.js"), array('jquery'), PLUGIN_RE_VERSION);
                wp_enqueue_script("autocompleteAddress");
            }
        }else if($base === "ad_page_bthoptions") {
            wp_register_script("options", plugins_url(PLUGIN_RE_NAME."/includes/js/options.js"), array(), PLUGIN_RE_VERSION);

            wp_enqueue_script("options");
        }      
    }
      
    public function completeMenu() {
        $parentSlug = "edit.php?post_type=ad";
        /*add_submenu_page(
            $parentSlug, //Parent slug
            'Accueil', //Page title
            'Accueil', //Menu title
            'manage_options', // capability
            "bthwelcome", // menu_slug
            array($this->Ad, "showPage"), // function
            0 // position
        );*/
        add_submenu_page(
            $parentSlug, //Parent slug
            'Importez les annonces', //Page title
            'Importez les annonces', //Menu title
            'manage_options', //Capability
            "bthimport", //Menu slug
            array($this->Import, "showPage"), //Function
            3 //Position
        );
        add_submenu_page(
            $parentSlug, //Parent slug
            'Exportez les annonces', //Page title
            'Exportez les annonces', //Menu title
            'manage_options', //Capability
            "bthexport", //Menu slug
            array($this->Export, "showPage"), //Function
            4 //Position
        );
        add_submenu_page(
            $parentSlug, //Parent slug
            'Options', //Page title
            'Options', //Menu title
            'manage_options', //Capability
            "bthoptions", //Menu slug
            array($this->Options, "showPage"), //Function
            5 //Position
        );
    }
    
    public function maxLengthTitle($title) {
        $postType = get_current_screen()->post_type;
        if($postType === "ad") {
            $title = substr($title, 0, 64);
        }
        
        return $title;
    }
    
    public function changeTitle($title) {
        $postType = get_current_screen()->post_type;
        if($postType === "agent") {
            $title = "Nom et prénom de l'agent";
        }else if($postType === "agency") {
            $title = "Nom de l'agence";
        }
        
        return $title;
    }
	
}
new Bth;

?>