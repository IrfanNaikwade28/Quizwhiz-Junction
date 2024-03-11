var menuBtn = document.querySelector("#menuIcon");
var menu = document.querySelector("header .menu-collapse");
let click = 1;

menuBtn.addEventListener("click",function(){
    if(click == 1){
        menu.style = "display:flex";
        click = 0;
        menuBtn.className = "fa-solid fa-close menu-icon";
    }
    else if(click == 0){
        menu.style = "display:none";
        click = 1;
        menuBtn.className = "fa-solid fa-bars menu-icon";
    }
});
