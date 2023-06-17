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
    private function registerPluginStylesSingleAd($path) {
        wp_register_style("leaflet", plugins_url(PLUGIN_RE_NAME."/includes/css/others/leaflet.min.css"), array(), "1.9.3");
        wp_register_style("leafletFullscreen", plugins_url(PLUGIN_RE_NAME."/includes/css/others/leafletFullscreen.min.css"), array(), "2.3.0");
        wp_register_style("singleAd", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/$path/singles/singleAd.css"));
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
               ),

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
        if(defined("PLUGIN_RE_THEME")) {
            $shortPath = PLUGIN_RE_THEME["name"].'/'.PLUGIN_RE_THEME["version"];
            $fullPath = PLUGIN_RE_PATH."templates/$shortPath";
            if(is_dir($fullPath)) {
                if(get_post_type() === "re-ad") {
                    if(is_single() && !locate_template(array("single-ad.php"))) {
                        $path = "$fullPath/singles/single-ad.php";
                        $this->registerPluginScriptsSingleAd();
                        $this->registerPluginStylesSingleAd($shortPath);
                    }else if(is_post_type_archive("re-ad") && !locate_template(array("archive-ad.php"))) {
                        $path =  "$fullPath/archives/archive-ad.php";
                        wp_register_style("archiveAd", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/$shortPath/archives/archiveAd.css"), array(), PLUGIN_RE_VERSION);
                        wp_enqueue_style("archiveAd");
                    }
                }else if(is_search() && !have_posts() && !locate_template(array("no-results.php"))) {
                    $path =  "$fullPath/archives/no-results.php";
                    wp_register_style("noResults", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/$shortPath/archives/noResults.css"), array(), PLUGIN_RE_VERSION);
                    wp_enqueue_style("noResults");
                }
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

        foreach ($terms as $term) {
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
    public function filterAdsByTaxonomies() {
        global $typenow;
        $postType = "re-ad"; 
        $taxonomies = get_taxonomies(["object_type" => [$postType]]);
        foreach($taxonomies as $taxonomy) {
            if($typenow == $postType) {
                $selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : "";
                $taxonomyData = get_taxonomy($taxonomy);
                wp_dropdown_categories(array(
                        "show_option_all" => $taxonomyData->label,
                        "taxonomy"        => $taxonomy,
                        "name"            => $taxonomy,
                        "orderby"         => "name",
                        "selected"        => $selected,
                        "show_count"      => true,
                        "hide_empty"      => true,
                        "hide_if_empty"   => true
                ));
            }
        }
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
