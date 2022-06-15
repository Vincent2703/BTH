<?php

class Agency {
    public function createAgency() {
        register_post_type("agency",
            array(
                "labels" => array(
                    "name"                  => "Agences",
                    "singular_name"         => "Une agence",
                    "add_new"               => "Ajouter",
                    "add_new_item"          => "Ajouter une agence",
                    "edit"                  => "Editer",
                    "edit_item"             => "Editer une agence",
                    "new_item"              => "Nouvelle agence",
                    "view"                  => "Voir",
                    "view_item"             => "Voir une agence",
                    "search_items"          => "Chercher des agences",
                    "not_found"             => "Aucune agence trouvée",
                    "not_found_in_trash"    => "Aucune agence trouvée dans la corbeille",
                    //"parent"                => "ads",
                    "all_items"             => "Toutes les agences",
                    "featured_image"        => "Photo de l'agence",
                    "set_featured_image"    => "Choisir une photo",
                    "remove_featured_image" => "Enlever la photo",
                    "use_featured_image"    => "Utiliser comme",
                ),

                "public" => true,
                "menu_position" => 16,
                "supports" => array("title", "thumbnail"),
                //"taxonomies" => array(""),
                "menu_icon" => "dashicons-admin-multisite",
                "has_archive" => false
            )
        );
    }
    
    function templatePostAgency($path) {
	if(get_post_type() == "agency") {
            if(is_single()) {
                //$this->registerPluginScriptsSingleAgency();
                //$this->registerPluginStylesSingleAgency();
                if($themeFile = locate_template(array('single_agency.php'))) {
                    $path = $path;
                }else{
                    $path = plugin_dir_path(__DIR__)."templates/single_agency.php";
                }
            }
	}
	return $path;
    }
}
