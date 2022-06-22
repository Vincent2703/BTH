<?php

class Agent {
    public function createAgent() {
        register_post_type("agent",
            array(
                "labels" => array(
                    "name"                  => "Agents",
                    "singular_name"         => "Un agent",
                    "add_new"               => "Ajouter",
                    "add_new_item"          => "Ajouter un agent",
                    "edit"                  => "Editer",
                    "edit_item"             => "Editer un agent",
                    "new_item"              => "Nouvel agent",
                    "view"                  => "Voir",
                    "view_item"             => "Voir un agent",
                    "search_items"          => "Chercher des agents",
                    "not_found"             => "Aucun agent trouvé",
                    "not_found_in_trash"    => "Aucun agent trouvé dans la corbeille",
                    //"parent"                => "ads",
                    "all_items"             => "Tous les agents",
                    "featured_image"        => "Avatar de l'agent",
                    "set_featured_image"    => "Choisir un avatar",
                    "remove_featured_image" => "Enlever l'avatar",
                    "use_featured_image"    => "Utiliser comme",
                ),

                "public" => true,
                "menu_position" => 16,
                "supports" => array("title", "thumbnail"),
                //"taxonomies" => array(""),
                "menu_icon" => "dashicons-businessperson",
                "has_archive" => false
            )
        );
        register_taxonomy("agentAgency", array("agent"), array(
            "hierarchical"      => false, 
            "description"       => "L'agence à laquelle appartient l'agent.", 
            "label"             => "Agence", 
            "show_admin_column" => true, 
            "show_in_menu"      => false,
            "show_ui"           => false,
            "singular_label"    => "Agence", 
            "rewrite"           => false
       ));
    }
    
    function templatePostAgent($path) {
	if(get_post_type() == "agent") {
            if(is_single()) {
                $this->registerPluginScriptsSingleAd();
                $this->registerPluginStylesSingleAd();
                if($themeFile = locate_template(array('single-agent.php'))) {
                    $path = $path;
                }else{
                    $path = plugin_dir_path(__DIR__)."templates/single-agent.php";
                }
            }
	}
	return $path;
    }
    
    
    function filterAgentByAgency() {
        global $typenow;
        $postType = "agent"; 
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

    function convertIdToTermInQuery($query) {
        global $typenow;
        global $pagenow;

        $taxonomies = get_taxonomies(["object_type" => ["agent"]]);

        foreach($taxonomies as $taxonomy) {
            if($pagenow == "edit.php" && $typenow == "agent" && isset($_GET[$taxonomy]) && is_numeric($_GET[$taxonomy]) && $_GET[$taxonomy] != 0) {
                $taxQuery = array(
                        "taxonomy" => $taxonomy,
                        "terms"    => array( $_GET[$taxonomy] ),
                        "field"    => "id",
                        "operator" => "IN",
                );
                $query->tax_query->queries[] = $taxQuery; 
                $query->query_vars["tax_query"] = $query->tax_query->queries;
            }
        }

    }
}
