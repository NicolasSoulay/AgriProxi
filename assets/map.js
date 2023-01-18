import './styles/map.scss';

const latitude = document.getElementById('latitude').value;

const longitude = document.getElementById('longitude').value;

const zoomLevel = document.getElementById('zoomLevel').value;

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

