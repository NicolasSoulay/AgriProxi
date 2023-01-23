// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.scss";

// start the Stimulus application
import "./bootstrap";

//-------------- BURGER MENU --------------

//BURGER ICON
const burgerBouton = document.querySelector(".navburger");

//BURGER LIST
const burgerMenu = document.querySelector(".navlist");

// const main = document.querySelector("main");
// const footer = document.querySelector("footer");

//AFFICHAGE MENU AU CLICK
burgerBouton.addEventListener("click", () => {
  if (burgerMenu.classList.contains("transition_nav")) {
    burgerMenu.classList.remove("transition_nav");
  } else {
    burgerMenu.classList.add("transition_nav");
  }
});

//On crée un évènement pour pouvoir fermer le menu en cliquant n'importe où sur l'écran
// main.addEventListener("click", () => {
//   burgerMenu.classList.remove("transition_nav");
// });

// footer.addEventListener("click", () => {
//   burgerMenu.classList.remove("transition_nav");
// });

//---- SLIDER ----
const slide = ["s1.png", "s2.png", "s3.png", "s4.png"];
let numero = 0;

function ChangeSlide(sens) {
  numero = numero + sens;
  if (numero > slide.length - 1) {
    numero = 0;
  }
  if (numero < 0) {
    numero = slide.length - 1;
  }
  document.getElementById("slide").src = "build/images/" + slide[numero];
}

// setInterval(ChangeSlide(1), 4000);

const precedent = document.querySelector("#precedent");
const suivant = document.querySelector("#suivant");

precedent.addEventListener("click", () => {
  ChangeSlide(-1);
});

suivant.addEventListener("click", () => {
  ChangeSlide(1);
});

setInterval(ChangeSlide, 6500, 1);


