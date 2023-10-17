// I didn't succeed with parent/submenu _file hook
jQuery(document).ready(function($) {        
    let searchParams = new URLSearchParams(window.location.search);
    if(searchParams.has("taxonomy")) {
        if(searchParams.get("taxonomy") === "adTypeProperty" || searchParams.get("taxonomy") === "adTypeAd") {
            let subMenu = $("#menu-posts-re-ad li:last-child");
            subMenu.addClass("current");
            $("a", subMenu).addClass("current");
        }
    }
});