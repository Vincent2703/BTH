<?php 
if(!defined("ABSPATH")) {
    exit; //Exit if accessed directly
} 
get_header(); ?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main">
                <h3 class="color-accent"><?php _e("There is no results for this search."); ?></h3>
                <?php if(defined("PLUGIN_RE_REP") && PLUGIN_RE_REP && current_user_can("customer")) { ?>
                <span id="btnSubscribeAlert">
                    <button id="subscribeAlert"><?php _e("Save the search", "retxtdom"); ?></button>
                </span>
                <?php } ?> 
            </main>
        </div>
<?php get_footer(); ?>