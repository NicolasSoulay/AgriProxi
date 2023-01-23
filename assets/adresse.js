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

// Fonction qui crée des div avec la ville et le code département depuis un json et qui ajoute un évènement au click afin que ça effectue une autocomplétion
function createDivCity(json){
    divCities.innerHTML = '';
    for(let i=0; i <json.length; i++){
        let nameCity = json[i].ville;
        let departementCity = json[i].codeDepartement;
        const city= document.createElement('div');
        city.classList.add('displayCity');
        city.setAttribute('id', json[i].id);
        city.textContent = nameCity+' - '+departementCity;
        divCities.appendChild(city);
        city.addEventListener('click', ()=> {
            inputCity.value = nameCity;
        })
    }
}