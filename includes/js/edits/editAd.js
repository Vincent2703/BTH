jQuery(function($) {
    $(document).ready(function(){
        $("#title").attr("maxLength", 64); //Limit ad's title to 64 characteres
        $("#insertAdPictures").click(mediaManager); //Open the WP media manager

        const selectStatus = $("#post_status");

        selectStatus.change(function(event) {
            let textOption = $("option:selected", this).text();      
            $("option:selected", this).attr("selected", "selected");
            $("#post-status-display").text(textOption); 
        });

        //MetaBox HF (REALMPLUS)
        if($("#adSubmissionMetaBox").length > 0) {
            $("#allowSubmission").click(function() {
                $("#needGuarantors").prop("disabled", !$("#allowSubmission").is(":checked"));
            }); 
        }
            
        $("#agents").click(function() {
            reloadAgents(this);
        });

        $("#post").submit(function(e) {
            if($("#addressOK").val() === "false") {
                e.preventDefault();
                $("html, body").animate({
                    scrollTop: $("#address").offset().top-200
                }, 900);
                $("#addressInput").css("border-color", "red");
            }
        });
        
        /*$("#addressInput").on("click", function() {
            $("#publish").prop("disabled", true);
        });*/
        
    });

    //Media manager - Add Id pictures to an hidden input
    function mediaManager() {
        if(this.window === undefined) {
            const inputImages = $("#images");
            const showPictures = $("#showPictures");

            this.window = wp.media({
                title: "Choisissez les images à afficher",
                library: {type: "image"},
                multiple: true
            });

            this.window.on("select", () => {
                const attachments = this.window.state().get("selection").toJSON();
                
                const pictureIds = attachments.map(attachment => attachment.id).join(";");
                inputImages.val(pictureIds);

                const picturesHtml = attachments
                    .map((attachment) => {
                    return `
                        <div class="aPicture" data-imgid="${attachment.id}">
                            <img src="${attachment.sizes.thumbnail.url}" class="imgAd">
                            <div class="controlPicture">
                                <span class="moveToLeft" onclick="movePicture(this, 'left');">←</span>
                                <span class="deletePicture" onclick="deletePicture(this);">${variablesEditAd.delete}</span>
                                <span class="moveToRight" onclick="movePicture(this, 'right');">→</span>
                            </div>
                        </div>`;
                    })
                    .join("");

                showPictures.html(picturesHtml);
                $("#insertAdPictures").text(variablesEditAd.replace);
            });
        }

        this.window.open();
        return false;
    }
});


//Delete a picture from the list
function deletePicture(elem) {
    const pictureElem = elem.closest('.aPicture');
    const pictureId = pictureElem.dataset.imgid;
    const picturesElem = jQuery('#images');
    const picturesArray = picturesElem.val().split(';');
    const pictureIndex = picturesArray.indexOf(pictureId);

    if(pictureIndex > -1) {
        picturesArray.splice(pictureIndex, 1);
    }
    pictureElem.remove();
    picturesElem.val(picturesArray.join(';'));
}

//Move a picture in the list
function movePicture(elem, dir) {
    const pictureElem = jQuery(elem).parents()[1];
    const pictureId = pictureElem.dataset.imgid;
    const picturesElem = jQuery("#images");
    const picturesArray = picturesElem.val().split(';');
    
    if(dir==="left") {
        var previous = jQuery(pictureElem).prev();
        if(previous.length >= 1) {
            jQuery(pictureElem).insertBefore(jQuery(previous));
            var previousPictureId = jQuery(previous).attr("data-imgid");
            var indexCurrent = picturesArray.findIndex(item=>item.toString()===pictureId);
            var indexPrevious = picturesArray.findIndex(item=>item.toString()===previousPictureId);
            picturesArray[indexCurrent] = previousPictureId;
            picturesArray[indexPrevious] = pictureId;
            picturesElem.val(picturesArray.join(';'));
        }
    }else{
        var next = jQuery(pictureElem).next();
        if(next.length >= 1) {
            jQuery(pictureElem).insertAfter(jQuery(next));
            var nextPictureId = jQuery(next).attr("data-imgid");
            var indexCurrent = picturesArray.findIndex(item=>item.toString()===pictureId);
            var indexNext = picturesArray.findIndex(item=>item.toString()===nextPictureId);
            picturesArray[indexCurrent] = nextPictureId;
            picturesArray[indexNext] = pictureId;
            picturesElem.val(picturesArray.join(';'));
        }     
    }
}

//Reload the list of agents
function reloadAgents(select) {
    let agentSelected = parseInt(jQuery(":selected", jQuery(select)).val());
    jQuery.ajax({
        url: variablesEditAd.URLAPIGetAgents,
        data: { nonce: jQuery("#reloadNonce").val() },
        type: "POST",
        dataType: "json"
    }).success(function(response) {
        jQuery(select).empty();
        response.forEach(function(val) {
            jQuery("<option/>")
                .val(val.ID)
                .text(val.display_name)
                .appendTo(select);
            }
        );
        jQuery('option[value="'+agentSelected+'"]', jQuery(select)).attr("selected", "selected");
    });
}