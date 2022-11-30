<?php

class Ad {
    
        public function registerPluginStylesSingleAd() {
            wp_register_style("leaflet", plugins_url(PLUGIN_RE_NAME."/includes/css/others/leaflet.min.css"), array(), "1.8.0");
            wp_register_style("leafletFullscreen", plugins_url(PLUGIN_RE_NAME."/includes/css/others/leafletFullscreen.min.css"), array(), "2.3.0");
            wp_register_style("singleAd", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/singles/singleAd.css"));
            wp_register_style("googleIcons", "https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,1,0");
            wp_enqueue_style("leaflet");
            wp_enqueue_style("leafletFullscreen");
            wp_enqueue_style("singleAd");
            wp_enqueue_style("googleIcons");
        }

        public function registerPluginScriptsSingleAd() {
            wp_register_script("leaflet", plugins_url(PLUGIN_RE_NAME."/includes/js/others/leaflet.min.js"), array(), "1.8.0", true);
            wp_register_script("leafletFullscreen", plugins_url(PLUGIN_RE_NAME."/includes/js/others/leafletFullscreen.min.js"), array(), "2.3.0", true);
            wp_register_script("singleAd", plugins_url(PLUGIN_RE_NAME."/includes/js/templates/singles/singleAd.js"), array("jquery"), PLUGIN_RE_VERSION, true);
            wp_register_script("dpeges", plugins_url(PLUGIN_RE_NAME."/includes/js/others/dpeges.js"), array(), PLUGIN_RE_VERSION, true);
            wp_enqueue_script("leaflet");
            wp_enqueue_script("leafletFullscreen");
            wp_enqueue_script("singleAd");
            wp_enqueue_script("dpeges");
        }
    
        public function createAd() {
        register_post_type("re-ad",
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
                    "featured_image"        => __("Ad thumbnail", "retxtdom"),
                    "set_featured_image"    => __("Choose a thumbnail", "retxtdom"),
                    "remove_featured_image" => __("Remove thumbnail", "retxtdom"),
                    "use_featured_image"    => __("Use as thumbnail", "retxtdom"),
               ),

                "public" => true,
                "menu_position" => 15,
                "supports" => array("title", "editor", "thumbnail"),
                "menu_icon" => "dashicons-admin-home",
                "has_archive" => true
            )
        );
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
        
        wp_insert_term("Appartement", "adTypeProperty"); //TODO : Trad ?
        wp_insert_term("Bâtiment", "adTypeProperty");
        wp_insert_term("Boutique", "adTypeProperty");
        wp_insert_term("Bureaux", "adTypeProperty");
        wp_insert_term("Local", "adTypeProperty");
        wp_insert_term("Maison/villa", "adTypeProperty");
        wp_insert_term("Maison avec terrain", "adTypeProperty");
        wp_insert_term("Parking/box", "adTypeProperty");
        wp_insert_term("Terrain", "adTypeProperty");
        
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
        
        wp_insert_term("Location", "adTypeAd");
        wp_insert_term("Vente", "adTypeAd");
        wp_insert_term("Vente de prestige", "adTypeAd");
        
        register_taxonomy("adAvailable", array("re-ad"), array(
            "hierarchical"      => false, 
            "description"       => __("Property availability", "retxtdom"), 
            "label"             => __("Property availability", "retxtdom"), 
            "show_admin_column" => true, 
            "show_in_menu"      => false,
            "singular_label"    => __("Property availability", "retxtdom"), 
            "rewrite"           => false,
            "meta_box_cb"       => array($this, "taxonomyAdAvailableCheckboxCB"),
            "default_term"      => "Disponible"
        ));
        
