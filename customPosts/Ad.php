<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Ad class
 * 
 */
class REALM_Ad {

    /*
     * Register plugin styles for the singleAd template
     */
    private function registerPluginStylesSingleAd() {
        $CSSPath = PLUGIN_RE_NAME."/includes/css/templates/".PLUGIN_RE_THEME["name"].'/'.PLUGIN_RE_THEME["version"];

        wp_register_style("leaflet", plugins_url(PLUGIN_RE_NAME."/includes/css/others/leaflet.min.css"), array(), "1.9.3");
        wp_register_style("leafletFullscreen", plugins_url(PLUGIN_RE_NAME."/includes/css/others/leafletFullscreen.min.css"), array(), "2.3.0");
        wp_register_style("singleAd", plugins_url("$CSSPath/singles/singleAd.css"));
        wp_register_style("googleIcons", "https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,1,0");
        wp_enqueue_style("leaflet");
        wp_enqueue_style("leafletFullscreen");
        wp_enqueue_style("singleAd");
        wp_enqueue_style("googleIcons");
    }

    /*
     * Register plugin scripts for the singleAd template
     */
    private function registerPluginScriptsSingleAd() {
        wp_register_script("leaflet", plugins_url(PLUGIN_RE_NAME."/includes/js/others/leaflet.min.js"), array(), "1.9.3", true);
        wp_register_script("leafletFullscreen", plugins_url(PLUGIN_RE_NAME."/includes/js/others/leafletFullscreen.min.js"), array(), "2.3.0", true);
        wp_register_script("singleAd", plugins_url(PLUGIN_RE_NAME."/includes/js/templates/singles/singleAd.js"), array("jquery"), PLUGIN_RE_VERSION, true);
        wp_localize_script("singleAd", "translations", array("confirm" => "Do you confirm that you want to apply?"));
        wp_enqueue_script("leaflet");
        wp_enqueue_script("leafletFullscreen");
        wp_enqueue_script("singleAd");
        wp_enqueue_script("dpeges");
    }

