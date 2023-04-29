jQuery(function($) {
    $(document).ready(function(){
            $("#title").attr("maxLength", 64); //Limit ad's title to 64 characteres
            $("#insertAdPictures").click(mediaManager); //Open the WP media manager
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

                const picturesHtml = attachments
                    .map((attachment) => {
                    inputImages.val((i, val) => '${val}${attachment.id};');

                    return `
                        <div class="aPicture" data-imgId="${attachment.id}">
                            <img src="${attachment.sizes.thumbnail.url}" class="imgAd">
                            <div class="controlPicture">
                                <span class="moveToLeft" onclick="movePicture(this, 'left');">←</span>
                                <span class="deletePicture" onclick="deletePicture(this);">${translations.delete}</span>
                                <span class="moveToRight" onclick="movePicture(this, 'right');">→</span>
                            </div>
                        </div>`;
                    })
                    .join("");

                showPictures.html(picturesHtml);
                inputImages.val((i, val) => val.slice(0, -1));
                $("#insertAdPictures").text(translations.replace);
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
    const pictureId = pictureElem.dataset.imgId;
    const picturesElem = jQuery("#images");
    const picturesArray = picturesElem.val().split(';');
    
    if(dir==="left") {
        var previous = jQuery(pictureElem).prev();
        if(previous.length >= 1) {
            jQuery(pictureElem).insertBefore(jQuery(previous));
            var previousPictureId = jQuery(previous).attr("data-imgId");
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
            var nextPictureId = jQuery(next).attr("data-imgId");
            var indexCurrent = picturesArray.findIndex(item=>item.toString()===pictureId);
            var indexNext = picturesArray.findIndex(item=>item.toString()===nextPictureId);
            picturesArray[indexCurrent] = nextPictureId;
            picturesArray[indexNext] = pictureId;
            picturesElem.val(picturesArray.join(';'));
        }     
    }
}