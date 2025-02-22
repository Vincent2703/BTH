jQuery(document).ready(function($) {
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
            attribution: 'Données cartographiques : <a href="https://www.openstreetmap.fr/mentions-legales">OpenStreetMap.fr</a>'
        }).addTo(map);
        L.circle([dataMap[0], dataMap[1]], {
            color: "red",
            fillColor: "#f03",
            fillOpacity: 0.5,
            radius: dataMap[3]
        }).addTo(map);
    }

    var tabIsActive = true;
    document.addEventListener("visibilitychange", () => {
       tabIsActive = !document.hidden;
    });

    /* Slider */
    var autoplay = $("#miniSlider ul li").length > 1;
   
    setInterval(function () {
        if(tabIsActive && autoplay && $("#miniSlider:hover").length===0) {
            moveSlide("right");
            pagingUpdate("right");
        }
    }, 4000);
    
    var currentImg = 1;
    var maxImg = $("#miniSlider ul li").length;
	    
    $(".pagingImg").text("1/" + maxImg);
    
    /* REALMP apply button */
    if($("#applyBtn").length) {
        $("#applyForm").submit(function(event){
            if(!confirm(translations.confirm)){
                event.preventDefault();
            }
       });
    }

    function moveSlide(direction) {
        var slideWidth = $("#miniSlider ul li").width();

        if(direction === "left") {
            $("#miniSlider ul li:last-child").prependTo("#miniSlider ul");
            $("#miniSlider ul").css("left", -slideWidth).animate({
                left: 0
            }, 200);
           
        if($("#fullscreenSlider").is(":visible")) {
            var largeImage = $("#miniSlider ul li:last-child img").attr("src").replace(/-[0-9]+x[0-9]+/, '');
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
                var largeImage = $("#miniSlider ul li:nth-child(2) img").attr("src").replace(/-[0-9]+x[0-9]+/, '');
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
        var largeImage = $(this).attr("src").replace(/-[0-9]+x[0-9]+/, '');
        $("#fullscreenSlider .displayFullscreen").css("background-image", "url("+largeImage+")");
        $("#fullscreenSlider").show("slow", function() { $("body").css("overflow", "hidden"); }); 
    });
    
    /* Similar posts */
    const adsWrapper = $(".similarAdsWrapper");
    const prevButton = $(".prevMorePosts");
    const nextButton = $(".nextMorePosts");

    let currentIndex = 0;
    var adWidth = 20; 
    var containerWidth = adsWrapper.width();
    var maxVisibleAds = Math.min(Math.floor(containerWidth/160), 5);

    function updateButtonsVisibility() {
      const adCount = $("div.similarAd", adsWrapper).length;

      if(adCount > maxVisibleAds) {
      if(currentIndex === 0) {
        prevButton.hide();
      }else {
        prevButton.show();
      }

      if(currentIndex >= adCount - maxVisibleAds) {
        nextButton.hide();
      }else {
        nextButton.show();
      }
  }else{
      prevButton.hide();
      nextButton.hide();
  }
    }

    function updateSlider() { 
        containerWidth = adsWrapper.width();
        var adCount = $("div.similarAd", adsWrapper).length;
        var maxIndex = Math.max(adCount - maxVisibleAds, 1);
                
        maxVisibleAds = Math.min(Math.floor(containerWidth/160), 5);
        adWidth = 100/maxVisibleAds;

        currentIndex = Math.min(currentIndex, maxIndex);

        if(adCount <= maxVisibleAds) {
            adsWrapper.css("transform", "translateX(0)");
            adsWrapper.css("justify-content", "center");
            //$(".contentAd .similarAd").css("flex", "0 0 0%");
        }else {
            $(".contentAd .similarAd").css("flex", "0 0 "+adWidth+"%");
            var translateX = -currentIndex * adWidth;
            adsWrapper.css("transform", "translateX("+translateX+"%)");
            adsWrapper.css("justify-content", "");
        }
        updateButtonsVisibility();
    }

    prevButton.click(function () {
      if(currentIndex > 0) {
        currentIndex--;
        updateSlider();
      }
    });

    nextButton.click(function () {
      const adCount = $("div.similarAd", adsWrapper).length;
      if(currentIndex < adCount - maxVisibleAds) {
        currentIndex++;
        updateSlider();
      }
    });

    updateSlider();

    $(window).resize(updateSlider);

});    
