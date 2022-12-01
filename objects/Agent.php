<?php

class Agent {
    public function createAgent() {
        register_post_type("agent",
            array(
                "labels" => array(
                    "name"                  => __("Agents", "retxtdom"),
                    "singular_name"         => __("An agent", "retxtdom"),
                    "add_new"               => __("Add", "retxtdom"),
                    "add_new_item"          => __("Add an agent", "retxtdom"),
                    "edit"                  => __("Edit", "retxtdom"),
                    "edit_item"             => __("Edit an agent", "retxtdom"),
                    "new_item"              => __("New agent", "retxtdom"),
                    "view"                  => __("View", "retxtdom"),
                    "view_item"             => __("View an agent", "retxtdom"),
                    "search_items"          => __("Search agents", "retxtdom"),
                    "not_found"             => __("No agents found", "retxtdom"),
                    "not_found_in_trash"    => __("No agents found in trash", "retxtdom"),
                    "all_items"             => __("All agents", "retxtdom"),
                    "featured_image"        => __("Agent thumbnail", "retxtdom"),
                    "set_featured_image"    => __("Choose a thumbnail", "retxtdom"),
                    "remove_featured_image" => __("Remove thumbnail", "retxtdom"),
                    "use_featured_image"    => __("Use as thumbnail", "retxtdom"),
                ),

                "public" => true,
                "menu_position" => 16,
                "supports" => array("title", "thumbnail"),
                "menu_icon" => "dashicons-businessperson",
                "has_archive" => false
            )
        );
    }
    
    public function templatePostAgent($path) {
	if(get_post_type() == "agent") {
            if(is_single()) {
                if(!locate_template(array("single-agent.php"))) {
                    $path = plugin_dir_path(__DIR__)."templates/singles/single-agent.php";
                    wp_register_style("singleAgent", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/singles/singleAgent.css"), array(), PLUGIN_RE_VERSION);
                    wp_enqueue_style("singleAgent");
                }
            }
	}
	return $path;
    }
    
        
    public function publicQueryAgentPostParent() {
        global $pagenow;
        global $typenow;
        if(is_admin() && $pagenow == "edit.php" && $typenow === "agent") {
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

        $columns["agency"] = __("Agency", "retxtdom");

        return $columns;
    }

    public function selectCustomAgentColumn($column, $postID) {
        if($column === "agency") {
            if(!empty($parent = get_post_parent($postID))) {
                echo "<a href='edit.php?post_type=agent&post_parent=".$parent->ID."'>".get_the_title($parent)."</a>";
            } else {
               _e("No assigned agency", "retxtdom");
            }
        }
    }
    
    
}
