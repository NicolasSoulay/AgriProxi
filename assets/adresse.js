import './styles/adresse.scss';

// Je déclare une constante correspondant à l'élément html de l'input ville
const inputCity = document.getElementById('adresse_creation_form_ville');

// Je déclare une constante correspondant à l'élément html où apparaîtront mes villes
const divCities = document.getElementById('displayCities');

// Je crée un écouteur afin que pour chaque input rentré dans le champs ville une requête est faite à la table ville de la BDD
inputCity.addEventListener('input', (event)=>{
    let string = event.target.value;
    fetch('https://localhost:8000/adresse/ajax/ville/'+string)
    .then((response) => response.json())
    .then((json) => createDivCity(json))
})

// Fonction qui crée des div avec la ville et le code département depuis un json
function createDivCity(json){
    divCities.innerHTML = '';
    for(let i=0; i <json.length; i++){
        divCities.innerHTML += `<div class="displayCity" id="${json[i].id}">${json[i].ville} - ${json[i].codeDepartement}</div>`
    }
}

//Je déclare une constante correspondant aux élément html des villes qui apparaissent selon les input rentrés dans ville
const displayCity = document.querySelectorAll(".displayCity");

// Je crée un écouteur pour qu'au click sur l'une des div ville, cela alimente le champs ville
displayCity.forEach(element => element.addEventListener('click',() => {
    //inputCity.value = element.value;
    console.log('hi');
}));