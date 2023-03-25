jQuery(function($) {
    $(document).ready(function(){
            $("#title").attr("maxLength", 64); //Pour limiter le titre à 64 caractères
            $("#insertAdPictures").click(open_media_window);
        });

    function open_media_window() {
        if (this.window === undefined) {
            this.window = wp.media({
                    title: "Choisissez les images à afficher",
                    library: {type: "image"},
                    multiple: true
                });

            var self = this; 
            inputImages = $("#images");
            this.window.on("select", function() {
                let showPictures = $("#showPictures");
                inputImages.val(null);
                showPictures.html(null);
                let attachments = self.window.state().get("selection").toJSON();
                attachments.forEach(elem => {
                    inputImages.val(inputImages.val()+elem.id+';');
                    showPictures.html(showPictures.html()+
                    '<div class="aPicture" data-imgId='+elem.id+'>'+
                        '<img src="'+elem.sizes.thumbnail.url+'" class="imgAd">'+
                        '<div class="controlPicture">'+
                            '<span class="moveToLeft" onclick="movePicture(this, \'left\');">←</span>'+
                            '<span class="deletePicture" onclick="deletePicture(this);">'+translations.delete+'</span>'+
                            '<span class="moveToRight" onclick="movePicture(this, \'right\');">→</span></div>'+
                        '</div>'+
                    '</div>');

                });
                inputImages.val(inputImages.val().slice(0, -1));
                $("#insertAdPictures").text(translations.replace);
            });
        }

    this.window.open();
    return false;
    }
});


function deletePicture(elem) {
    var pictureElem = jQuery(elem).parents()[1];
    var pictureId = jQuery(pictureElem).attr("data-imgId");
    var picturesElem = jQuery("#images");
    var picturesArray = jQuery(picturesElem).val().split(';');
    picturesArray.splice(picturesArray.findIndex(item=>item.toString()===pictureId), 1);
    pictureElem.remove();
    picturesElem.val(picturesArray.join(';'));
}

function movePicture(elem, dir) {
    var pictureElem = jQuery(elem).parents()[1];
    var pictureId = jQuery(pictureElem).attr("data-imgId");
    var picturesElem = jQuery("#images");
    var picturesArray = jQuery(picturesElem).val().split(';');
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