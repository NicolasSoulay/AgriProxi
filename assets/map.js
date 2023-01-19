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


//console.log(coordinatesProduits)

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

L.marker([latitude, longitude], {icon: redIcon, title: "you"}).addTo(map)

addProductMarker(coordinatesProduits);

//Affiche la sélection de sous-categories correspondant si une categorie à été choisie
document.getElementById("categorie").addEventListener("click", function(){ 
    categorie = document.getElementById("categorie")
    subCategorie = document.getElementById("subCategorie")

    if (categorie.value != "") {
        subCategorie.style.visibility = "visible";
        for (let i=0; i<subCategorie.length; i++) {
            if (subCategorie.options[i].className != categorie){
                const classname = subCategorie.options[i].className;
                console.log(classname)
                //faut que je trouve comment le cacher.......
                //subCategorie.options[i].;
            }
        }
    }
})





function addProductMarker(coordinates) {
    {
        for (let i=0; i < coordinates.length; i++){
            coordinates[i] = coordinates[i].split(",");
            
            const lat = coordinates[i][0];
            const long = coordinates[i][1];
            const nomEntreprise = coordinates[i][2]
            const idEntreprise = coordinates[i][3]

            let distance= map.distance([latitude, longitude],[lat, long])
            distance = Math.ceil(distance/1000)

            L.marker([lat, long], {title:idEntreprise+"|"+distance}).addTo(map).bindPopup("<a href='/entreprise/"+idEntreprise+"'>"+nomEntreprise+"</a><p>Distance: "+distance+"km</p>");
            document.getElementsByClassName(idEntreprise+nomEntreprise)[0].innerText = "Distance: "+distance+"km";
            
        }
    };
}