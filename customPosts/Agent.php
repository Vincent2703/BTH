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
    
}
