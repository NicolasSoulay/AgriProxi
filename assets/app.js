/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.scss";

// start the Stimulus application
import "./bootstrap";

//-------------- BURGER MENU --------------

// On déclare une variable correspondant à l'élément du DOM du bouton burger
const burgerBouton = document.querySelector(".navburger");

// On déclare une variable correspondant à l'élément du DOM du menu burger
// const burgerMenu = document.getElementsByClassName("navbar_dekstop");
const burgerMenu = document.querySelector(".navlist");

//On déclare des variables pointant sur le main et le footer
const main = document.querySelector("main");
const footer = document.querySelector("footer");

//On crée un évènement sur le clic du bouton burger pour afficher le menu
burgerBouton.addEventListener("click", () => {
  if (burgerMenu.classList.contains("transition_nav")) {
    burgerMenu.classList.remove("transition_nav");
  } else {
    burgerMenu.classList.add("transition_nav");
  }
});

//On crée un évènement pour pouvoir fermer le menu en cliquant n'importe où sur l'écran
main.addEventListener("click", () => {
  burgerMenu.classList.remove("transition_nav");
});

footer.addEventListener("click", () => {
  burgerMenu.classList.remove("transition_nav");
});
