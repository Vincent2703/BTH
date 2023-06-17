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
    
    private function registerPluginStylesSingleAgency($path) {
        wp_register_style("singleAgency", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/$path/singles/singleAgency.css"), array(), PLUGIN_RE_VERSION);
        wp_enqueue_style("singleAgency");
    }
    
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
    public function templatePostAgency($path) {
        if(defined("PLUGIN_RE_THEME")) {
            $shortPath = PLUGIN_RE_THEME["name"].'/'.PLUGIN_RE_THEME["version"];
            $fullPath = PLUGIN_RE_PATH."templates/$shortPath";
            if(is_dir($fullPath)) {
                if(get_post_type() === "agency") {
                    if(is_single() && !locate_template(array("single-agency.php"))) {
                        $path = "$fullPath/singles/single-agency.php";
                        $this->registerPluginStylesSingleAgency($shortPath);
                    }
                }
            }
        }
        return $path;
    }
}
