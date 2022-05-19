let dataMap = document.getElementById("map").getAttribute("data-coord").split(',');
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
