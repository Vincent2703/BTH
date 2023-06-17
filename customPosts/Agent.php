<?php
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
/*
 * 
 * Agent class
 * 
 */
class REALM_Agent {
    
    private function registerPluginStylesSingleAgenct($path) {
        wp_register_style("singleAgent", plugins_url(PLUGIN_RE_NAME."/includes/css/templates/$path/singleAgent.css"), array(), PLUGIN_RE_VERSION);
        wp_enqueue_style("singleAgent");
    }
    
    /*
     * Create the custom post Agent
     */
    public function createAgent() {
        register_post_type("agent",
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
    public function templatePostAgent($path) {
        if(defined("PLUGIN_RE_THEME")) {
            $shortPath = PLUGIN_RE_THEME["name"].'/'.PLUGIN_RE_THEME["version"];
            $fullPath = PLUGIN_RE_PATH."templates/$shortPath";
            if(is_dir($fullPath)) {
                if(get_post_type() === "agency") {
                    if(is_single() && !locate_template(array("single-agent.php"))) {
                        $path = "$fullPath/singles/single-agent.php";
                        $this->registerPluginStylesSingleAgency($shortPath);
                    }
                }
            }
        }
        return $path;
    }  
    
}
