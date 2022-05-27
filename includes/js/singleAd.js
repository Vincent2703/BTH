/*let dataMap = document.getElementById("map").getAttribute("data-coord").split(",");
let map = L.map("map").setView([dataMap[0], dataMap[1]], dataMap[2]);
L.tileLayer("http://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png", {
    attribution: "Données cartographiques : <a href="https://www.openstreetmap.fr/mentions-legales">OpenStreetMap.fr</a>"
}).addTo(map);
let circle = L.circle([dataMap[0], dataMap[1]], {
    color: "red",
    fillColor: "#f03",
    fillOpacity: 0.5,
    radius: dataMap[3]
}).addTo(map);
*/

jQuery(document).ready(function ($) {
    
    let autoplay = true;
   
    setInterval(function () {
        if(autoplay) {
            moveRight();
        }
    }, 4000);
    

    var slideCount = $("#miniSlider ul li").length;
    var slideWidth = $("#miniSlider ul li").width();
    var slideHeight = $("#miniSlider ul li").height();
    var miniSliderUlWidth = slideCount * slideWidth;
    var currentImg = 1;
    var maxImg = $("#miniSlider ul li").length;

    $("#miniSlider").css({ width: slideWidth, height: slideHeight });

    $("#miniSlider ul").css({ width: miniSliderUlWidth, marginLeft: - slideWidth });
	
    $("#miniSlider ul li:last-child").prependTo("#miniSlider ul");
    
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
    
    $("body").keypress(function() {
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
        $("#fullscreenSlider").show("slow"); //Remplacer par quelque chose qui permet de personnaliser l"effet
        $("body").css("overflow", "hidden");
    });
    

});    
