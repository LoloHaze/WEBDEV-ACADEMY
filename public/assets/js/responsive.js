window.addEventListener("load",()=> {
 
    
    //MENU HAMBURGUESA


const toggle = document.querySelector('.menu-toggle');
const menu = document.querySelector('.navbar-right');

toggle.addEventListener('click', () => {
    menu.classList.toggle('active');
});

})

