// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.scss";

// start the Stimulus application
import "./bootstrap";

//-------------- BURGER MENU --------------
//BURGER ICON
const burgerBouton = document.querySelector(".navburger");

//BURGER LIST
const burgerMenu = document.querySelector(".navlist");

const main = document.querySelector("main");
const footer = document.querySelector("footer");

//AFFICHAGE MENU AU CLICK
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


//-------------- SLIDER --------------
let slideIndex = 0;
showSlides();

function showSlides() {
  let i;
  let slides = document.getElementsByClassName("slide");
  let title = document.getElementsByClassName("banner_title");
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
    title[i].style.display = "none";
  }
  slideIndex++;
  if (slideIndex > slides.length) {slideIndex = 1}
  slides[slideIndex-1].style.display = "block";
  title[slideIndex-1].style.display = "block";
  setTimeout(showSlides, 4000);
}

