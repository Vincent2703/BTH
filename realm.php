<?php
/*
Plugin Name: REALM
Description: Manage your real estate ads on WordPress
Version: Dev
Author: Vincent Bourdon
License:  GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}

require_once("realm.php"); //Install plugin from interface

/*
 * 
 * Main plugin's class
 * 
 */
class Realm {
    
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
        define("PLUGIN_RE_NAME", "realm");
        define("PLUGIN_RE_VERSION", "dev");
        define("PLUGIN_RE_PATH", WP_PLUGIN_DIR.'/'.PLUGIN_RE_NAME.'/');
    }
    
    /*
     * Fetch the classes and instantiate them
     */
    public function requireClasses() {
        //Custom posts
        require_once("customPosts/Ad.php");
        require_once("customPosts/Agent.php");
        require_once("customPosts/Agency.php");
        
        //Controllers
        require_once("controllers/Options.php");
        require_once("controllers/EditAd.php");
        require_once("controllers/EditAgent.php");
        require_once("controllers/EditAgency.php");
        require_once("controllers/Export.php");
        require_once("controllers/Import.php");
        
        //Models
        require_once("models/searches/GetAds.php");
        
        
        $this->Ad           = new REALM_Ad;
        $this->Agent        = new REALM_Agent;
        $this->Agency       = new REALM_Agency;
        
        $this->Options      = new REALM_Options;
        $this->EditAd       = new REALM_EditAd;
        $this->EditAgent    = new REALM_EditAgent;
        $this->EditAgency   = new REALM_EditAgency;
        $this->Export       = new REALM_Export;
        $this->Import       = new REALM_Import;
        
        $this->GetAds       = new REALM_GetAds;   
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
        
        //add_action("widgets_init", array($this->SearchBar, "searchBarWidget"));

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
        
        //Display notices
        add_action("admin_notices", array($this, "displayNotices"));

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
        add_filter("pre_get_posts", array($this->GetAds, "getAds")); 
        
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
        //TODO : Ajouter option pour supprimer options ou non quand on désactive le plugin
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
        update_option(PLUGIN_RE_NAME."OptionsGeneral", $defaultValuesLanguage); 
        
        $defaultValuesImports = array(
            "maxSavesImports"       => 2,
            "maxDim"                => 1024,
            "qualityPictures"       => 85,
            //"templateUsedImport"    => "stdxml"
        );
        update_option(PLUGIN_RE_NAME."OptionsImports", $defaultValuesImports); 
        
        $defaultValuesExports = array(
            //"templateUsedExport"    => "stdxml",
            "maxSavesExports"       => 1
        );
        update_option(PLUGIN_RE_NAME."OptionsExports", $defaultValuesExports); 
        
        $defaultValuesEmail = array(
            //"emailError"    => wp_get_current_user()->user_email,
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
            __("Import ads", "retxtdom"), //Page title
            __("Import ads", "retxtdom"), //Menu title
            "manage_options", //Capability
            PLUGIN_RE_NAME."import", //Menu slug
            array($this->Import, "showPage"), //Callback
            2 //Priority
        );
        add_submenu_page(
            $parentSlug, //Parent slug
            __("Export ads", "retxtdom"), //Page title
            __("Export ads", "retxtdom"), //Menu title
            "manage_options", //Capability
            PLUGIN_RE_NAME."export", //Menu slug
            array($this->Export, "showPage"), //Callback
            3 //Priority
        );
        add_submenu_page(
            $parentSlug, //Parent slug
            __("Options", "retxtdom"), //Page title
            __("Options", "retxtdom"), //Menu title
            "manage_options", //Capability
            PLUGIN_RE_NAME."options", //Menu slug
            array($this->Options, "showPage"), //Callback
            4 //Priority
        );
    }   
    
    /*
     * Register plugin styles for the admin area
     */
    public function registerPluginStylesAdmin() {
        $screen = get_current_screen();
        $postType = $screen->post_type;
        $base = $screen->base;
        
        $styleSheets = array(
            "re-ad" => array(
                "post" => array(
                    "editAd" => "/includes/css/edits/editAd.css",
                    "autocompleteAddress" => "/includes/css/others/autocompleteAddress.css"
                ),
                "re-ad_page_".PLUGIN_RE_NAME."options" => array(
                    "options" => "/includes/css/others/options.css"
                ),
                "edit-tags" => array(
                    "editTagsAd" => "/includes/css/edits/editTagsAd.css"
                ),
                "re-ad_page_".PLUGIN_RE_NAME."import" => array(
                    "import" => "/includes/css/others/import.css"
                )
            ),
            "agent" => array(
                "post" => array(
                    "editAgent" => "/includes/css/edits/editAgent.css"
                )
            ),
            "agency" => array(
                "post" => array(
                    "editAgency" => "/includes/css/edits/editAgency.css",
                    "autocompleteAddress" => "/includes/css/others/autocompleteAddress.css"
                )
            )
        );
        
        $stylesToRegister = isset($styleSheets[$postType][$base])?$styleSheets[$postType][$base]:array();
        foreach($stylesToRegister as $name => $path) {
            wp_register_style($name, plugins_url(PLUGIN_RE_NAME.$path), array(), PLUGIN_RE_VERSION);
            wp_enqueue_style($name);
        }
    }
    
    /*
     * Register plugin scripts for the admin area
     */
    public function registerPluginScriptsAdmin() {
        $screen = get_current_screen();
        $postType = $screen->post_type;
        $base = $screen->base;

        $scripts = array(
            "re-ad" => array(
                "post" => array(
                    "editAd" => array(
                        "path" => "/includes/js/edits/editAd.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "localize" => array(
                            "translations" => array(
                                "replace" => __("Replace pictures", "retxtdom"),
                                "delete" => __("Delete", "retxtdom")
                            )
                        ),
                    ),
                    "autocompleteAddress" => array(
                        "path" => "/includes/js/searches/autocompleteAddress.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "localize" => array(
                            "variablesAddress" => array(
                                "getAddressDataURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/address")
                            )
                        )
                    ),
                    "reloadAgents" => array(
                        "path" => "/includes/js/searches/reloadAgents.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "localize" => array(
                            "variablesAgents" => array(
                                "getAgentsURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/agents")
                            )
                        )
                    )
                ),
                "re-ad_page_".PLUGIN_RE_NAME."import" => array(
                    "import" => array(
                        "path" => "/includes/js/others/import.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "localize" => array(
                            "variablesImport" => array(
                                "confirmation" => __("Are you sure that you want to import this file ?", "retxtdom"),
                                "url" => wp_nonce_url(admin_url("edit.php?post_type=re-ad&page=".PLUGIN_RE_NAME."import"), "importAds", "nonceSecurity")
                            )
                        )
                    )
                ),
                "re-ad_page_".PLUGIN_RE_NAME."options" => array(
                    "options" => array(
                        "path" => "/includes/js/others/options.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "localize" => array(
                            "variablesOptions" => array(
                                "mainFeatures" => __("main features", "retxtdom"),
                                "additionalFeatures" => __("Additional features", "retxtdom")
                            )
                        )
                    )
                )
            ),
            "agent" => array(
                "post" => array(
                    "reloadAgents" => array(
                        "path" => "/includes/js/searches/reloadAgencies.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "localize" => array(
                            "variablesAgencies" => array(
                                "getAgenciesUrl" => get_rest_url(null, PLUGIN_RE_NAME."/v1/agencies")
                            )
                        )
                    )
                )
            ),
            "agency" => array(
                "post" => array(                  
                    "autocompleteAddress" => array(
                        "path" => "/includes/js/searches/autocompleteAddress.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "localize" => array(
                            "variablesAddress" => array(
                                "getAddressDataURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/address")
                            )
                        )
                    )
                )
            )
        );

        $scriptsToRegister = isset($scripts[$postType][$base])?$scripts[$postType][$base]:array();
        foreach($scriptsToRegister as $name => $script) {
            wp_register_script($name, plugins_url(PLUGIN_RE_NAME.$script["path"]), $script["dependencies"], PLUGIN_RE_VERSION, $script["footer"]);
            if(isset($script["localize"])) {
                foreach($script["localize"] as $variableName => $localizeData) {
                    wp_localize_script($name, $variableName, $localizeData);
                }
            }
            wp_enqueue_script($name);
        }

        if($base === "post") {
            if($postType === "re-ad") {
                wp_enqueue_media();
            }else if($postType === "agency") {
                wp_enqueue_script("mediaButton");
            }
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
        require_once("models/searches/getAddressData.php");
        register_rest_route(PLUGIN_RE_NAME."/v1", "address", array( 
            "methods" => "GET",
            "callback" => "getAddressData",
            "permission_callback" => array($this, "permissionCallbackGetAddressData")
        ));
        
        require_once("models/searches/getAgencies.php");
        register_rest_route(PLUGIN_RE_NAME."/v1", "agencies", array( 
            "methods" => "GET",
            "callback" => "getAgencies",
            "permission_callback" => function() {
                $idUser = apply_filters("determine_current_user", false);
                wp_set_current_user($idUser);
                return current_user_can("edit_others_posts");
            }
        ));
        
        require_once("models/searches/getAgents.php");
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
     * Update the logs TODO : Put that part elsewhere
     */
    public function permissionCallbackGetAddressData($request) {
        $idUser = apply_filters("determine_current_user", false);
        wp_set_current_user($idUser);
        $apisOptions = get_option(PLUGIN_RE_NAME."OptionsApis");
        $userCanEdit = current_user_can("edit_others_posts");
        $nonceExistsAndIsValid = !is_null($request->get_param("nonce")) && wp_verify_nonce($request->get_param("nonce"), "apiAddress");
        $isAjax = !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest";
        $noLimit = !boolval($apisOptions["apiLimitNbRequests"]);
        
        if($userCanEdit || ($nonceExistsAndIsValid || $isAjax) || $noLimit) {
            $clientAllowed = true;
        }else{
            $logsAPI = get_option(PLUGIN_RE_NAME."LogsAPIIPNbRequests");
            $date = date("m-d-Y");
            $maxRequests = intval($apisOptions["apiMaxNbRequests"]);
            $clientIP = $_SERVER["REMOTE_ADDR"]; 

            if($logsAPI !== false && isset($logsAPI[$date])) {
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
            wp_register_script("searchBarAd", plugins_url(PLUGIN_RE_NAME."/includes/js/searches/searchBarAd.js"), array("jquery", "jquery-ui-slider", "jquery-ui-autocomplete"), PLUGIN_RE_VERSION, false);
            $variablesSearchBar = array(
                "filters" => __("FILTERS", "retxtdom"),
                "searchBarURL" => plugin_dir_url(__FILE__)."templates/searchBars/searchBarAd.php",
                "autocompleteURL" => plugin_dir_url(__FILE__)."includes/js/searches/autocompleteAddress.js",
                "getAddressDataURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/address"),
                "nonce" => wp_create_nonce("searchNonce")
            );
            wp_localize_script("searchBarAd", "variablesSearchBar", $variablesSearchBar);
            wp_enqueue_script("searchBarAd");

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
            '<a href="'.esc_url(admin_url("/edit.php?post_type=re-ad&page=".PLUGIN_RE_NAME."options")).'">'.__("Options", "retxtdom")."</a>"
        ));
	return $links;
    }
    
    public function displayNotices() {
        $errors = array();
        $warnings = array();
        $informations = array();
        
        //Check theme
        
        $currentTheme = wp_get_theme();
        $themeName = str_replace(' ', '', strtolower($currentTheme->name));
        $themeVersion = $currentTheme->version;
        $listThemes = array_diff(scandir(PLUGIN_RE_PATH."templates/"), array("..", '.', "searchBars"));
        
        foreach($listThemes as $theme) {
            if(strpos($theme, $themeName) === 0) {
                $themePluginVersion = substr($theme, strlen($themeName));
                if($themePluginVersion === $themeVersion) {
                    $checkTheme = "ok";
                    break;
                }else {
                    $checkTheme = "badVersion";
                    break;
                }
            }else{
                $checkTheme = "noTheme";
            }
        }
        
        if($checkTheme === "noTheme") {
            array_push($errors, __("The theme that you are using is not compatible with the plugin. Please use one of the following themes :", "retxtdom")."<br />"
                . "<ul><li><a target='_blank' href='https://wordpress.org/themes/twentytwenty/'>Twenty twenty 2.1</a></li></ul><br />"
                . __("For more information, please read the", "retxtdom").'&nbsp;<a target="_blank" href="#">'.__("documentation", "retxtdom")."</a>");
        }else if($checkTheme === "badVersion") {
            array_push($warnings, sprintf(__('The version of the theme you are using (%1$s) is different from the one the plugin templates were built with (%2$s). Expect potential bugs.', "retxtdom"), $themeVersion, $themePluginVersion)."<br />"
                . __("For more information, please read the", "retxtdom").'&nbsp;<a target="_blank" href="#">'.__("documentation", "retxtdom")."</a>");
        }
        
        $pluginName = strtoupper(PLUGIN_RE_NAME);
        foreach($errors as $error) { ?>
            <div class="notice notice-error is-dismissible">
                <h3><?= $pluginName; ?></h3>
                <p><?= $error; ?></p>   
            </div>
        <?php }
        foreach($warnings as $warning) { ?>
            <div class="notice notice-warning is-dismissible">
                <h3><?= $pluginName; ?></h3>
                <p><?= $warning; ?></p>   
            </div>
        <?php }
        foreach($informations as $info) { ?>
            <div class="notice notice-info is-dismissible">
                <h3><?= $pluginName; ?></h3>
                <p><?= $info; ?></p>   
            </div>
        <?php }
    }
    
}
new Realm;