        wp_insert_term("Disponible", "adAvailable");
        wp_insert_term("Indisponible", "adAvailable");

    }
    
    public function showPage() {
    ?>

    <div class="wrap">
        <h2>BTH Accueil</h2>
        <p>Bien le bonjour</p>
        <?php settings_errors(); ?>
    </div>
    <?php }
    
  
    public function templatePostAd($path) {
	if(get_post_type() == "re-ad") {
            if(is_single()) {
                if(!locate_template(array("single-ad.php"))) {
                    $path = plugin_dir_path(__DIR__)."templates/singles/single-re-ad.php";
                    $this->registerPluginScriptsSingleAd();
                    $this->registerPluginStylesSingleAd();
                }
            }else if(is_post_type_archive("re-ad")) { 
                if(!locate_template(array("archive-re-ad.php"))) {
                    $path = plugin_dir_path(__DIR__)."templates/archives/archive-re-ad.php";
                    wp_register_style("archiveAd", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/archives/archiveAd.css"), array(), PLUGIN_RE_VERSION);
                    wp_enqueue_style("archiveAd");
                }
            }
	}
	return $path;
    }
    
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
                <input type="radio" name="<?= $taxonomyName; ?>" value="<?php esc_attr_e($term->name); ?>" <?php checked($term->name, $name); ?>>
                    <span><?php esc_html_e($term->name); ?></span>
            </label><br>
        <?php
        }
    }
    
    public function taxonomyAdAvailableCheckboxCB($post/*, $taxonomy*/) {
        $taxonomyName = "adAvailable";
        $terms = get_terms($taxonomyName, array("hide_empty" => false));
	$term = wp_get_object_terms($post->ID, $taxonomyName, array("orderby" => "term_id", "order" => "ASC"));
	$name  = '';
        if(!is_wp_error($term)) {
            if(isset($term[0]) && isset($term[0]->name)) {
                $name = $term[0]->name;
            }
        }

        ?>
        <label title='<?php _e("The property is available", "retxtdom");?>'>
            <input type="checkbox" name="<?= $taxonomyName; ?>" value="<?php esc_attr_e($terms[0]->name); ?>" <?php checked($terms[0]->name, $name); ?>>
            <span><?php _e("The property is available", "retxtdom");?></span>
        </label>
        <?php
    }
    
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
    
    public function searchAds($query) {
        if(!is_admin() && $query->is_search && isset($_GET["post_type"]) && $_GET["post_type"] === "re-ad") {        
            $query->set("post_type", "re-ad");
            
            $terms = array();
            $metas = array();
            
            if(isset($_GET["typeAd"]) && !empty($_GET["typeAd"])) {
                array_push($terms,
                    array(
                        "taxonomy" => "adTypeAd",
                        "field" => "slug",
                        "terms" => sanitize_text_field($_GET["typeAd"])
                    )
                );            
            }
            if(isset($_GET["typeProperty"]) && !empty($_GET["typeProperty"])) {
                array_push($terms,
                    array(
                        "taxonomy" => "adTypeProperty",
                        "field" => "slug",
                        "terms" => sanitize_text_field($_GET["typeProperty"])
                    )
                );
            }
            if(isset($_GET["minSurface"]) && isset($_GET["maxSurface"]) && is_numeric($_GET["minSurface"]) && is_numeric($_GET["maxSurface"])) {
                array_push($metas,
                    array(
                        "key" => "adSurface",
                        "value" => array(intval($_GET["minSurface"]), intval($_GET["maxSurface"])),
                        "compare" => "BETWEEN",
                        "type" => "DECIMAL"
                    )
                );
            }         
            if(isset($_GET["minPrice"]) && isset($_GET["maxPrice"]) && is_numeric($_GET["minPrice"]) && is_numeric($_GET["maxPrice"])) {
                array_push($metas,
                    array(
                        "key" => "adPrice",
                        "value" => array(intval($_GET["minPrice"]), intval($_GET["maxPrice"])),
                        "compare" => "BETWEEN",
                        "type" => "DECIMAL"
                    )
                );
            } 
            if(isset($_GET["city"]) && isset($_GET["radius"]) && !empty($_GET["city"]) && !empty($_GET["radius"])) {
                $city = sanitize_text_field($_GET["city"]);
                $radius = intval($_GET["radius"]);
                $url = "https://api-adresse.data.gouv.fr/search/?q=".$city."&type=municipality&limit=1"; 
                $apiResponse = json_decode(wp_remote_retrieve_body(wp_remote_get($url)), true);
                if(isset($apiResponse["features"])) {
                    $coordsGPS = $apiResponse["features"][0]["geometry"]["coordinates"];
                    $minLat = $coordsGPS[1]-$radius/111;
                    $maxLat = $coordsGPS[1]+$radius/111;
                    $minLong = $coordsGPS[0]-$radius/76;
                    $maxLong = $coordsGPS[0]+$radius/76;
                    array_push($metas,
                        array(
                            "key" => "adLatitude",
                            "value" => array($minLat, $maxLat),
                            "compare" => "BETWEEN"
                        )
                    );
                    array_push($metas,
                        array(
                            "key" => "adLongitude",
                            "value" => array($minLong, $maxLong),
                            "compare" => "BETWEEN"
                        )
                    );
                } 
            }
                           
            if(!empty($terms)) {
                $query->set("tax_query", array($terms));
            }
            if(!empty($metas)) {
                $query->set("meta_query", array($metas));
            }
        }
    }
    
}