    /*
     * Create the custom post Ad
     */
    public function createAd() {
        register_post_type("re-ad", //Just "ad" doesn't work for some reason
            array(
                "labels" => array(
                    "name"                  => __("Ads", "retxtdom"),
                    "singular_name"         => __("An ad", "retxtdom"),
                    "add_new"               => __("Add an ad", "retxtdom"),
                    "add_new_item"          => __("Add an ad", "retxtdom"),
                    "edit"                  => __("Edit", "retxtdom"),
                    "edit_item"             => __("Edit an ad", "retxtdom"),
                    "new_item"              => __("New ad", "retxtdom"),
                    "view"                  => __("View", "retxtdom"),
                    "view_item"             => __("View an ad", "retxtdom"),
                    "search_items"          => __("Search ads", "retxtdom"),
                    "not_found"             => __("No ads found", "retxtdom"),
                    "not_found_in_trash"    => __("No ads found in trash", "retxtdom"),
                    "all_items"             => __("All ads", "retxtdom"),
                    "featured_image"        => __("Ad's thumbnail", "retxtdom"),
                    "set_featured_image"    => __("Choose a thumbnail", "retxtdom"),
                    "remove_featured_image" => __("Remove the thumbnail", "retxtdom"),
                    "use_featured_image"    => __("Use as thumbnail", "retxtdom"),
                    "rewrite"               => array("slug" => "ad")
               ),
                "capabilities" => array(
                    "edit_post"              => "edit_ad", 
                    "read_post"              => "read_ad", 
                    "delete_post"            => "delete_ad", 
                    "edit_posts"             => "edit_ads", 
                    "edit_others_posts"      => "edit_others_ads", 
                    "publish_post"           => "publish_ad", 
                    "publish_posts"          => "publish_ads",
                    "read_private_posts"     => "read_private_ads",
                    "read"                   => "read", 
                    "delete_posts"           => "delete_ads",  
                    "delete_private_posts"   => "delete_private_ads", 
                    "delete_published_posts" => "delete_published_ads", 
                    "delete_others_posts"    => "delete_others_ads", 
                    "edit_private_posts"     => "edit_private_ads", 
                    "edit_published_posts"   => "edit_published_ads", 
                    "create_posts"           => "create_ads", 
                ),
                "capability_type" => array("ad", "ads"),
                "hierarchical" => true, //To be able to use wp_dropdown_pages
                "map_meta_cap" => true, 
                "public" => true,
                "menu_position" => 15,
                "supports" => array("title", "editor", "thumbnail"),
                "menu_icon" => "dashicons-admin-home",
                "has_archive" => true
            )
        );

        //Taxonomy property's type
        register_taxonomy("adTypeProperty", array("re-ad"), array(
            "hierarchical"      => false, 
            "description"       => __("Create a property type to categorize your ads", "retxtdom"), 
            "label"             => __("Property types", "retxtdom"), 
            "show_admin_column" => true, 
            "show_in_menu"      => false,
            "singular_label"    => __("Property type", "retxtdom"), 
            "rewrite"           => false,
            "meta_box_cb"       => array($this, "taxonomyMetaBoxCB")
        ));

        if(get_option("REPluginActivation") != 1) { //If plugin actived for the first time, register terms
            $termIds = wp_insert_term(__("House", "retxtdom"), "adTypeProperty", array("slug"=>"house"));
            if(!is_wp_error($termIds)) {
                update_term_meta($termIds["term_id"], "habitable", true);
            }

            $termIds = wp_insert_term(__("Apartment", "retxtdom"), "adTypeProperty", array("slug"=>"apartment"));
            if(!is_wp_error($termIds)) {
                update_term_meta($termIds["term_id"], "habitable", true);
            }

            $termIds = wp_insert_term(__("Shop", "retxtdom"), "adTypeProperty", array("slug"=>"shop"));
            if(!is_wp_error($termIds)) {
                update_term_meta($termIds["term_id"], "habitable", false);
            }

            $termIds = wp_insert_term(__("Office", "retxtdom"), "adTypeProperty", array("slug"=>"office"));
            if(!is_wp_error($termIds)) {
                update_term_meta($termIds["term_id"], "habitable", false);
            }

            $termIds = wp_insert_term(__("Parking/garage", "retxtdom"), "adTypeProperty", array("slug"=>"parking-garage"));
            if(!is_wp_error($termIds)) {
                update_term_meta($termIds["term_id"], "habitable", false);
            }

            $termIds = wp_insert_term(__("Building", "retxtdom"), "adTypeProperty", array("slug"=>"building"));
            if(!is_wp_error($termIds)) {
                update_term_meta($termIds["term_id"], "habitable", false);
            }

            $termIds = wp_insert_term(__("Land", "retxtdom"), "adTypeProperty", array("slug"=>"land"));
            if(!is_wp_error($termIds)) {
                update_term_meta($termIds["term_id"], "habitable", false);
            }
            add_option("REPluginActivation", 1, false);
        }

        //Taxonomy ad's type
        register_taxonomy("adTypeAd", array("re-ad"), array(
            "hierarchical"      => false, 
            "description"       => __("Create an ad type to categorize your ads", "retxtdom"), 
            "label"             => __("Ad types", "retxtdom"), 
            "show_admin_column" => true, 
            "show_in_menu"      => false,
            "singular_label"    => __("Ad type", "retxtdom"), 
            "rewrite"           => false,
            "meta_box_cb"       => array($this, "taxonomyMetaBoxCB")
        ));
        
        wp_insert_term(__("Rental", "retxtdom"), "adTypeAd", array("slug"=>"rental"));
        wp_insert_term(__("Sell", "retxtdom"), "adTypeAd", array("slug"=>"sell"));
        
        register_taxonomy("adAvailable", array("re-ad"), array(
            "hierarchical"      => false, 
            "description"       => __("Property availability", "retxtdom"), 
            "label"             => __("Property availability", "retxtdom"), 
            "show_admin_column" => true, 
            "show_in_menu"      => false,
            "singular_label"    => __("Property availability", "retxtdom"), 
            "rewrite"           => false,
            "meta_box_cb"       => array($this, "taxonomyAdAvailableCheckboxCB"),
            "default_term"      => array("slug"=>"available")
        ));
        
        wp_insert_term(__("Available", "retxtdom"), "adAvailable", array("slug"=>"available"));
        wp_insert_term(__("Unavailable", "retxtdom"), "adAvailable", array("slug"=>"unavailable"));
        
    }
    
  
    /*
     * Fetch the single or archive custom post Ad template
     */
    public function templatePostAd($path) {
        $shortPath = PLUGIN_RE_THEME["name"].'/'.PLUGIN_RE_THEME["version"];
        $fullPath = PLUGIN_RE_PATH."templates/front";
        if(is_dir($fullPath)) {
            if(get_post_type() === "re-ad") {
                if(is_single() && !locate_template(array("single-ad.php"))) {
                    $path = "$fullPath/singles/single-ad.php";
                    $this->registerPluginScriptsSingleAd();
                    $this->registerPluginStylesSingleAd();
                }else if(is_post_type_archive("re-ad") && !locate_template(array("archive-ad.php"))) {
                    $path =  "$fullPath/archives/archive-ad.php";
                    wp_register_style("archiveAd", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/$shortPath/archives/archiveAd.css"), array(), PLUGIN_RE_VERSION);
                    wp_enqueue_style("archiveAd");
                }
                if(is_post_type_archive("re-ad") && PLUGIN_RE_REP) {
                    wp_register_script("archiveAds", plugins_url(PLUGIN_REP_NAME."/includes/js/templates/archives/archiveAds.js"), array("jquery"), PLUGIN_REP_VERSION, true);
                    wp_localize_script("archiveAds", "variables", array(
                        "APIURL" => get_rest_url(null, PLUGIN_REP_NAME."/v1/alerts"), 
                        "success" => __("You are subscribed to this alert with success.", "reptxtdom"),
                        "sameAlert" => __("You are already subscribed to this alert.", "reptxtdom"),
                        "error" => __("An error occured, please try again later.", "reptxtdom")
                        ));
                    wp_enqueue_script("archiveAds");
                }
            }else if(is_search() && !have_posts() && !locate_template(array("no-results.php"))) {
                $path =  "$fullPath/archives/no-results.php";
                wp_register_style("noResults", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/$shortPath/archives/noResults.css"), array(), PLUGIN_RE_VERSION);
                wp_enqueue_style("noResults");
            }
        }
        
        return $path;
    }
    
    /*
     * Add a metabox to display the taxonomies adType and adProperty
     */
    public function taxonomyMetaBoxCB($post, $taxonomy) {
        $taxonomyName = $taxonomy["args"]["taxonomy"];
        $terms = get_terms($taxonomyName, array("hide_empty" => false));
	$term = wp_get_object_terms($post->ID, $taxonomyName, array("orderby" => "term_id", "order" => "ASC"));
	$name  = '';

        if(!is_wp_error($term)) {
            if(isset($term[0]) && isset($term[0]->name)) {
                $name = $term[0]->name;
            }
        }

        foreach($terms as $term) {
        ?>
            <label title="<?php esc_attr_e($term->name); ?>">
                <input type="radio" name="<?= $taxonomyName; ?>" value="<?php esc_attr_e($term->name); ?>" <?php checked($term->name, $name); ?> required>
                    <span><?php esc_html_e($term->name); ?></span>
            </label><br>
        <?php
        }
    }
    
    /*
     * Add a metabox to display the ad's availability
     */
    public function taxonomyAdAvailableCheckboxCB($post) {
        global $pagenow;
        $availability = wp_get_post_terms($post->ID, "adAvailable", array("fields"=>"slugs"));
        $available = false;
        if(isset($availability[0])) {
            $available = $availability[0]==="available";
        }
        ?>
        <label title="<?php _e("The property is available", "retxtdom");?>">
            <input type="checkbox" name="adAvailable" value="available" <?php checked($pagenow==="post-new.php" || $available); ?>>
            <span><?php _e("The property is available", "retxtdom");?></span>
        </label>
        <?php
    }
    
    /*
     * Filtering the ads by the taxonomies in the admin area
     */
    public function dropdownsTaxonomies() {
        global $typenow;
        $postType = "re-ad"; 
        if($typenow === $postType) {
            $currentUserID = get_current_user_id();
            $currentUser = get_user_by("ID", $currentUserID);
            if(!$currentUser) {
                return;
            }
            $currentUserRole = $currentUser->roles[0];
            global $wp_query;
        
            $taxonomies = get_taxonomies(["object_type" => [$postType]]);
            require_once(PLUGIN_RE_PATH."models/UserModel.php");
            if($currentUserRole === "administrator") {
                $agents = REALM_UserModel::getUsersByRole("agent");
            }else{
                if($currentUserRole === "agent") {
                    $agencyID = intval(get_user_meta($currentUserID, "agentAgency", true));
                }else if($currentUserRole === "agency") {
                    $agencyID = $currentUserID;
                }
                $agents = REALM_UserModel::getAgentsAgency($agencyID);
            }
            foreach($taxonomies as $taxonomy) {
                $taxonomyData = get_taxonomy($taxonomy);
                $terms = get_terms(array(
                    "taxonomy" => $taxonomy,
                    "orderby" => "name",
                    "hide_empty"   => true,
                    
                ));
                ?>
                <select name="<?=$taxonomy;?>">
                    <option value=""><?=$taxonomyData->label;?></option>
                    <?php
                    $current = isset($_GET["$taxonomy"]) && intval($_GET["$taxonomy"]) > 0?intval($_GET["$taxonomy"]):'';
                    foreach($terms as $term) { 
                        $nbTerms = 0;
                        foreach($wp_query->posts as $post) {                           
                            $adTermID = get_the_terms($post->ID, "$taxonomy")[0]->term_id;
                            if($adTermID === $term->term_id) {
                                $nbTerms++;
                            }
                        }?>
                        <option value="<?=$term->term_id;?>" <?php selected($term->term_id==$current);?>><?=$term->name;?>&nbsp;(<?=$nbTerms;?>)</option>
                    <?php } ?>
                </select>
            <?php
            } ?>
            
            <select name="agent">
                <option value=""><?php _e("Agents", "reptxtdom");?></option>
                <?php
                    $current = isset($_GET["agent"])?$_GET["agent"]:'';
                    foreach($agents as $agent) {
                        $nbAdsByAgent = 0;
                        global $wp_query;
                        foreach($wp_query->posts as $post) {                           
                            $agentInCharge = get_post_meta($post->ID, "adIdAgent", true);
                            if($agentInCharge === $agent->ID) {
                                $nbAdsByAgent++;
                            }
                        }
                        $agentName = get_user_meta($agent->ID, "first_name", true).' '.get_user_meta($agent->ID, "last_name", true); ?>
                        <option value="<?=$agent->ID;?>" <?php selected($agent->ID==$current);?>><?=$agentName;?>&nbsp;(<?=$nbAdsByAgent;?>)</option>
                    <?php } ?>
            </select>
        <?php }
    }
    
    
    /*
     * Add or modify the columns shown in the WordPress admin table for the re-da custom post type
     */
    public function colsAdsList($columns) {
        unset($columns["date"]);
        $columns["inCharge"] = __("Agent in charge", "retxtdom");
        $columns["date"] = __("Date", "retxtdom");
        return $columns;
    }
    
    
    public function contentCustomColsAds($column, $postID) {
        if($column === "inCharge") {
            $agentID = intval(get_post_meta($postID, "adIdAgent", true));
            $url = admin_url("edit.php?post_type=re-ad&agent=$agentID");
            $agentName = get_user_meta($agentID, "first_name", true).' '.get_user_meta($agentID, "last_name", true);
            echo '<a href="'.esc_attr($url).'">'.$agentName."</a>";
        }
    }
    
    /*
     * Modify the query before it is executed, in order to convert post ID values into their corresponding taxonomy terms
     */
    public function taxonomiesQuery($query) {
        global $pagenow;            
        global $typenow;

        if(is_admin() && $pagenow == "edit.php" && $typenow === "re-ad") {
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
     * Modify the query before it is executed, in order to filter the ads by an agency if needed
     * If agency : itself
     * If agent : own agency
     * If admin : by id agency get variable
     */
public function customFiltersQuery($query) {
    global $pagenow, $typenow;

    if(is_admin() && $pagenow === "edit.php" && $typenow === "re-ad") {
        $currentUserID = get_current_user_id();
        $currentUser = get_user_by("ID", $currentUserID);
        if(!$currentUser) {
            return;
        }
        $currentUserRole = $currentUser->roles[0];

        if($currentUserRole === "agency") {
            $idAgency = $currentUserID;
        }elseif ($currentUserRole === "agent") {
            $idAgency = get_user_meta($currentUserID, "agentAgency", true);
        }elseif($currentUserRole === "administrator" && isset($_GET["agency"]) && absint($_GET["agency"]) > 0) {
            $idAgency = absint($_GET["agency"]);
        }

        if(isset($idAgency)) {
            $agentsAgency = REALM_UserModel::getAgentsAgency($idAgency);
            $agentsAgencyIds = array_column($agentsAgency, "ID");

            $metaQueryValue = !empty($agentsAgencyIds) ? $agentsAgencyIds : 0;
            $meta_query = array(
                "key" => "adIdAgent",
                "value" => $metaQueryValue,
                "compare" => !empty($agentsAgencyIds) ? "IN" : "=",
            );

            if(isset($_GET["agent"]) && is_numeric($_GET["agent"]) && in_array(absint($_GET["agent"]), $agentsAgencyIds)) {
                $meta_query["value"] = absint($_GET["agent"]);
            }
        }else if($currentUserRole === "administrator") {
            if(isset($_GET["agency"]) && $_GET["agency"] === "no") {
                $meta_query = array(
                    "key" => "adIdAgent",
                    "compare" => "NOT EXISTS",
                );
            }else if(isset($_GET["agent"]) && absint($_GET["agent"]) > 0) {
                $meta_query = array(
                    "key" => "adIdAgent",
                    "value" => absint($_GET["agent"])
                );
            }
        }

        if(isset($meta_query)) {
            $postStatus = $query->get("post_status");

            if(empty($postStatus) && !$query->get("author")) {
                $query->set("post_status", array("publish", "draft"));
            }elseif ($postStatus === "trash") {
                $query->set("post_status", "trash");
            }
            $query->set("meta_query", array($meta_query));
        }
        
    }
}

    
    /*
     * Modify the tabs in edit re-ad for each custom filter
     */
    public function customFiltersTabs($tabs) {
        $currentUserId = get_current_user_id();
        $currentUser = get_user_by("ID", $currentUserId);
        if(!$currentUser) {
            return;
        }
        $currentUserRole = $currentUser->roles[0];
        
        $currentCustomFilter = isset($_GET["agency"]) && (is_numeric($_GET["agency"]) || $_GET["agency"] === "no" || empty($_GET["agency"]))?$_GET["agency"]:null;
        if(in_array($currentUserRole, array("agent", "agency"))) {
            global $wp_query;
            require_once(PLUGIN_RE_PATH."models/AdModel.php");

            if($currentUserRole === "agent") {
                $currentUserAgencyID = get_user_meta($currentUserId, "agentAgency", true);
                $nbDrafts = REALM_AdModel::getNbAdsByAgent($currentUserId, "draft");
                $nbTrashedAds = REALM_AdModel::getNbAdsByAgent($currentUserId, "trash");
            }else{
                $currentUserAgencyID = $currentUserId;
                $nbDrafts = REALM_AdModel::getNbAdsByAgency($currentUserId, "draft");
                $nbTrashedAds = REALM_AdModel::getNbAdsByAgency($currentUserId, "trash");
            }
            $currentDraft = isset($wp_query->query["post_status"]) && $wp_query->query["post_status"]==="draft";
            $draftLink = '<a'.($currentDraft?' class="current" aria-current="page"':'').' href="'.admin_url("edit.php?post_type=re-ad&post_status=draft").'">'.__("Draft").'</a><span class="count">('.$nbDrafts.')</span>';
            $tabs["draft"] = $draftLink;     
            
            $currentTrash = isset($wp_query->query["post_status"]) && $wp_query->query["post_status"]==="trash";
            $trashLink = '<a'.($currentTrash?' class="current" aria-current="page"':'').' href="'.admin_url("edit.php?post_type=re-ad&post_status=trash").'">'.__("Trash", "reptxtdom").'</a><span class="count">('.$nbTrashedAds.')</span>';
            $tabs["trash"] = $trashLink;  
                    
            $nbAds = REALM_AdModel::getNbAdsByAgency($currentUserAgencyID);
            $current = !isset($wp_query->query["author"]) && (!isset($wp_query->query["post_status"]) || empty($wp_query->query["post_status"]));
            $agencyLink = '<a'.($current?' class="current" aria-current="page"':'').' href="'.admin_url("edit.php?post_type=re-ad").'">'.__("Agency", "retxtdom").'</a><span class="count">('.$nbAds.')</span>';
            $array["agency"] = $agencyLink;
            $arrayBefore = array_slice($tabs, 0, 1, true);
            $arrayAfter = array_slice($tabs, 1, null, true);
            $tabs = array_merge($arrayBefore, $array, $arrayAfter);
        }else if($currentUserRole === "administrator") {
            global $wp_query;
            require_once(PLUGIN_RE_PATH."models/UserModel.php");
            require_once(PLUGIN_RE_PATH."models/AdModel.php");
            $agencies = REALM_UserModel::getUsersByRole("agency");
            foreach($agencies as $agency) {
                $nbAds = REALM_AdModel::getNbAdsByAgency($agency->ID);
                if(isset($_GET["agent"]) && absint($_GET["agent"])>0 && empty($wp_query->query["post_status"])) {
                    $agentsAgency = array_column(REALM_UserModel::getAgentsAgency($agency->ID), "ID");
                    $current = in_array(absint($_GET["agent"]), $agentsAgency);
                }else{
                    $current = $currentCustomFilter === $agency->ID;
                }
                $agencyLink = '<a'.($current?' class="current" aria-current="page"':'').' href="'.admin_url("edit.php?post_type=re-ad&agency=".$agency->ID).'">'.$agency->display_name.'</a><span class="count">('.$nbAds.')</span>';
                $tabs[$agency->display_name] = $agencyLink;
            }
            $nbAds = REALM_AdModel::getNbAdsWithoutAgency();
            $currentNoAgency = $currentCustomFilter === "no";
            $noAgencyLink = '<a'.($currentNoAgency?' class="current" aria-current="page"':'').' href="'.admin_url("edit.php?post_type=re-ad&agency=no").'">'.__("Without agency", "retxtdom").'</a><span class="count">('.$nbAds.')</span>';
            $tabs["noAgency"] = $noAgencyLink;
        }
        
        if(in_array($currentUserRole, array("agent", "agency"))) {
            unset($tabs["all"]);
            unset($tabs["publish"]);
        }   
        
        return $tabs;
    }


    
    /*
     * Add a column to display the typeProperty
     */
    public function typePropertyColumns($originalColumns) {
        $newColumns = $originalColumns;
        array_splice($newColumns, 1);
        $newColumns["habitable"] = __("Habitable", "retxtdom");
        $posts = $originalColumns["posts"];
        unset($originalColumns["posts"]);
        $newColumns = array_merge($originalColumns, $newColumns);
        $newColumns["posts"] = $posts;
        return $newColumns;
    }
    
    /*
     * Add a column to display the property habitability
     */
    public function typePropertyHabitableColumn($row, $columnName, $termId) {
        $meta = get_term_meta($termId, "habitable", true);
        if ("habitable" === $columnName) {
            if($meta == 1) {
                return $row . __("Yes", "retxtdom");
            }else {
                return $row . __("No", "retxtdom");
            }   
        }
    }
    
    /*
     * When adding a property's type, ask if it's habitable
     */
    public function typePropertyCreateFields($taxonomy) { ?>
        <?php _e("Habitable", "retxtdom"); ?> <input type="checkbox" name="habitable">
        <p class="description" id="description-description"><?php _e("Is this type of property habitable?", "retxtdom"); ?></p><br />
    <?php }
    
    /*
     * When updating a property's type, ask if it's habitable
     */
    public function typePropertyEditFields($term, $taxonomy) {
        $checked = get_term_meta($term->term_id, "habitable", true)==1;
        $html ='
            <tr class="form-field form-required">
              <th scope="row" valign="top"><label for="tag-type">'.__("Habitable", "retxtdom").'</label></th>
              <td><input type="checkbox" name="habitable"'.checked($checked).'>
              <p class="description" id="description-description">'.__("Is this type of property habitable?", "retxtdom").'</p>
            </td>
            </tr>';
        echo $html;
    }
    
    /*
     * Update the property's type habitability 
     */
    public function termTypePropertyUpdate($termId, $ttId, $taxonomy) {
        update_term_meta($termId, "habitable", isset($_POST["habitable"]));   
    }   
}
