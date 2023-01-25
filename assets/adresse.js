import './styles/adresse.scss';

// Je déclare une constante correspondant à l'élément html de l'input label
const inputLabel = document.getElementById('label');

// Je déclare une constante correspondant à l'élément html de l'input code postal
const inputZip = document.getElementById('zip_code');

// Je déclare une constante correspondant à l'élément html de l'input ville
const inputCity = document.getElementById('ville');

// Je déclare une constante correspondant à l'élément html de l'input caché villeId
const inputId = document.getElementById('villeId');

// Je déclare les constantes correspondant aux éléments html des input cachés longitude et latitude
const inputLongitude = document.getElementById('longitude');
const inputLatitude = document.getElementById('latitude');

// Je déclare une constante correspondant à l'élément html où apparaîtront mes villes
const divCities = document.getElementById('displayCities');

// Je crée un écouteur afin que pour chaque input rentré dans le champs ville une requête est faite à la table ville de la BDD
inputCity.addEventListener('input', (event)=>{
    divCities.style.display = "block";
    let string = event.target.value;
    fetch('https://127.0.0.1:8000/adresse/ajax/ville/'+string)
    .then((response) => response.json())
    .then((json) => createDivCity(json))
})

// Fonction qui crée des div avec la ville et le code département depuis un json et qui ajoute un évènement au click afin que ça effectue une autocomplétion
function createDivCity(json){
    divCities.innerHTML = '';
    for(let i=0; i <json.length; i++){
        let nameCity = json[i].ville;
        let departementCity = json[i].codeDepartement;
        let idCity = json[i].id
        const city= document.createElement('div');
        city.classList.add('displayCity');
        city.setAttribute('id', json[i].id);
        city.textContent = nameCity+' - '+departementCity;
        divCities.appendChild(city);
        // Lorsque l'on clique sur la ville, l'appel à l'API s'effectue pour alimenter les champs longitude et latitude
        city.addEventListener('click', ()=> {
            inputCity.value = nameCity;
            inputId.value = idCity;
            let label = inputLabel.value;
            let zipCode = inputZip.value;
            let ville = inputCity.value;
            fetch('https://api-adresse.data.gouv.fr/search/?q='+label+zipCode+ville+'&type=street&autocomplete=0')
            .then((response) => response.json())
            .then((json) => addLongAndLat(json));
            divCities.style.display = "none";
        })
    }
}

// Fonction d'ajout de la longitude et latitude pour la soumission du formulaire
function addLongAndLat(json){
    inputLongitude.value = json.features[0].geometry.coordinates[0];
    inputLatitude.value = json.features[0].geometry.coordinates[1];
}