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
        
        //$this->createNotices();
        
        //$this->checkTheme();
        
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
        
        $activatedPluginsList = get_option("active_plugins");
        define("PLUGIN_RE_REP", in_array("realm/realm.php", $activatedPluginsList));
    }
    
    /*
     * (Re-)initialize/fill the notices option
     */
    public static function createNotices($type=null, $msg=null, $reset=false) {
        if($reset) {
            $notices = array("errors"=>[], "warnings"=>[], "informations"=>[]);
        }else{
            $notices = get_option(PLUGIN_RE_NAME."Notices");
        }
        
        if(!is_null($type) && !is_null($msg)) {
            array_push($notices[$type], wp_kses_post($msg));
        }
        
        update_option(PLUGIN_RE_NAME."Notices", $notices, true);
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
        require_once("controllers/RegisterUserDashboard.php");
        require_once("controllers/Export.php");
        require_once("controllers/Import.php");
        
        //Models
        require_once("models/AdModel.php");
        require_once("models/UserModel.php");
        
        
        $this->Ad               = new REALM_Ad;
        $this->Agent            = new REALM_Agent;
        $this->Agency           = new REALM_Agency;
        
        $this->Options          = new REALM_Options;
        $this->EditAd           = new REALM_EditAd;
        $this->RegistrationUser = new REALM_RegisterUserDashboard;
        $this->Export           = new REALM_Export;
        $this->Import           = new REALM_Import;
        
        $this->AdModel          = new REALM_AdModel;   
        $this->UserModel        = new REALM_UserModel;
    }
    
    /*
     * Add the actions
     */
    private function actions() {
        //Load languages
        add_action("init", array($this, "loadLanguages"));

        //Initialize custom posts
        add_action("init", array($this, "initCustomPosts"));

        //Add capabilities to admin/agent/agency for Ad posts
        add_action("init", array($this, "addRolesCaps"));       
        
        //Add metaboxes to re-ad
        add_action("add_meta_boxes_re-ad", array($this->EditAd, "addMetaBoxes"));
        
        //Initialize the plugin's options
        add_action("admin_init", array($this->Options, "optionsPageInit"));
        
        //Add menu items to the WordPress admin dashboard
        add_action("admin_menu", array($this, "completeMenu"));    
        
        //https://wordpress.stackexchange.com/questions/178033/disable-posts-only-allow-to-edit-existing-pages-not-create-new-ones-create-po
        add_action("admin_menu", array($this, "fixWPBug22895"));
        
        //Hide the update version of WP in the footer
        add_filter("admin_menu", array($this, "hideWPVersion"));

        //Register plugin styles for the admin area
        add_action("admin_enqueue_scripts", array($this, "registerPluginStylesAdmin"));

        //Register plugin scripts for the admin area
        add_action("admin_enqueue_scripts", array($this, "registerPluginScriptsAdmin"));
        
        //Activate dashicons for the front-end
        add_action("wp_enqueue_scripts", array($this, "loadDashicons"));
        
        //Add fields on registration new user
        add_action("user_new_form", array($this->RegistrationUser, "addFieldsNewUser"));
        
        //Save the custom fields
        add_action("user_register", array($this->RegistrationUser, "saveCustomFieldsNewUser"));
        //When the user is an agent or an agency, create a post
        add_action("user_register", array($this->RegistrationUser, "createPostOnNewUser"));

        //Remove the default search widget
        add_action("widgets_init", array($this, "removeSearchWidget"));
        
        //add_action("widgets_init", array($this->SearchBar, "searchBarWidget"));

        //Add tabs to the admin notice area
        add_action("all_admin_notices", array($this->Options, "tabsOption"));
        
        //Remove some widgets from the WordPress dashboard
        add_action("wp_dashboard_setup", array($this, "removeWidgets"));

        //Add widgets to the WordPress dashboard
        add_action("wp_dashboard_setup", array($this->Import, "widgetImport"));
        add_action("wp_dashboard_setup", array($this->Export, "widgetExport"));

        //Save custom post types
        add_action("save_post_re-ad", array($this->EditAd, "savePost"), 10, 2);

        //Filter custom post types by taxonomies
        add_action("restrict_manage_posts", array($this->Ad, "dropdownsTaxonomies"));
        
        //Display notices
        add_action("admin_notices", array($this, "displayNotices"));

        //Update term meta for custom taxonomy
        add_action("created_term", array($this->Ad, "termTypePropertyUpdate"), 10, 3);
        add_action("edit_term", array($this->Ad, "termTypePropertyUpdate"), 10, 3);

        //Add fields to custom taxonomy creation/editing screens
        add_action("adTypeProperty_add_form_fields", array($this->Ad, "typePropertyCreateFields"));
        add_action("adTypeProperty_edit_form_fields", array($this->Ad, "typePropertyEditFields"), 10, 2);
        
        add_action("manage_re-ad_posts_custom_column", array($this->Ad, "contentCustomColsAds"), 10, 2);

        //Import data via AJAX
        add_action("wp_ajax_nopriv_import", array($this->Import, "startImport"));

        //Register custom API route
        add_action("rest_api_init", array($this, "registerRouteCustomAPIs"));
        
        //Add actions (links) to the plugin row in plugins.php
        add_action("plugin_action_links_" . plugin_basename( __FILE__ ), array($this, "addActionsPluginRow"));
        
        add_action("after_setup_theme", array($this, "checkTheme"));
    }
    
    /*
     * Add the filters
     */
    private function filters() {
        //Change the default text shown in the title field of a WordPress post editor
        //add_filter("enter_title_here", array($this, "changeTitle"));
        
        //Modify the query before it is executed, in order to convert post ID values into their corresponding taxonomy terms
        add_filter("pre_get_posts", array($this->Ad, "taxonomiesQuery")); 
        
        //Modify the query before it is executed, in order to filter the ads by an agency if needed
        add_filter("pre_get_posts", array($this->Ad, "customFiltersQuery"));
        
        //Modify the query before it is executed, in order to include custom search functionality for the ads
        add_filter("pre_get_posts", array($this->AdModel, "setQueryAds")); 
        
        //Add custom styles or scripts to the WordPress header section
        add_filter("wp_enqueue_scripts", array($this, "updateHeader"));
        
        //Add or modify the columns shown in the WordPress admin table for the re-da custom post type
        add_filter("manage_re-ad_posts_columns", array($this->Ad, "colsAdsList")); 
           
        //Display a text next to the Ad title in the posts list if the property is unavailable
        add_filter("display_post_states", array($this->Ad, "addPostStateAvailability"), 10, 2);
        
        //Make the taxonomy columns in the list of ads sortable
        //add_filter("manage_edit-re-ad_sortable_columns", array($this->Ad, "colsAdsListSortable")); 
                
        //Add custom columns to the WordPress admin table for the adTypeProperty taxonomy
        add_filter("manage_adTypeProperty_custom_column", array($this->Ad, "typePropertyHabitableColumn"), 15, 3); 
        
        //Define the columns to be displayed in the WordPress admin table for the "adTypeProperty" taxonomy
        add_filter("manage_edit-adTypeProperty_columns", array($this->Ad, "typePropertyColumns")); 
        
        //Modify the template file used to display a single or archive ad post type
        add_filter("template_include", array($this->Ad, "templatePostAd"), 1); 
        
        //Add an agent's agency column header to the agent users list and remove the posts and role columns
        add_filter("manage_users_columns", array($this->UserModel, "agentAgencyHeaderColumn"));         
        
        //Ads the agent's agency name to the previous column
        add_filter("manage_users_custom_column", array($this->UserModel, "agentAgencyDataColumn"), 10, 3);
        
        //Modify the tabs in edit re-ad for each custom filter
        add_filter("views_edit-re-ad", array($this->Ad, "customFiltersTabs"));
             
        //To do : sort and filter by the agent's agency
        //add_filter("manage_users_sortable_columns", array($this->UserModel, "agentAgencySortableColumn"));
        
    }
    
    /*
     * When the plugin is activated
     */
    public function onPluginActivation() {
        $this->defaultOptionsValues();
        $this->rewriteFlush(); //Update the permalinks structure
        
        add_role(
            "agent",
            __("Agent", "retxtdom"),
            array(
                "read" => true,
                "edit_posts" => false,
                "delete_posts" => false,
                "publish_posts" => false,
                "upload_files" => false, //false ?
            ) 
        );
        
        add_role(
            "agency",
            __("Agency", "retxtdom"),
            array(
                "read" => true,
                "edit_posts" => false,
                "delete_posts" => false,
                "publish_posts" => false,
                "upload_files" => true, //false ?
            ) 
        );        
    }
    
    public function addRolesCaps() {
        $rolesSlug = array("administrator", "agent", "agency");
        
        foreach($rolesSlug as $roleSlug) {
            $role = get_role("$roleSlug");
            
            if($role !== null) {
                $role->add_cap("read");

                //Ads
                $role->add_cap("read_ad");
                $role->add_cap("read_private_ads");
                $role->add_cap("edit_ad");
                $role->add_cap("edit_ads");
                $role->add_cap("edit_others_ads");
                $role->add_cap("edit_published_ads");
                $role->add_cap("edit_private_ads");
                $role->add_cap("create_ads");
                $role->add_cap("publish_ad");
                $role->add_cap("publish_ads");
                $role->add_cap("delete_ad");
                $role->add_cap("delete_ads");
                $role->add_cap("delete_others_ads");
                $role->add_cap("delete_private_ads");
                $role->add_cap("delete_published_ads");
            }
        }    
    }
    
    public function onPluginDeactivation() {
        //TODO : Ajouter option pour supprimer options ou non quand on désactive le plugin
        flush_rewrite_rules(); //Update the permalinks structure
        
        remove_role("agent");
        remove_role("agency");
    }

    /*
     * Save the default options values
     */
    private function defaultOptionsValues() {
        
        $defaultValuesLanguage = array(
            "language" => "en",
            "currency" => "$",
            "areaUnit" => "m²"
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
            '' => array(
                "user" => array(
                    "customRegistrationFields" => array(
                        "path" => "/includes/js/others/registrationUser.js",
                        "footer" => true,
                        "dependencies" => array("jquery")
                    ),
                    /*"reloadAgencies" => array(
                        "path" => "/includes/js/searches/reloadAgencies.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "variables" => array(
                            "variablesAgencies" => array(
                                "getAgenciesURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/agencies")
                            )
                        )
                    )*/
                ),
                "user-edit" => array(
                    "editProfile" => array(
                        "path" => "/includes/js/others/editProfile.js",
                        "footer" => true,
                        "dependencies" => array("jquery")
                    ),
                    /*"reloadAgencies" => array(
                        "path" => "/includes/js/searches/reloadAgencies.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "variables" => array(
                            "variablesAgencies" => array(
                                "getAgenciesURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/agencies")
                            )
                        )
                    )*/
                ),
                "profile" => array(
                    "editProfile" => array(
                        "path" => "/includes/js/others/editProfile.js",
                        "footer" => true,
                        "dependencies" => array("jquery")
                    )
                )
            ),
            "re-ad" => array(
                "post" => array(
                    "editAd" => array(
                        "path" => "/includes/js/edits/editAd.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "variables" => array(
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
                        "variables" => array(
                            "variablesAddress" => array(
                                "getAddressDataURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/address")
                            )
                        )
                    ),
                    /*"reloadAgents" => array(
                        "path" => "/includes/js/searches/reloadAgents.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "variables" => array(
                            "variablesAgents" => array(
                                "getAgentsURL" => get_rest_url(null, PLUGIN_RE_NAME."/v1/agents"),
                                "currentUserRole" => get_user_by("ID", get_current_user_id())->roles[0]
                            )
                        )
                    )*/
                ),
                "re-ad_page_".PLUGIN_RE_NAME."import" => array(
                    "import" => array(
                        "path" => "/includes/js/others/import.js",
                        "footer" => true,
                        "dependencies" => array("jquery"),
                        "variables" => array(
                            "variablesImport" => array(
                                "confirmation" => __("Are you sure that you want to import this file?", "retxtdom"),
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
                        "variables" => array(
                            "variablesOptions" => array(
                                "mainFeatures" => __("main features", "retxtdom"),
                                "additionalFeatures" => __("Additional features", "retxtdom")
                            )
                        )
                    )
                )
            ),
        );

        $scriptsToRegister = isset($scripts[$postType][$base])?$scripts[$postType][$base]:array();
        foreach($scriptsToRegister as $name => $script) {
            wp_register_script($name, plugins_url(PLUGIN_RE_NAME.$script["path"]), $script["dependencies"], PLUGIN_RE_VERSION, $script["footer"]);
            if(isset($script["variables"])) {
                foreach($script["variables"] as $variableName => $variablesData) {
                    wp_localize_script($name, $variableName, $variablesData);
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
        require_once("models/getAddressData.php");
        register_rest_route(PLUGIN_RE_NAME."/v1", "address", array( 
            "methods" => "GET",
            "callback" => "getAddressData",
            "permission_callback" => array($this, "permissionCallbackGetAddressData")
        ));
        
        register_rest_route(PLUGIN_RE_NAME."/v1", "agencies", array( 
            "methods" => "GET",
            "callback" => function() {
                $this->UserModel->getUsersByRole("agency", true);
            },
            "permission_callback" => array($this, "permissionCallbackApiGetUsers")
        ));
        
        register_rest_route(PLUGIN_RE_NAME."/v1", "agents", array( 
            "methods" => "GET",
            "callback" => function() {
                $this->UserModel->getUsersByRole("agent", true);
            },
            "permission_callback" => array($this, "permissionCallbackApiGetUsers")
        ));
    }
    
    /*
     * Check if the client can use the getAddress API
     * Update the logs TODO : Put that part elsewhere
     */
    public function permissionCallbackGetAddressData($request) {
        $idUser = apply_filters("determine_current_user", false);
        wp_set_current_user($idUser); //Plutôt directement chercher capabilities get_userdata() ?
        $apisOptions = get_option(PLUGIN_RE_NAME."OptionsApis");
        $userCanEdit = current_user_can("edit_ads");
        $nonceExistsAndIsValid = !is_null($request->get_param("nonce")) && is_numeric(wp_verify_nonce($request->get_param("nonce"), "apiAddress"));
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
    
    public function permissionCallbackApiGetUsers(/*$request*/) {
        $idUser = apply_filters("determine_current_user", false);
        wp_set_current_user($idUser);
        $userCanEdit = current_user_can("edit_ads");
        //$nonceExistsAndIsValid = !is_null($request->get_param("nonce")) && is_numeric(wp_verify_nonce($request->get_param("nonce"), "apiAddress"));
        $isAjax = !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest";

        return $userCanEdit /*&& $nonceExistsAndIsValid && $isAjax*/;
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
                "searchBarURL" => plugin_dir_url(__FILE__)."templates/front/searchBars/searchBarAd.php",
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
     * Activate dashicons for the front end
     */
    public function loadDashicons() {
        wp_enqueue_style("dashicons");
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
    
    /*
     * Check that the theme used is compatible with the plugin
     * Fill the notices arrays if needed
     * Save a constant with the templates path to use
     */
    public function checkTheme() {
        $currentTheme = wp_get_theme();
        $themeName = str_replace(' ', '', strtolower($currentTheme->name));
        $themeVersion = $currentTheme->version;
        $listThemes = array_diff(scandir(PLUGIN_RE_PATH."includes/css/templates"), array("..", '.', "searchBars")); 
        
        if(in_array($themeName, $listThemes)) {
            $listVersions = array_diff(scandir(PLUGIN_RE_PATH."includes/css/templates/$themeName"), array('.', ".."));
            if(!in_array($themeVersion, $listVersions)) {
                $this->createNotices("warnings", sprintf(__('The version of the theme you are using (%1$s) is different from the ones the plugin templates were built with (%2$s). Expect potential bugs.', "retxtdom"), $themeVersion, implode(", ", $listVersions))."<br />"
                . __("For more information, please read the", "retxtdom").'&nbsp;<a target="_blank" href="#">'.__("documentation", "retxtdom")."</a>");
                $versionToUse = end($listVersions);
            }else{
                $versionToUse = $themeVersion;
                $this->createNotices(null, null, true); //Reset
            }
            define("PLUGIN_RE_THEME", array("name" => $themeName, "version" => $versionToUse));
        }else{
            $this->createNotices("errors", __("The theme that you are using is not compatible with the plugin. Please use one of the following themes :", "retxtdom")."<br />"
                . "<ul><li><a target='_blank' href='https://wordpress.org/themes/twentytwenty/'>Twenty twenty 2.1</a></li></ul><br />"
                . __("For more information, please read the", "retxtdom").'&nbsp;<a target="_blank" href="#">'.__("documentation", "retxtdom")."</a>");
            
            $listVersions = scandir(PLUGIN_RE_PATH."includes/css/templates/twentytwenty");
            $themeLastVersion = end($listVersions);
            define("PLUGIN_RE_THEME", array("name" => "twentytwenty", "version" => $themeLastVersion));      
        } 
    }
    
    /*
     * Display a notice panel with one or several messages if needed
     */
    public function displayNotices() {
        $pluginName = strtoupper(PLUGIN_RE_NAME);
        $notices = get_option(PLUGIN_RE_NAME."Notices");
        foreach($notices["errors"] as $error) { ?>
            <div class="notice notice-error is-dismissible">
                <h3><?= $pluginName; ?></h3>
                <p><?= $error; ?></p>   
            </div>
        <?php }
        foreach($notices["warnings"] as $warning) { ?>
            <div class="notice notice-warning is-dismissible">
                <h3><?= $pluginName; ?></h3>
                <p><?= $warning; ?></p>   
            </div>
        <?php }
        foreach($notices["informations"] as $info) { ?>
            <div class="notice notice-info is-dismissible">
                <h3><?= $pluginName; ?></h3>
                <p><?= $info; ?></p>   
            </div>
        <?php }
        $this->createNotices(null, null, true); //Reset
    }  
    
    /*
     * Remove the default WP widgets from the dashboard for the non-administrators
     */
    public function removeWidgets() {
        if(!current_user_can("administrator")) {
            global $wp_meta_boxes;
            unset($wp_meta_boxes["dashboard"]["normal"]["core"]["dashboard_activity"]);
            unset($wp_meta_boxes["dashboard"]["side"]["core"]["dashboard_primary"]);
        }
    }   
    
    /*
     * Fix a bug when we only want to edit a post with a custom type
     */
    public function fixWPBug22895() {    
        add_submenu_page("edit.php?post_type=submission", "fixWPBug22895", "fixWPBug22895", "edit_submissions", "fixWPBug22895");
        add_filter("add_menu_classes", array($this, "fixWPBug22895Unset"));    
    }
    
    public function fixWPBug22895Unset($menu){
        remove_submenu_page("edit.php?post_type=submission", "fixWPBug22895");
        return $menu;
    }
    
    public function hideWPVersion() {
        if(!current_user_can("manage_options")) {
            remove_filter("update_footer", "core_update_footer"); 
        }
    }
    
}
new Realm;