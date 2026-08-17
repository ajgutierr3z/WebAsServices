const dialogRegistro = document.getElementById("dialogRegistro");
const cambioDialogRegistro = document.getElementById("cambioDialogRegistro");
const inputLoginPeticion = document.getElementById("loginPeticion");
const formularioLogin = dialogRegistro.getElementsByTagName("form")[0];
const mensajeErrorLoginClassic = document.getElementById("mensajeErrorLoginClassic");
const passwordInput = document.getElementById("password");

//Elementos que cambian en el formulario dependiendo de si se logea o se registra
const labelUsername = document.getElementById("labelUsername");
const inputUsername = document.getElementById("username");
const labelPasswordConfirm = document.getElementById("labelPasswordConfirm");
const passwordInput2 = document.getElementById("passwordConfirm");


//Hace que al clickear fuera del dialog se cierre
dialogRegistro.addEventListener('click', (evento) => {
  const rect = dialogRegistro.getBoundingClientRect();
    
  if (
    evento.clientX < rect.left ||
    evento.clientX > rect.right ||
    evento.clientY < rect.top ||
    evento.clientY > rect.bottom
  ) {
    dialogRegistro.close();
  }
});

//Cambia el dialog de registro a inicio de sesion y viceversa
cambioDialogRegistro.addEventListener("click", () => {
    if(cambioDialogRegistro.innerText === "No tengo cuenta"){
        mostrarDialogoRegistro("registro")
    }
    else{
        mostrarDialogoRegistro("inicio")
    }
})

//Comprueba que las contraseñas del formulario coincidan
formularioLogin.addEventListener("submit", (evento) => {
    if ((passwordInput.value !== passwordInput2.value) && inputLoginPeticion.value === "sign up") {
        evento.preventDefault();

        mensajeErrorLoginClassic.style.display = "block";        
        passwordInput2.focus();               
    }
    else{
        mensajeErrorLoginClassic.style.display = "none";        
    }
})

function mostrarDialogoRegistro(modo){
    dialogRegistro.showModal();    
    dialogRegistro.getElementsByTagName("div")[0].style.display = "grid";
    formularioLogin.style.display = "none";
    
    if (modo === "inicio") {
        dialogRegistro.getElementsByTagName("h2")[0].innerText = "Iniciar Sesión";            
        cambioDialogRegistro.innerText = "No tengo cuenta";
        inputLoginPeticion.value = "login";

        //Se ocultan los campos de correo y confirmar contraseña y no se piden
        passwordInput2.style.display = "none";
        passwordInput2.removeAttribute("required");

        inputUsername.style.display = "none";
        inputUsername.removeAttribute("required");

        labelUsername.style.display = "none";
        labelPasswordConfirm.style.display = "none";
    }    
    else{
        dialogRegistro.getElementsByTagName("h2")[0].innerText = "Crear Cuenta";           
        cambioDialogRegistro.innerText = "Ya tengo cuenta";          
        inputLoginPeticion.value = "sign up";    
        
        //Se muestran los campos de correo y confirmar contraseña y no se piden como requisito
        passwordInput2.style.display = "block";
        passwordInput2.setAttribute("required", "");

        inputUsername.style.display = "block";
        inputUsername.setAttribute("required", "");

        labelUsername.style.display = "block";
        labelPasswordConfirm.style.display = "block";
    }
}

function cerrarDialogoRegistro() {
    dialogRegistro.close();
}

function mostrarFormularioRegistro(){
    dialogRegistro.getElementsByTagName("div")[0].style.display = "none";
    dialogRegistro.getElementsByTagName("form")[0].style.display = "grid";
}

