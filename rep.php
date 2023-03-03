<?php
/*
Plugin Name: REP
Description: Manage your real estate ads on WordPress
Version: Dev
Author: Vincent Bourdon
License:  GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

require_once("rep.php"); //Install plugin from interface

/*
 * 
 * Main plugin's class
 * 
 */
class Rep {
    
    /*
     * When the class is instantiate
     */
    public function __construct() {
        
        $this->defineGlobalConsts();
        
        $this->requireClasses();
        
        register_activation_hook(__FILE__, array($this, "onPluginActivation"));
        register_deactivation_hook(__FILE__, array($this, "onPluginDeactivation"));
        
        $this->actions();
        
        $this->filters();
    }
    
    /*
     * Initialize the constants
     */
    private function defineGlobalConsts() {
        define("PLUGIN_RE_NAME", "REP");
        define("PLUGIN_RE_VERSION", "dev");
        define("PLUGIN_RE_PATH", WP_PLUGIN_DIR.'/'.PLUGIN_RE_NAME.'/');
    }
    
    /*
     * Fetch the classes and instantiate them
     */
    public function requireClasses() {
        require_once("customPosts/Ad.php");
        require_once("customPosts/Agent.php");
        require_once("customPosts/Agency.php");
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
    }
    
    /*
     * Add the actions
     */
    private function actions() {
        //Load languages
        add_action("init", array($this, "loadLanguages"));

        //Initialize custom posts
        add_action("init", array($this, "initCustomPosts"));

        //Initialize the admin area
        add_action("admin_init", array($this, "initAdmin"));

        //Add menu items to the WordPress admin dashboard
        add_action("admin_menu", array($this, "completeMenu"));

        //Register plugin styles for the admin area
        add_action("admin_enqueue_scripts", array($this, "registerPluginStylesAdmin"));

        //Register plugin scripts for the admin area
        add_action("admin_enqueue_scripts", array($this, "registerPluginScriptsAdmin"));

        //Remove the default search widget
        add_action("widgets_init", array($this, "removeSearchWidget"));

        //Add tabs to the admin notice area
        add_action("all_admin_notices", array($this->Options, "tabsOption"));

        //Add widgets to the WordPress dashboard
        add_action("wp_dashboard_setup", array($this->Import, "widgetImport"));
        add_action("wp_dashboard_setup", array($this->Export, "widgetExport"));

        //Save custom post types
        add_action("save_post_re-ad", array($this->EditAd, "savePost"), 10, 2);
        add_action("save_post_agent", array($this->EditAgent, "savePost"), 10, 2);
        add_action("save_post_agency", array($this->EditAgency, "savePost"), 10, 2);

        //Filter custom post types by taxonomies
        add_action("restrict_manage_posts", array($this->Ad, "filterAdsByTaxonomies"));
        add_action("restrict_manage_posts", array($this->Agent, "agentFilterByAgency"));

        //Set up public query vars for agent post type
        add_action("admin_init", array($this->Agent, "publicQueryAgentPostParent"));

        //Select custom columns for the agent post type
        add_action("manage_agent_posts_custom_column" , array($this->Agent, "selectCustomAgentColumn"), 10, 2);

        //Update term meta for custom taxonomy
        add_action("created_term", array($this->Ad, "termTypePropertyUpdate"), 10, 3);
        add_action("edit_term", array($this->Ad, "termTypePropertyUpdate"), 10, 3);

        //Add fields to custom taxonomy creation/editing screens
        add_action("adTypeProperty_add_form_fields", array($this->Ad, "typePropertyCreateFields"));
        add_action("adTypeProperty_edit_form_fields", array($this->Ad, "typePropertyEditFields"), 10, 2);

        //Import data via AJAX
        add_action("wp_ajax_nopriv_import", array($this->Import, "startImport"));

        //Register custom API route
        add_action("rest_api_init", array($this, "registerRouteCustomAPIs"));
        
        //Add actions (links) to the plugin row in plugins.php
        add_action("plugin_action_links_" . plugin_basename( __FILE__ ), array($this, "addActionsPluginRow"));
    }
    
