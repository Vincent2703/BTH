<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Agency class
 * 
 */
class REALM_Agency {
    
    /*
     * Create the custom post Agency
     */
    public function createAgency() {
        register_post_type("agency",
            array(
                "labels" => array(
                    "name"                  => __("Agencies", "retxtdom"),
                    "singular_name"         => __("An agency", "retxtdom"),
                    "add_new"               => __("Add", "retxtdom"),
                    "add_new_item"          => __("Add an agency", "retxtdom"),
                    "edit"                  => __("Edit", "retxtdom"),
                    "edit_item"             => __("Edit an agency", "retxtdom"),
                    "new_item"              => __("New agency", "retxtdom"),
                    "view"                  => __("View", "retxtdom"),
                    "view_item"             => __("View an agency", "retxtdom"),
                    "search_items"          => __("Search agencies", "retxtdom"),
                    "not_found"             => __("No agencies found", "retxtdom"),
                    "not_found_in_trash"    => __("No agencies found in trash", "retxtdom"),
                    "all_items"             => __("All agencies", "retxtdom"),
                    "featured_image"        => __("Agency's thumbnail", "retxtdom"),
                    "set_featured_image"    => __("Choose a thumbnail", "retxtdom"),
                    "remove_featured_image" => __("Remove the thumbnail", "retxtdom"),
                    "use_featured_image"    => __("Use as thumbnail", "retxtdom"),
                ),

                "public" => true,
                "menu_position" => 18,
                "supports" => array("title", "editor", "thumbnail"),
                "menu_icon" => "dashicons-admin-multisite",
                "has_archive" => false
            )
        );
    }
    
    /*
     * Fetch the single custom post Agency template
     */
    function templatePostAgency($path) {
        $dirTemplates = PLUGIN_RE_PATH."templates/";
        $currentTheme = wp_get_theme();
        $themeName = str_replace(' ', '', strtolower($currentTheme->name));
        $themeVersion = $currentTheme->version;

        if(is_dir($dirTemplates.$themeName)) {
            $listDirVersions = array_diff(scandir($dirTemplates.$themeName), array('.', ".."));

            if(in_array($themeVersion, $listDirVersions)) {
                $dirPath = "$themeName/$themeVersion";
            }else {
                $dirVersion = end($listDirVersions);
                $dirPath = "$themeName/$dirVersion";
            }

            $dirFullPath = "$dirTemplates$dirPath";

            if(get_post_type() === "agency" && is_single() && !locate_template(array("single-agency.php"))) {
                $path = "$dirFullPath/singles/single-agency.php";
                wp_register_style("singleAgency", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/$dirPath/singles/singleAgency.css"), array(), PLUGIN_RE_VERSION);
                wp_enqueue_style("singleAgency");
            }
        }

        return $path;
    }
}
