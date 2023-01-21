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

document.getElementById("prout").addEventListener("click", function(){
    fetch('https://127.0.0.1:8000/produit/ajax/subcat/12')
    .then((response) => response.json())
    .then((data) => console.log(data));
})

document.getElementById("pouet").addEventListener("click", function(){
    fetch('https://127.0.0.1:8000/produit/ajax/produitcat/12/produitsubcat/a')
    .then((response) => response.json())
    .then((data) => console.log(data));
})


//Affiche la sélection de sous-categories correspondant si une categorie à été choisie
document.getElementById("categorie").addEventListener("change", function() { 
    const categorie = document.getElementById("categorie")
    const subCategorie = document.getElementById("subCategorie")
    if (categorie.value != "") {
        subCategorie.style.visibility = "visible";
        getSubcategories(categorie.value);
        return
    }
    subCategorie.style.visibility = "hidden";
})

function getSubcategories(id) {
    return fetch('https://127.0.0.1:8000/produit/ajax/subcat/'+id)
        .then((response) => response.json())
        .then((json) => afficheSubcategories(json));
        
}

function afficheSubcategories(subCategories){
    let options = document.getElementsByClassName("dynamicSubCat");
    for (let i=0; i < options.length; i++) {
        options[i].remove();
    }
    subCategorie.innerHTML+= '<option class="dynamicSubCat" value="a">Aucune</option>';
    for (let i=0; i < subCategories.length; i++) {
        subCategorie.innerHTML+= '<option class="dynamicSubCat" value="'+subCategories[i][0]+'">'+subCategories[i][1]+'</option>'
        
    }
}



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