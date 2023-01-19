import './styles/map.scss';

const latitude = document.getElementById('latitude').value;

const longitude = document.getElementById('longitude').value;

const zoomLevel = document.getElementById('zoomLevel').value;

const greenIcon = new L.Icon({
    iconUrl: 'build/images/marker-icon-2x-green.png',
    shadowUrl: 'build/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

const redIcon = new L.Icon({
    iconUrl: 'build/images/marker-icon-2x-red.png',
    shadowUrl: 'build/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});


console.log(coordinatesProduits)

let map = L.map('map').setView([latitude, longitude], zoomLevel); 
//zoom level: 
//12 = 30km -> r = 10km 
//11 = 75km -> r = 25km 
//10 = 150km -> r = 50km
//9 = 240km -> r = 100km

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

L.marker([latitude, longitude], {icon: redIcon}).addTo(map)

addProductMarker(coordinatesProduits);

function addProductMarker(coordinates) {
    {
        for (let i=0; i < coordinates.length; i++){
            coordinates[i] = coordinates[i].split(",");
            
            let lat= coordinates[i][0];
            let long= coordinates[i][1];
            L.marker([lat, long]).addTo(map).bindPopup("<a href='/entreprise/"+coordinates[i][3]+"'>"+coordinates[i][2]+"</a>");
        }
    };
}