    /*
     * Add the filters
     */
    private function filters() {
        //Change the default text shown in the title field of a WordPress post editor
        add_filter("enter_title_here", array($this, "changeTitle"));
        
        //Modify the query before it is executed, in order to convert post ID values into their corresponding taxonomy terms
        add_filter("pre_get_posts", array($this, "convertIdToTermInQuery")); 
        
        //Modify the query before it is executed, in order to include custom search functionality for the ads
        add_filter("pre_get_posts", array($this->Ad, "searchAds")); 
        
        //Add custom styles or scripts to the WordPress header section
        add_filter("wp_enqueue_scripts", array($this, "updateHeader"));
        
        //Add or modify the columns shown in the WordPress admin table for the agent custom post type
        add_filter("manage_agent_posts_columns", array($this->Agent, "customAgentSortableColumns")); 
        
        //Make the columns added in the previous filter sortable.
        add_filter("manage_edit-agent_sortable_columns", array($this->Agent, "customAgentColumn")); 
        
        //Add custom columns to the WordPress admin table for the adTypeProperty custom post type
        add_filter("manage_adTypeProperty_custom_column", array($this->Ad, "typePropertyHabitableColumn"), 15, 3); 
        
        //Define the columns to be displayed in the WordPress admin table for the "adTypeProperty" custom post type
        add_filter("manage_edit-adTypeProperty_columns", array($this->Ad, "typePropertyColumns")); 
        
        //Modify the template file used to display a single or archive ad post type
        add_filter("template_include", array($this->Ad, "templatePostAd"), 1); 
        
        //Modify the template file used to display a single agency post type
        add_filter("template_include", array($this->Agency, "templatePostAgency"), 1); 
        
        //Modify the template file used to display a single agent post type
        add_filter("template_include", array($this->Agent, "templatePostAgent"), 1); 
    }
    
    /*
     * When the plugin is activated
     */
    public function onPluginActivation() {
        $this->defaultOptionsValues();
        $this->rewriteFlush(); //Update the permalinks structure
    }
    
    public function onPluginDeactivation() {
        //Ajouter option pour supprimer options ou non quand on désactive le plugin
        flush_rewrite_rules(); //Update the permalinks structure
    }

    /*
     * Save the default options values
     */
    private function defaultOptionsValues() {
        
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
        
        $defaultValuesApis = array(
            "apiUsed" => "govFr",
            "apiLimitNbRequests" => true,
            "apiMaxNbRequests" => 300,
            "apiAdminAreaLvl1" => false,
            "apiAdminAreaLvl2" => false
        );
        update_option(PLUGIN_RE_NAME."OptionsApis", $defaultValuesApis); 
    }
    
    private function rewriteFlush() {
        $this->Ad->createAd();
        flush_rewrite_rules();
    }
    
    /*
     * Load languages
     */
    public function loadLanguages() {
        load_plugin_textdomain("retxtdom", false, dirname(plugin_basename(__FILE__)).'/languages/');
    }
    
    /*
     * Initialize custom posts
     */    
    public function initCustomPosts() {
        $this->Ad->createAd();
        $this->Agent->createAgent();
        $this->Agency->createAgency();
    }
    
    /*
     * Initialize the admin area
     */
    public function initAdmin() {
        $this->EditAd->addMetaBoxes();
        $this->EditAgent->addMetaBoxes();
        $this->EditAgency->addMetaBox();
        $this->Options->optionsPageInit();
    }
    
    /*
     * Add menu items to the WordPress admin dashboard
     */
    public function completeMenu() {
        $parentSlug = "edit.php?post_type=re-ad";
        add_submenu_page(
            $parentSlug, //Parent slug
            "Importez les annonces", //Page title
            "Importez les annonces", //Menu title
            "manage_options", //Capability
            "repimport", //Menu slug
            array($this->Import, "showPage"), //Callback
            2 //Position
        );
        add_submenu_page(
            $parentSlug, //Parent slug
            "Exportez les annonces", //Page title
            "Exportez les annonces", //Menu title
            "manage_options", //Capability
            "repexport", //Menu slug
            array($this->Export, "showPage"), //Callback
            3 //Position
        );
        add_submenu_page(
            $parentSlug, //Parent slug
            "Options", //Page title
            "Options", //Menu title
            "manage_options", //Capability
            "repoptions", //Menu slug
            array($this->Options, "showPage"), //Callback
            4 //Position
        );
    }   
    
