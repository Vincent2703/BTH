jQuery(document).ready(function ($) {
    /* Map */
    if($("#map").length) {
        var dataMap = $("#map").data("coords").split(",");
        let map = L.map("map",{  
            fullscreenControl: true,
            fullscreenControlOptions: {
                position: "topleft"
            }
        }).setView([dataMap[0], dataMap[1]], dataMap[2]);
        L.tileLayer("http://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png", {
            attribution: "Données cartographiques : <a href='https://www.openstreetmap.fr/mentions-legales'>OpenStreetMap.fr</a>"
        }).addTo(map);
        var circle = L.circle([dataMap[0], dataMap[1]], {
            color: "red",
            fillColor: "#f03",
            fillOpacity: 0.5,
            radius: dataMap[3]
        }).addTo(map);
    }


    /* Slider */
    var autoplay = $("#miniSlider ul li").length > 1;
   
    setInterval(function () {
        if(autoplay && jQuery("#miniSlider:hover").length===0) {
            moveSlide("right");
            pagingUpdate("right");
        }
    }, 4000);
    

    var slideCount = $("#miniSlider ul li").length;
    var slideWidth = $("#miniSlider ul li").width();
    var slideHeight = $("#miniSlider ul li").height();
    var currentImg = 1;
    var maxImg = $("#miniSlider ul li").length;

    $("#miniSlider").css({ maxWidth: slideWidth, height: slideHeight });

    $("#miniSlider ul").css({ width: "100vw" });
	
    //$("#miniSlider ul li:last-child").prependTo("#miniSlider ul");
    
    $(".pagingImg").text("1/" + maxImg);

    function moveSlide(direction) {
        if(direction === "left") {
            $("#miniSlider ul").animate({
                left: + slideWidth
            }, 200, function () {
                $("#miniSlider ul li:last-child").prependTo("#miniSlider ul");
                $("#miniSlider ul").css("left", '');          
            });
            
        if($("#fullscreenSlider").is(":visible")) {
            var largeImage = $("#miniSlider ul li:first-child img").attr("src")/*.attr("srcset").split(", ").find(e => e.includes("1024w")).replace(/ \d+w$/, '')*/;
            $("#fullscreenSlider .displayFullscreen").fadeOut("400", function() {
                $("#fullscreenSlider .displayFullscreen").css("background-image", "url("+largeImage+")");
            })
            .fadeIn(400);
        }
        }else{
            $("#miniSlider ul").animate({
                left: - slideWidth
            }, 200, function () {
                $("#miniSlider ul li:first-child").appendTo("#miniSlider ul");
                $("#miniSlider ul").css("left", '');
            });
            
            if($("#fullscreenSlider").is(":visible")) {
                var largeImage = $("#miniSlider ul li:nth-child(3) img").attr("src");
                $("#fullscreenSlider .displayFullscreen").fadeOut("400", function() {
                    $("#fullscreenSlider .displayFullscreen").css("background-image", "url("+largeImage+")");
                })
                .fadeIn(400);
            }
        }
       
    };

    $(".sliders .controlPrev").click(function() {
        moveSlide("left");
        pagingUpdate("left");
        autoplay = false;
    });

    $(".sliders .controlNext").click(function() {
        moveSlide("right");
        pagingUpdate("right");
        autoplay = false;
    });
    
    
    
    $("body").keyup(function() {
        $("#fullscreenSlider").hide("fast");
        $("body").css("overflow", '');
    });
    
    $("#fullscreenSlider .controlClose").click(function() {
        $("#fullscreenSlider").hide("fast");
        $("body").css("overflow", '');
    });
    
    function pagingUpdate(direction) {
        if(direction === "left") {
            if(currentImg === 1) {
                currentImg = maxImg;
            }else{
                currentImg--;
            }
        }else{
            if(currentImg === maxImg) {
                currentImg = 1;
            }else{
                currentImg++;
            }
        }
        $(".pagingImg").text(currentImg + "/" + maxImg);
    }
    
    
    var imgs = $("#miniSlider ul li img");
    $(imgs).click(function() {
        autoplay = false;
        var largeImage = $(this).attr("src");
        $("#fullscreenSlider .displayFullscreen").css("background-image", "url("+largeImage+")");
        $("#fullscreenSlider").show("slow"); //Remplacer par quelque chose qui permet de personnaliser l'effet
        setTimeout(function() { //Pour éviter que la page soit redimensionnée avant que l'image s'affiche en plein écran. On peut peut-être trouver mieux ?
            $("body").css("overflow", "hidden");
        }, 600);
    });
    
    /* More posts */
    $(".prevMorePosts, .nextMorePosts").click(function(e) {
        var target = $(e.target);
        var parent = target.parent();
        if(target.attr("class") === "prevMorePosts") {
            parent.hide();
            if(parent.prev().index() !== -1) {
                parent.prev().show();
            }else{
                parent.parent().find(".morePostsPanel:last").show();
               
            }
        }else if(target.attr("class")=== "nextMorePosts") {
            parent.hide();
            if(parent.next().index() !== -1) {
                parent.next().show();
            }else{
                parent.parent().find(".morePostsPanel:first").show();
            }
        }
    });
    
    //Doit convertir et rajouter dpeges ici
    

});    
