import './styles/user.scss';

//ELEMENTS ADRESSE
let modal = document.querySelector(".modal");
let btn = document.querySelectorAll(".mybtn");
let span = document.querySelectorAll(".close");

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

//ACTION BUTTON ADRESSE
btn.forEach(element => { 
  element.addEventListener("click", function () {
   //Ouverture de la modale
    modal.style.display = "block";
    // //getAttribute : recupere la valeur de data-href
    let href = this.getAttribute("data-href");
    let link = document.getElementById("link_del");
    // //setAttribute : affecte la valeur de href dans un element <a> de la modale
    link.setAttribute("href", href);
  })
});

//ELEMENTS USER
let modalUser = document.querySelector(".modal_user");
let btnUser = document.querySelectorAll(".mybtn_user");
let spanUser = document.querySelector('.close_user');

//CLOSE MODAL
spanUser.onclick = function () {
  modalUser.style.display = "none";
};

//CLICK EN DEHORS DE LA FENETRE
window.onclick = function (event) {
  if (event.target == modalUser) {
    modalUser.style.display = "none";
  }
};

//ACTION BUTTON USER
btnUser.forEach(element => { 
  element.addEventListener("click", function () {
   //Ouverture de la modale
    modalUser.style.display = "block";
    // //getAttribute : recupere la valeur de data-href
    let href = this.getAttribute("data-href");
    let link = document.getElementById("link_del_user");
    // //setAttribute : affecte la valeur de href dans un element <a> de la modale
    link.setAttribute("href", href);
  })
});
