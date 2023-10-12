jQuery(document).ready(function($) {
    /*$(".RENotices button.notice-dismiss").click(function(e) {
        e.preventDefault();*/
    $(".RENotices p.closeNotice").click(function() {
        var noticeName = $(this).parent().attr("data-notice"); 
        $(this).text(translations.notBeDisplayedAnymore);
        $.ajax(ajaxurl, //admin-ajax.php path
        {
            type: "POST",
            data: {
                action: "dismissNoticeHandler",
                name: noticeName
            }
        }).done(function() {
            $(".RENotices[data-notice="+noticeName+"]").hide("slow");
        });
    });
});