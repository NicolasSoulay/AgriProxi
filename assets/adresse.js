const inputVille = document.getElementById('adresse_creation_form_ville');
const value = inputVille.value;

inputVille.addEventListener('input', (event) => {
    value.textContent = event.target.value;
})