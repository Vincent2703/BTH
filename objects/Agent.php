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
                "taxonomies" => array(""),
                "menu_icon" => "dashicons-businessperson",
                "has_archive" => false
            )
        );
    }
    
    function templatePostAgent($path) {
	if(get_post_type() == "agent") {
            if(is_single()) {
                $this->registerPluginScriptsSingleAd();
                $this->registerPluginStylesSingleAd();
                if($themeFile = locate_template(array('singleAgent.php'))) {
                    $path = $path;
                }else{
                    $path = plugin_dir_path(__DIR__)."templates/singleAgent.php";
                }
            }
	}
	return $path;
    }
}