    /*
     * Register plugin styles for the admin area
     */
    public function registerPluginStylesAdmin() {
        $postType = get_current_screen()->post_type;
        $base = get_current_screen()->base;
        if($postType === "re-ad" && $base === "post") {
            wp_register_style("editAd", plugins_url(PLUGIN_RE_NAME."/includes/css/edits/editAd.css"), array(), PLUGIN_RE_VERSION);
            wp_register_style("autocompleteAddress", plugins_url(PLUGIN_RE_NAME."/includes/css/others/autocompleteAddress.css"), array(), PLUGIN_RE_VERSION);

            wp_enqueue_style("editAd");
            wp_enqueue_style("autocompleteAddress");
        }else if($postType === "re-ad" && $base === "re-ad_page_repoptions") {
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
    
    /*
     * Register plugin scripts for the admin area
     */
    public function registerPluginScriptsAdmin() {
        $postType = get_current_screen()->post_type;
        $base = get_current_screen()->base;
                
        if($base === "post") {
            if($postType === "re-ad") {
                wp_enqueue_media();
                wp_register_script("editAd", plugins_url(PLUGIN_RE_NAME."/includes/js/edits/editAd.js"), array('jquery'), PLUGIN_RE_VERSION);
                $translations = array(
                    "delete" => __("Delete", "retxtdom")
                );
                wp_localize_script("editAd", "translations", $translations);
                wp_enqueue_script("editAd");
                
                wp_register_script("autocompleteAddress", plugins_url(PLUGIN_RE_NAME."/includes/js/ajax/autocompleteAddress.js"), array('jquery'), PLUGIN_RE_VERSION, true);
                $variablesAddress = array(
                    "getAddressDataURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/address"),
                );
                wp_register_script("reloadAgents", plugins_url(PLUGIN_RE_NAME."/includes/js/ajax/reloadAgents.js"), array('jquery'), PLUGIN_RE_VERSION, true);
                $variablesAgents = array(
                    "getAgentsURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/agents"),
                );
                wp_localize_script("autocompleteAddress", "variables", $variablesAddress);
                wp_localize_script("reloadAgents", "variables", $variablesAgents);
                wp_enqueue_script("autocompleteAddress");
                wp_enqueue_script("reloadAgents");
            }else if($postType === "agency") {
                wp_register_script("autocompleteAddress", plugins_url(PLUGIN_RE_NAME."/includes/js/ajax/autocompleteAddress.js"), array('jquery'), PLUGIN_RE_VERSION, true);
                wp_enqueue_script("mediaButton");
                $variables = array(
                    "getAddressDataURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/address"),
                );
                wp_localize_script("autocompleteAddress", "variables", $variables);
                wp_enqueue_script("autocompleteAddress");
            }else if($postType === "agent") {
                wp_register_script("reloadAgencies", plugins_url(PLUGIN_RE_NAME."/includes/js/ajax/reloadAgencies.js"), array("jquery"), PLUGIN_RE_VERSION, true);
                $variables = array(
                    "getAgenciesURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/agencies"),
                );
                wp_localize_script("reloadAgencies", "variables", $variables);
                wp_enqueue_script("reloadAgencies");
            }
        }else if($base === "re-ad_page_repoptions") {
            wp_register_script("options", plugins_url(PLUGIN_RE_NAME."/includes/js/others/options.js"), array("jquery"), PLUGIN_RE_VERSION, true);
            $translations = array(
                "mainFeatures" => __("Main characteristics", "retxtdom"),
                "complementaryFeatures" => __("Complementary characteristics", "retxtdom")
            );
            wp_localize_script("options", "translations", $translations);
            wp_enqueue_script("options");
        }    
    }
    
    /*
     * Remove the default search widget
     */
    public function removeSearchWidget() {
	unregister_widget("WP_Widget_Search");
    }   
    
    /*
     * Register custom API route
     */
    public function registerRouteCustomAPIs() {
        require_once("models/ajax/getAddressData.php");
        register_rest_route(PLUGIN_RE_NAME."/v1", "address", array( 
            "methods" => "GET",
            "callback" => "getAddressData",
            "permission_callback" => array($this, "permissionCallbackGetAddressData")
        ));
        
        require_once("models/ajax/getAgencies.php");
        register_rest_route(PLUGIN_RE_NAME."/v1", "agencies", array( 
            "methods" => "GET",
            "callback" => "getAgencies",
            "permission_callback" => function() {
                $idUser = apply_filters("determine_current_user", false);
                wp_set_current_user($idUser);
                return current_user_can("edit_others_posts");
            }
        ));
        
        require_once("models/ajax/getAgents.php");
        register_rest_route(PLUGIN_RE_NAME."/v1", "agents", array( 
            "methods" => "GET",
            "callback" => "getAgents",
            "permission_callback" => function() {
                $idUser = apply_filters("determine_current_user", false);
                wp_set_current_user($idUser);
                return current_user_can("edit_others_posts");
            }
        ));
    }
    
    /*
     * Check if the client can use the getAddress API
     * Update the logs
     */
    public function permissionCallbackGetAddressData() {
        $idUser = apply_filters("determine_current_user", false);
        wp_set_current_user($idUser);
        $apisOptions = get_option(PLUGIN_RE_NAME."OptionsApis");
        if(current_user_can("edit_others_posts") || !boolval($apisOptions["apiLimitNbRequests"])) {
            $clientAllowed = true;
        }else{
            $logsAPI = get_option(PLUGIN_RE_NAME."LogsAPIIPNbRequests");
            $date = date("m-d-Y");
            $maxRequests = intval($apisOptions["apiMaxNbRequests"]);
            $clientIP = $_SERVER["REMOTE_ADDR"]; 

            if($logsAPI !== false) {
                $newLogsAPI = array($date=>$logsAPI[$date]);
                $IPs = $newLogsAPI[$date];
                $clientAllowed = !isset($IPs[$clientIP]) || isset($IPs[$clientIP]) && $IPs[$clientIP] < $maxRequests;
                if($clientAllowed) {
                    if(!isset($IPs[$clientIP])) {
                        $newLogsAPI[$date][$clientIP] = 1;
                    }else{
                        $newLogsAPI[$date][$clientIP]++;
                    }
                }      
            }else{
                $newLogsAPI = array($date=>array($clientIP=>1));
                $clientAllowed = true;
            }
            update_option(PLUGIN_RE_NAME."LogsAPIIPNbRequests", $newLogsAPI);
        }
        return $clientAllowed;
    }
    
    /*
     * Change the default text shown in the title field of a WordPress post editor
     */
    public function changeTitle($title) {
        $postType = get_current_screen()->post_type;
        if($postType === "agent") {
            $title = "Nom et prénom de l'agent";
        }else if($postType === "agency") {
            $title = "Nom de l'agence";
        }       
        return $title;
    }
    
    /*
     * Modify the query before it is executed, in order to convert post ID values into their corresponding taxonomy terms
     */
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
    
    /*
     * Add custom styles or scripts to the WordPress header section
     */
    public function updateHeader() {
        global $post_type;
        global $pagenow;
        if($post_type === "re-ad" || ($pagenow === "index.php" && empty($post_type))) {         
            wp_register_script("addSearchBarAd", plugins_url(PLUGIN_RE_NAME."/includes/js/templates/searchBars/addSearchBarAd.js"), array("jquery", "jquery-ui-slider", "jquery-ui-autocomplete"), PLUGIN_RE_VERSION, false);
            $variables = array(
                "filters" => __("FILTERS", "retxtdom"),
                "searchBarURL" => plugin_dir_url(__FILE__)."templates/searchBars/searchBarAd.php",
                "autocompleteURL" => plugin_dir_url(__FILE__)."includes/js/ajax/autocompleteAddress.js",
                "getAddressDataURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/address"),
                "nonce" => wp_create_nonce("searchNonce")
            );
            wp_localize_script("addSearchBarAd", "variables", $variables);
            wp_enqueue_script("addSearchBarAd");

            wp_register_style("searchBarAd", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/searchBars/searchBarAd.css"), array(), PLUGIN_RE_VERSION);
            wp_enqueue_style("searchBarAd");
            
            wp_register_style("autocompleteAddress", plugins_url(PLUGIN_RE_NAME."/includes/css/others/autocompleteAddress.css"), array(), PLUGIN_RE_VERSION);
            wp_enqueue_style("autocompleteAddress");
        }
    }
    
    /*
     * Add actions (links) to the plugin row in plugins.php
     */
    public function addActionsPluginRow($links) {
       $links = array_merge($links, array(
            '<a href="'.esc_url(admin_url("/edit.php?post_type=re-ad&page=".strtolower(PLUGIN_RE_NAME)."options")).'">'.__("Options", "retxtdom")."</a>"
        ));
	return $links;
    }
    
}
new Rep;