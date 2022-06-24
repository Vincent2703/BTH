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
    }
    
    public function templatePostAgent($path) {
	if(get_post_type() == "agent") {
            if(is_single()) {
                if($themeFile = locate_template(array('single-agent.php'))) {
                    $path = $path;
                }else{
                    $path = plugin_dir_path(__DIR__)."templates/single-agent.php";
                }
            }
	}
	return $path;
    }
    
        
    public function publicQueryAgentPostParent() {
        global $pagenow;
        $postType = $_GET["post_type"];
        var_dump($postType);
        if (is_admin() && $pagenow == "edit.php" && $postType === "agent") {
            $GLOBALS["wp"]->add_query_var("post_parent");
        }
    }

    public function agentFilterByAgency() {
        global $wpdb;
        if (isset($_GET["post_type"]) && $_GET["post_type"] === "agent") {
            $sql = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE post_type = 'agency' AND post_parent = 0 AND post_status = 'publish' ORDER BY post_title";
            $parent_pages = $wpdb->get_results($sql, OBJECT_K);
            $select = '
                <select name="post_parent">
                    <option value="">Agences</option>';
                    $current = isset($_GET['post_parent']) ? $_GET['post_parent'] : '';
                    foreach ($parent_pages as $page) {
                        $select .= sprintf('<option value="%s"%s>%s</option>', $page->ID, $page->ID == $current ? ' selected="selected"' : '', $page->post_title);
                    }
            $select .= '
                </select>';
            echo $select;
        } else {
            return;
       }
    }
    
    
    public function customAgentColumn($columns) {
        $columns["agency"] = "agency";
        return $columns;
    }
    
    
    public function customAgentSortableColumns($columns) {
        unset($columns["date"]);

        $columns["agency"] = "Agence";

        return $columns;
    }

    public function selectCustomAgentColumn($column, $postID) {
        if($column === "agency") {
            if(!empty($parent = get_post_parent($postID))) {
                echo "<a href='edit.php?post_type=agent&post_parent=".$parent->ID."'>".get_the_title($parent)."</a>";
            } else {
               echo "Pas d'agence attribuée";
            }
        }
    }
    
    
}
