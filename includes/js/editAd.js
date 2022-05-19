jQuery(function($) {
    $(document).ready(function(){
            $('#insertAdPictures').click(open_media_window);
        });

    function open_media_window() {
         if (this.window === undefined) {
        this.window = wp.media({
                title: 'Choisissez les images à afficher',
                library: {type: 'image'},
                multiple: true
            });

        var self = this; 
        inputImages = document.getElementById("images");
        this.window.on('select', function() {
                let showPictures = document.getElementById("showPictures");
                inputImages.value = null;
                showPictures.innerHTML = null;
                let attachments = self.window.state().get('selection').toJSON();
                if(attachments.length > 4) {
                    console.log(attachments);
                }
                attachments.forEach(elem => {
                    inputImages.value += elem.id+";";
                    showPictures.innerHTML += '<img src="'+elem.sizes.thumbnail.url+'" class="imgAd">';
                });
                inputImages.value = inputImages.value.slice(0, -1);
            });
    }

    this.window.open();
    return false;
    }
});
