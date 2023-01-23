import './styles/user.scss';

let modal = document.querySelector(".modal");
let btn = document.querySelectorAll(".mybtn");
let span = document.querySelectorAll(".close")[0];

//CLOSE MODAL
span.onclick = function () {
  modal.style.display = "none";
};

//CLICK EN DEHORS DE LA FENETRE
window.onclick = function (event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
};

//ACTION BUTTON 
btn.forEach(element => { 
  element.addEventListener("click", function () {
    console.log('pouet');
   //Ouverture de la modale
    modal.style.display = "block";
    // //getAttribute : recupere la valeur de data-href
    let href = this.getAttribute("data-href");
    let link = document.getElementById("link_del");
    // //setAttribute : affecte la valeur de href dans un element <a> de la modale
    link.setAttribute("href", href);
  })
});
