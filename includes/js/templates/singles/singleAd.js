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
    
    function DPEGES(diag, domID) {
        var smallDiag = $("<div>")
          .attr("id", domID+"Small")
          .css({"font-size": 0, "user-select": "none"});

        var valueDiag = $("#" + domID).text();

        var diagRanks = [];
        if(diag === "DPE") {
          diagRanks = [
            { min: 0, max: 50, color: "#319834", textColor: "#000000", label: 'A' },
            { min: 51, max: 90, color: "#33cc31", textColor: "#000000", label: 'B' },
            { min: 91, max: 150, color: "#cbfc34", textColor: "#000000", label: 'C' },
            { min: 151, max: 230, color: "#fbfe06", textColor: "#000000", label: 'D' },
            { min: 231, max: 330, color: "#fbcc05", textColor: "#000000", label: 'E' },
            { min: 331, max: 450, color: "#fc9935", textColor: "#000000", label: 'F' },
            { min: 451, max: 500, color: "#fc0205", textColor: "#ffffff", label: 'G' }
          ];
        } else {
          diagRanks = [
            { min: 0, max: 5, color: "#f2eff4", textColor: "#000000", label: 'A' },
            { min: 6, max: 10, color: "#dfc1f7", textColor: "#000000", label: 'B' },
            { min: 11, max: 20, color: "#d6aaf4", textColor: "#000000", label: 'C' },
            { min: 21, max: 35, color: "#cc93f4", textColor: "#000000", label: 'D' },
            { min: 36, max: 55, color: "#bb72f3", textColor: "#ffffff", label: 'E' },
            { min: 56, max: 80, color: "#a94cee", textColor: "#ffffff", label: 'F' },
            { min: 81, max: 100, color: "#8b1ae1", textColor: "#ffffff", label: 'G' }
          ];
        }

        $.each(diagRanks, function(index, rank) {
          var span = $("<span>")
            .text(rank.label)
            .css({"padding": "5px 7px 7px 5px", "font-size": "10px", "color": rank.textColor, "background-color": rank.color});

          if(valueDiag >= rank.min && valueDiag <= rank.max) {
            span.css({fontWeight: "bold", border: "white solid", fontSize: "15px"});
          } 

          smallDiag.append(span);              
        });

        $("#" + domID).append(smallDiag);
      }

      if($("#DPEValue").length) {
        DPEGES("DPE", "DPEValue");
      }
      if($("#GESValue").length) {
        DPEGES("GES", "GESValue");
      }
    

});    
