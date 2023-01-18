<?php
/*
Plugin Name: bth
Description: Créez des annonces immobilières et exportez les.
Version: Dev
Author: Vincent Bourdon
License:  GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

require_once("bth.php"); //Pour installer le plugin depuis l'interface

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
        
        add_action("init", array($this, "loadLanguages"));  /* VOIR POUR LIMITER L'EXECUTION DES ACTIONS ET FILTRES */
        add_action("init", array($this, "initObjects"));

	register_activation_hook(__FILE__, array($this, "activationPlugin")); //A l'activation du plugin...
        //Ajouter à la désactivation
        
        add_action("admin_init", array($this, "initAdmin"));
        
        add_action("admin_menu", array($this, "completeMenu"));
  
        add_action("admin_enqueue_scripts", array($this, "registerPluginStylesAdmin"));
        add_action("admin_enqueue_scripts", array($this, "registerPluginScriptsAdmin"));        
        
        add_action("wp_dashboard_setup", array($this->Import, "widgetImport"));
        add_action("wp_dashboard_setup", array($this->Export, "widgetExport"));
        
        add_action("save_post_re-ad", array($this->EditAd, "savePost"), 10, 2);
        add_action("save_post_agent", array($this->EditAgent, "savePost"), 10, 2);
        add_action("save_post_agency", array($this->EditAgency, "savePost"), 10, 2);
        
        add_action("restrict_manage_posts", array($this->Ad, "filterAdsByTaxonomies"));
        
        add_action("restrict_manage_posts", array($this->Agent, "agentFilterByAgency"));

        add_action("admin_init", array($this->Agent, "publicQueryAgentPostParent"));
        add_filter("manage_agent_posts_columns", array($this->Agent, "customAgentSortableColumns"));
        add_action("manage_agent_posts_custom_column" , array($this->Agent, "selectCustomAgentColumn"), 10, 2);
        add_filter("manage_edit-agent_sortable_columns", array($this->Agent, "customAgentColumn"));
        
        add_action("created_term", array($this->Ad, "termTypePropertyUpdate"), 10, 3);
        add_action("edit_term", array($this->Ad, "termTypePropertyUpdate"), 10, 3);
        add_action("adTypeProperty_add_form_fields", array($this->Ad, "typePropertyCreateFields"));
        add_action("adTypeProperty_edit_form_fields", array($this->Ad, "typePropertyEditFields"), 10, 2);
        add_filter("manage_adTypeProperty_custom_column", array($this->Ad, "typePropertyHabitableColumn"), 15, 3);
        add_filter("manage_edit-adTypeProperty_columns", array($this->Ad, "typePropertyColumns"));

        
        add_filter("template_include", array($this->Ad, "templatePostAd"), 1);
        add_filter("template_include", array($this->Agency, "templatePostAgency"), 1);
        add_filter("template_include", array($this->Agent, "templatePostAgent"), 1);

        add_filter("enter_title_here", array($this, "changeTitle"));
        add_filter("pre_get_posts", array($this, "convertIdToTermInQuery"));
        add_filter("pre_get_posts", array($this->Ad, "searchAds"));
        
        add_action("all_admin_notices", array($this->Options, "tabsOption"));
        
        add_action("wp_ajax_nopriv_import", array($this->Import, "startImport")); //Cron
        
        add_action("widgets_init", array($this, "removeSearchWidget"));
        
        add_filter("wp_enqueue_scripts", array($this, "updateHeader"));
                
        SELF::defineGlobalConsts();
    }
    
    public function removeSearchWidget() {
	unregister_widget("WP_Widget_Search");
    }   
    
    public function updateHeader() {
        global $post_type;
        global $pagenow;
        if($post_type === "re-ad" || ($pagenow === "index.php" && empty($post_type))) {
            wp_register_script("addSearchBarAd", plugins_url(PLUGIN_RE_NAME."/includes/js/templates/searchBars/addSearchBarAd.js"), array("jquery", "jquery-ui-slider", "jquery-ui-autocomplete"), PLUGIN_RE_VERSION, false);
            $variables = array(
                "filters" => __("FILTERS", "retxtdom"),
                "searchBarURL" => plugin_dir_url(__FILE__)."/templates/searchBars/searchBarAd.php",
                "autocompleteURL" => plugin_dir_url(__FILE__)."/includes/js/ajax/autocompleteAddress.js",
                "getAddressDataURL" => plugin_dir_url(__FILE__)."/includes/php/getAddressData.php"
            );
            wp_localize_script("addSearchBarAd", "variables", $variables);
            wp_enqueue_script("addSearchBarAd");

            wp_register_style("searchBarAd", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/searchBars/searchBarAd.css"), array(), PLUGIN_RE_VERSION);
            wp_enqueue_style("searchBarAd");
            
            wp_register_style("autocompleteAddress", plugins_url(PLUGIN_RE_NAME."/includes/css/others/autocompleteAddress.css"), array(), PLUGIN_RE_VERSION);
            wp_enqueue_style("autocompleteAddress");
        }
    }
    
    private static function defineGlobalConsts() {
        define("PLUGIN_RE_NAME", "BTH");
        define("PLUGIN_RE_VERSION", "dev");
    }

    
    public function activationPlugin() { //A l'activation du plugin
        //Enregistrer les valeurs par défaut
        
        $defaultValuesLanguage = array(
            "language" => "en",
            "currency" => "$"
        );
        update_option(PLUGIN_RE_NAME."OptionsLanguage", $defaultValuesLanguage); 
        
        $defaultValuesImports = array(
            "maxSavesImports"       => 2,
            "maxDim"                => 1024,
            "qualityPictures"       => 85,
            "templateUsedImport"    => "stdxml"
        );
        update_option(PLUGIN_RE_NAME."OptionsImports", $defaultValuesImports); 
        
        $defaultValuesExports = array(
            "templateUsedExport"    => "stdxml",
            "maxSavesExports"       => 1
        );
        update_option(PLUGIN_RE_NAME."OptionsExports", $defaultValuesExports); 
        
        $defaultValuesEmail = array(
            "emailError"    => wp_get_current_user()->user_email,
            "emailAd"       => wp_get_current_user()->user_email
        );
        update_option(PLUGIN_RE_NAME."OptionsEmail", $defaultValuesEmail); 
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
    
    public function loadLanguages() {
        load_plugin_textdomain("retxtdom", false, dirname(plugin_basename(__FILE__)).'/languages/');
    }
        
    public function registerPluginStylesAdmin() {
        $postType = get_current_screen()->post_type;
        $base = get_current_screen()->base;
        if($postType === "re-ad" && $base === "post") {
            wp_register_style("editAd", plugins_url(PLUGIN_RE_NAME."/includes/css/edits/editAd.css"), array(), PLUGIN_RE_VERSION);
            wp_register_style("autocompleteAddress", plugins_url(PLUGIN_RE_NAME."/includes/css/others/autocompleteAddress.css"), array(), PLUGIN_RE_VERSION);

            wp_enqueue_style("editAd");
            wp_enqueue_style("autocompleteAddress");
        }else if($postType === "re-ad" && $base === "re-ad_page_bthoptions") {
            wp_register_style("options", plugins_url(PLUGIN_RE_NAME."/includes/css/others/options.css"), array(), PLUGIN_RE_VERSION);
            
            wp_enqueue_style("options");
        }else if($postType === "re-ad" && $base === "edit-tags") {
            wp_register_style("editTagsAd", plugins_url(PLUGIN_RE_NAME."/includes/css/edits/editTagsAd.css"), array(), PLUGIN_RE_VERSION);
            
            wp_enqueue_style("editTagsAd");
        }else if($postType === "agent" && $base === "post") {
            wp_register_style("editAgent", plugins_url(PLUGIN_RE_NAME."/includes/css/edits/editAgent.css"), array(), PLUGIN_RE_VERSION);
            
            wp_enqueue_style("editAgent");
        }else if($postType === "agency" && $base === "post") {
            wp_register_style("editAgency", plugins_url(PLUGIN_RE_NAME."/includes/css/edits/editAgency.css"), array(), PLUGIN_RE_VERSION);
            wp_register_style("autocompleteAddress", plugins_url(PLUGIN_RE_NAME."/includes/css/others/autocompleteAddress.css"), array(), PLUGIN_RE_VERSION);

            wp_enqueue_style("editAgency");
            wp_enqueue_style("autocompleteAddress");
        }
    }
    
    public function registerPluginScriptsAdmin() { //Mettre dans une autre classe et créer des fonctions pour simplifier la gestion des ressources ?
        $postType = get_current_screen()->post_type;
        $base = get_current_screen()->base;
                
        if($base === "post") {
            if($postType === "re-ad") {
                wp_enqueue_media();
                wp_register_script("mediaButton", plugins_url(PLUGIN_RE_NAME."/includes/js/edits/editAd.js"), array('jquery'), PLUGIN_RE_VERSION);
                wp_register_script("autocompleteAddress", plugins_url(PLUGIN_RE_NAME."/includes/js/ajax/autocompleteAddress.js"), array('jquery'), PLUGIN_RE_VERSION, true);
                wp_register_script("reloadAgents", plugins_url(PLUGIN_RE_NAME."/includes/js/ajax/reloadAgents.js"), array('jquery'), PLUGIN_RE_VERSION, true);

                wp_enqueue_script("mediaButton");
                wp_enqueue_script("autocompleteAddress");
                wp_enqueue_script("reloadAgents");
                
                wp_add_inline_script("autocompleteAddress", 'var URLGetAddressDataFile="'.plugins_url(PLUGIN_RE_NAME."/includes/php/getAddressData.php").'";');
                wp_add_inline_script("reloadAgents", 'var pluginName="'.PLUGIN_RE_NAME.'";');
            }else if($postType === "agency") {
                wp_register_script("autocompleteAddress", plugins_url(PLUGIN_RE_NAME."/includes/js/ajax/autocompleteAddress.js"), array('jquery'), PLUGIN_RE_VERSION, true);
                wp_enqueue_script("autocompleteAddress");
                wp_add_inline_script("autocompleteAddress", 'var URLGetAddressDataFile="'.plugins_url(PLUGIN_RE_NAME."/includes/php/getAddressData.php").'";');
            }else if($postType === "agent") {
                wp_register_script("reloadAgencies", plugins_url(PLUGIN_RE_NAME."/includes/js/ajax/reloadAgencies.js"), array("jquery"), PLUGIN_RE_VERSION, true);
                wp_enqueue_script("reloadAgencies");
                wp_add_inline_script("reloadAgencies", 'var pluginName="'.PLUGIN_RE_NAME.'";');
            }
        }else if($base === "re-ad_page_bthoptions") {
            wp_register_script("options", plugins_url(PLUGIN_RE_NAME."/includes/js/others/options.js"), array("jquery"), PLUGIN_RE_VERSION, true);
            $translations = array(
                "mainFeatures" => __("Main characteristics", "retxtdom"),
                "complementaryFeatures" => __("Complementary characteristics", "retxtdom")
            );
            wp_localize_script("options", "translations", $translations);
            wp_enqueue_script("options");
        }    
    }
      
    public function completeMenu() {
        $parentSlug = "edit.php?post_type=re-ad";
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
            "Importez les annonces", //Page title
            "Importez les annonces", //Menu title
            "manage_options", //Capability
            "bthimport", //Menu slug
            array($this->Import, "showPage"), //Function
            3 //Position
        );
        add_submenu_page(
            $parentSlug, //Parent slug
            "Exportez les annonces", //Page title
            "Exportez les annonces", //Menu title
            "manage_options", //Capability
            "bthexport", //Menu slug
            array($this->Export, "showPage"), //Function
            4 //Position
        );
        add_submenu_page(
            $parentSlug, //Parent slug
            "Options", //Page title
            "Options", //Menu title
            "manage_options", //Capability
            "bthoptions", //Menu slug
            array($this->Options, "showPage"), //Function
            5 //Position
        );
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
    
    public function convertIdToTermInQuery($query) {
        global $pagenow;            
        global $typenow;

        if(is_admin() && $pagenow == "edit.php") {

            $taxonomies = get_taxonomies(["object_type" => [$typenow]]);

            foreach($taxonomies as $taxonomy) {
                if(isset($_GET[$taxonomy]) && is_numeric($_GET[$taxonomy]) && $_GET[$taxonomy] != 0) {
                    $taxQuery = array(
                            "taxonomy" => $taxonomy,
                            "terms"    => array($_GET[$taxonomy]),
                            "field"    => "id",
                            "operator" => "IN",
                    );
                    $query->tax_query->queries[] = $taxQuery; 
                    $query->query_vars["tax_query"] = $query->tax_query->queries;
                }
            }
        }

    }
	
}
new Bth;