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
                "public" => false,
                "has_archive" => false,
                "publicly_queryable" => false,
                "query_var" => false,
                "exclude_from_search" => true
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
