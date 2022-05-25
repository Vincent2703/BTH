/*let dataMap = document.getElementById("map").getAttribute("data-coord").split(',');
let map = L.map("map").setView([dataMap[0], dataMap[1]], dataMap[2]);
L.tileLayer('http://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
    attribution: 'Données cartographiques : <a href="https://www.openstreetmap.fr/mentions-legales">OpenStreetMap.fr</a>'
}).addTo(map);
let circle = L.circle([dataMap[0], dataMap[1]], {
    color: 'red',
    fillColor: '#f03',
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
    

    var slideCount = $('#slider ul li').length;
    var slideWidth = $('#slider ul li').width();
    var slideHeight = $('#slider ul li').height();
    var sliderUlWidth = slideCount * slideWidth;

    $('#slider').css({ width: slideWidth, height: slideHeight });

    $('#slider ul').css({ width: sliderUlWidth, marginLeft: - slideWidth });
	
    $('#slider ul li:last-child').prependTo('#slider ul');

    function moveLeft() {
        $('#slider ul').animate({
            left: + slideWidth
        }, 200, function () {
            $('#slider ul li:last-child').prependTo('#slider ul');
            $('#slider ul').css('left', '');
        });
    };

    function moveRight() {
        $('#slider ul').animate({
            left: - slideWidth
        }, 200, function () {
            $('#slider ul li:first-child').appendTo('#slider ul');
            $('#slider ul').css('left', '');
        });
    };

    $('span.control_prev').click(function () {
        moveLeft();
        autoplay = false;
    });

    $('span.control_next').click(function () {
        moveRight();
        autoplay = false;
    });
    
    
    var imgs = $("#slider ul li img");
    $(imgs).click(function() {
        var largeImage = $(this).attr("srcset").split(', ').at(-1).replace(/ \d+w$/, '');
        console.log(largeImage);
        $("#fullscreen").css("background-image", "url("+largeImage+")");
        //$("#fullscreen").css("background-size","640px");
        $("#fullscreen").show("slow", "swing"); //Remplacer par quelque chose qui permet de personnaliser l'effet
    });
    

});    
