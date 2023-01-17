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

const slide = ["Slide1.jpg", "Slide2.jpg", "Slide1.jpg", "Slide2.jpg"];
let numero = 0;

function ChangeSlide(sens) {
  alert("pouet");
  document.getElementById("slide").src = "images/Slide2.jpg";
}
/* <script>
const slide = [{{ asset('images/Slide1.jpg') }}, {{ asset('images/Slide2.jpg') }}];
let numero = 0;
{# function ChangeSlide(sens) {
  numero = numero + sens;
  if (numero < 0) numero = slide.length - 1;
  if (numero > slide.length - 1) numero = 0;
  setInterval("ChangeSlide(1)", 4000);
  document.getElementById("slide").src = slide[numero];
} #}
{# 'images/Slide2.jpg' #}
function ChangeSlide(sens) {
    
    document.getElementById("slide").src = {{ asset('images/Slide2.jpg') }}
} */
// </script>
