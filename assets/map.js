/**ICI C'EST LES VARIABLES*/

import './styles/map.scss';

let latitudeUser = document.getElementById('latitude').value;
let longitudeUser = document.getElementById('longitude').value;
const zoomLevel = document.getElementById('zoomLevel').value;

const categorie = document.getElementById("categorie")
const subCategorie = document.getElementById("subCategorie")
const listeProduits = document.getElementById("search_result")
const adresseUser = document.getElementById("adresse")

const greenIcon = new L.Icon({
    iconUrl: 'build/images/marker-icon-2x-green.png',
    shadowUrl: 'build/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

const yellowIcon = new L.Icon({
    iconUrl: 'build/images/marker-icon-2x-gold.png',
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

const map = L.map('map').setView([latitudeUser, longitudeUser], zoomLevel); 





/**ICI C'EST LE SCRIPT*/


setMap();
addYourMarker();

//Affiche la sélection de sous-categories correspondant si une categorie à été choisie, et affiche des produits
categorie.addEventListener("change", function() { 
    produitRemove();
    markerRemove();
    if (categorie.value != "a") {
        subCategorie.style.visibility = "visible";
        subCategorie.value = "a"
        fetchSubcategories(categorie.value);
        fetchProduits(categorie.value, subCategorie.value)
        return;
    }
    if (categorie.value != "a") {
        subCategorie.value = "a"
    }
    subCategorie.style.visibility = "hidden";
    fetchProduits(categorie.value, subCategorie.value)
});

//Affiche des produits si on change la sous categorie
subCategorie.addEventListener("change", function() { 
    produitRemove();
    markerRemove();
    fetchProduits(categorie.value, subCategorie.value)
});

adresseUser.addEventListener("change", function() {
    produitRemove();
    markerRemove();
    markerAdresseUserRemove();
    fetchAdresseUser(adresseUser.value);
    fetchProduits(categorie.value, subCategorie.value)
});






/**ICI C'EST LES FONCTIONS */

/** RECHERCHE DE PRODUITS & AJAX*/ 

//on va fetch des sous-categories correspondante à la categorie selectionné
function fetchSubcategories(id) {
    return fetch('https://127.0.0.1:8000/produit/ajax/subcat/'+id)
        .then((response) => response.json())
        .then((json) => afficheSubcategories(json));
}


//on va fetch des produits correspondante à la categorie ou sous-categorie selectionné
function fetchProduits(idCat, idSubCat) {
    return fetch('https://127.0.0.1:8000/produit/ajax/produitcat/'+idCat+'/produitsubcat/'+idSubCat)
        .then((response) => response.json())
        .then((json) => afficheProduitsAndMarkers(json));
}

//on va fetch des produits correspondante à la categorie ou sous-categorie selectionné
function fetchAdresseUser(idAdresse) {
    return fetch('https://127.0.0.1:8000/produit/ajax/adresse/'+idAdresse)
        .then((response) => response.json())
        .then((json) => addMarkerAdresseUser(json));
}

//si notre selecteur à des options qu'on a crée, on les enleves, puis on recrée les nouvelles à partir de notre fetch
function afficheSubcategories(subCategories) {
    let options = document.getElementsByClassName("dynamicSubCat");
    let nbrOptionToRemove = options.length // on definie la longueur en dehors de la boucle, sinon on reduit l'iteration à chaque fois qu'on enleve un element
    for (let i=0; i < nbrOptionToRemove; i++) {
        options[0].remove(); //idem, on enleve que l'index 0, car js apparament réindexe les elements apres un remove
    }
    for (let i=0; i < subCategories.length; i++) {
        subCategorie.innerHTML+= '<option class="dynamicSubCat" value="'+subCategories[i].id+'">'+subCategories[i].name+'</option>'
        
    }
}

function afficheProduitsAndMarkers(json){
    if (json[0] === "salut ya rien"){
        return
    }
    afficheProduits(json.produits)
    addProductMarker(json.entreprises)
    console.log(json)
}

function afficheProduits(produits) {
    for (let i=0; i < produits.length; i++) {
        
        let image = produits[i].imageURL;
        if(image === null){
            image = 'https://127.0.0.1:8000/build/images/placeholder_612x612.jpg';
        }

        listeProduits.innerHTML+= 
            '<div class="product_card" id="'+produits[i].name+produits[i].id+'"><div class="product_desc"><div class="card_image"><img class="product_img" src="'+image+'" alt="product_image" width="200" height="200"></div><div class="card_text"><h3>'+produits[i].name+'</h3><a href="https://127.0.0.1:8000/entreprise/'+produits[i].entreprise.id+'">'+produits[i].entreprise.name+'</a><p>'+produits[i].desc+'</p><h3 classe="'+produits[i].entreprise.id+'distance"></h3></div></div><div class="btn_link" id="panTo'+produits[i].id+'">Voir sur la map</div><div class="card_btn"><a href="https://127.0.0.1:8000/devis/add/'+produits[i].id+'"><button class="btn_link_green">Ajouter à votre liste de commande</button><a></div></div>'
        ;
        
    }

}


/**MAP*/

function setMap(){
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
}

function addYourMarker(){
    L.marker([latitudeUser, longitudeUser], {title: "you"}).addTo(map)
}

function addMarkerAdresseUser(adresse){
    document.getElementById('latitude').value = adresse[0].latitude;
    document.getElementById('longitude').value = adresse[0].longitude;
    L.marker([adresse[0].latitude, adresse[0].longitude], {title: "you"}).addTo(map);
    map.panTo(new L.LatLng(adresse[0].latitude, adresse[0].longitude));
}
function addProductMarker(entreprises) {
    
    for (let i=0; i < entreprises.length; i++){
        const idEntreprise = entreprises[i].id;
        const nomEntreprise = entreprises[i].name;
        const adresseEntreprise = entreprises[i].adresse;
        const lat = entreprises[i].coordinates.latitude;
        const long = entreprises[i].coordinates.longitude;

        let distance= map.distance([document.getElementById('latitude').value, document.getElementById('longitude').value],[lat, long])
        distance = Math.ceil(distance/1000)
        let co2 = distance*120
        if (co2<10000){
            L.marker([lat, long], {icon: greenIcon,title:nomEntreprise}).addTo(map).bindPopup("<a href='/entreprise/"+idEntreprise+"'>"+nomEntreprise+"</a><p>"+adresseEntreprise.label+"</p><p>"+adresseEntreprise.zipCode+" "+adresseEntreprise.ville.name+"</p><p>Distance: "+distance+"km</p><p>Emission: "+co2+"g</p>");
        }
        if (10000<co2 && co2<20000){
            L.marker([lat, long], {icon: yellowIcon,title:nomEntreprise}).addTo(map).bindPopup("<a href='/entreprise/"+idEntreprise+"'>"+nomEntreprise+"</a><p>"+adresseEntreprise.label+"</p><p>"+adresseEntreprise.zipCode+" "+adresseEntreprise.ville.name+"</p><p>Distance: "+distance+"km</p><p>Emission: "+co2+"g</p>");
        }
        if (co2>20000){
            L.marker([lat, long], {icon: redIcon,title:nomEntreprise}).addTo(map).bindPopup("<a href='/entreprise/"+idEntreprise+"'>"+nomEntreprise+"</a><p>"+adresseEntreprise.label+"</p><p>"+adresseEntreprise.zipCode+" "+adresseEntreprise.ville.name+"</p><p>Distance: "+distance+"km</p><p>Emission: "+co2+"g</p>");
        }
    }
}

function produitRemove(){
    let cartesProduits = document.getElementsByClassName("product_card");
    let nbrProduitsToRemove = cartesProduits.length // on definie la longueur en dehors de la boucle, sinon on reduit l'iteration à chaque fois qu'on enleve un element
    for (let i=0; i < nbrProduitsToRemove; i++) {
        cartesProduits[0].remove(); //idem, on enleve que l'index 0, car js apparament réindexe les elements apres un remove
    }
}

function markerRemove() {
    let marker_pane = document.getElementsByClassName("leaflet-pane leaflet-marker-pane");
    let shadow_pane = document.getElementsByClassName("leaflet-pane leaflet-shadow-pane");
    let nbrMarkerToRemove = marker_pane[0].childElementCount;
    for (let i=1; i<nbrMarkerToRemove; i++) {
        marker_pane[0].lastElementChild.remove();
        shadow_pane[0].lastElementChild.remove();
    }
}

function markerAdresseUserRemove(){
    let marker_pane = document.getElementsByClassName("leaflet-pane leaflet-marker-pane");
    let shadow_pane = document.getElementsByClassName("leaflet-pane leaflet-shadow-pane");
    marker_pane[0].firstElementChild.remove();
    shadow_pane[0].firstElementChild.remove();
}
