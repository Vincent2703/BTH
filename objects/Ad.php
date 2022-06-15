<?php

class Ad {
    
        public function registerPluginStylesSingleAd() {
            wp_register_style("leaflet", plugins_url(PLUGIN_RE_NAME."/includes/css/leaflet.min.css"), array(), "1.8.0");
            wp_register_style("leafletFullscreen", plugins_url(PLUGIN_RE_NAME."/includes/css/leafletFullscreen.min.css"), array(), "2.3.0");
            wp_register_style("singleAd", plugins_url(PLUGIN_RE_NAME."/includes/css/singleAd.css"));
            wp_register_style("googleIcons", "https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,1,0");
            wp_enqueue_style("leaflet");
            wp_enqueue_style("leafletFullscreen");
            wp_enqueue_style("singleAd");
            wp_enqueue_style("googleIcons");
        }

        public function registerPluginScriptsSingleAd() {
            wp_register_script("leaflet", plugins_url(PLUGIN_RE_NAME."/includes/js/leaflet.min.js"), array(), "1.8.0", true);
            wp_register_script("leafletFullscreen", plugins_url(PLUGIN_RE_NAME."/includes/js/leafletFullscreen.min.js"), array(), "2.3.0", true);
            wp_register_script("singleAd", plugins_url(PLUGIN_RE_NAME."/includes/js/singleAd.js"), array("jquery"), PLUGIN_RE_VERSION, true);
            wp_enqueue_script("leaflet");
            wp_enqueue_script("leafletFullscreen");
            wp_enqueue_script("singleAd");
        }
    
        public function createAd() {
        register_post_type("ad",
            array(
                "labels" => array(
                    "name"                  => "Annonces",
                    "singular_name"         => "Une annonce",
                    "add_new"               => "Ajouter une annonce",
                    "add_new_item"          => "Ajouter une annonce",
                    "edit"                  => "Editer",
                    "edit_item"             => "Editer une annonce",
                    "new_item"              => "Nouvelle annonce",
                    "view"                  => "Voir",
                    "view_item"             => "Voir une annonce",
                    "search_items"          => "Chercher des annonces",
                    "not_found"             => "Aucune annonce trouvée",
                    "not_found_in_trash"    => "Aucune annonce trouvée dans la corbeille",
                    //"parent"              => "ads",
                    "all_items"             => "Toutes les annonces",
                    "featured_image"        => "Miniature de l'annonce",
                    "set_featured_image"    => "Choisir une miniature",
                    "remove_featured_image" => "Enlever la miniature",
                    "use_featured_image"    => "Utiliser comme",
               ),

                "public" => true,
                "menu_position" => 15,
                "supports" => array("title", "editor", "thumbnail"),
                "menu_icon" => "dashicons-admin-home",
                "has_archive" => true
           )
       );
        register_taxonomy("adTypeProperty", array("ad"), array(
            "hierarchical"      => false, 
            "description"       => "Créez un type de bien pour catégoriser vos annonces.", 
            "label"             => "Types des biens immobiliers", 
            "show_admin_column" => true, 
            "show_in_menu"      => false,
            "singular_label"    => "Type de bien", 
            "rewrite"           => false,
            "meta_box_cb"       => array($this, "taxonomyMetaBoxCB")
       ));
        
        wp_insert_term("Appartement", "adTypeProperty");
        wp_insert_term("Bâtiment", "adTypeProperty");
        wp_insert_term("Boutique", "adTypeProperty");
        wp_insert_term("Bureaux", "adTypeProperty");
        wp_insert_term("Local", "adTypeProperty");
        wp_insert_term("Maison/villa", "adTypeProperty");
        wp_insert_term("Maison avec terrain", "adTypeProperty");
        wp_insert_term("Parking/box", "adTypeProperty");
        wp_insert_term("Terrain", "adTypeProperty");
        
        register_taxonomy("adTypeAd", array("ad"), array(
            "hierarchical"      => false, 
            "description"       => "Créez un type d'annonce pour catégoriser vos annonces.", 
            "label"             => "Types des annonces immobilières", 
            "show_admin_column" => true, 
            "show_in_menu"      => false,
            "singular_label"    => "Type d'annonce", 
            "rewrite"           => false,
            "meta_box_cb"       => array($this, "taxonomyMetaBoxCB")
       ));
        
        wp_insert_term("Location", "adTypeAd");
        wp_insert_term("Vente", "adTypeAd");
        wp_insert_term("Vente de prestige", "adTypeAd");
        
        register_taxonomy("adAvailable", array("ad"), array(
            "hierarchical"      => false, 
            "description"       => "Disponibilité de l'annonce.", 
            "label"             => "Disponibilité de l'annonce", 
            "show_admin_column" => true, 
            "show_in_menu"      => false,
            "singular_label"    => "Disponibilité de l'annonce", 
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
    
  
    function templatePostAd($path) {
	if(get_post_type() == "ad") {
            if(is_single()) {
                $this->registerPluginScriptsSingleAd();
                $this->registerPluginStylesSingleAd();
                if($themeFile = locate_template(array("single_ad.php"))) {
                    $path = $path;
                }else{
                    $path = plugin_dir_path(__DIR__)."templates/single_ad.php";
                }
            }
	}
	return $path;
    }
    
    function taxonomyMetaBoxCB($post, $taxonomy) {
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
    
    function taxonomyAdAvailableCheckboxCB($post/*, $taxonomy*/) {
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
        <label title="Le bien est disponible">
            <input type="checkbox" name="<?= $taxonomyName; ?>" value="<?php esc_attr_e($terms[0]->name); ?>" <?php checked($terms[0]->name, $name); ?>>
            <span>Le bien est disponible</span>
        </label>
        <?php
    }
    
    function filterAdsByTaxonomies() {
        global $typenow;
        $postType = "ad"; 
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
    
    
}
