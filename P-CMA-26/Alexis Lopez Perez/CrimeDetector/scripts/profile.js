const formularioCambiarPassword = document.getElementById(
    "formularioCambiarPassword",
);
const passwordInput = document.getElementById("nuevoPassword");
const passwordInput2 = document.getElementById("nuevoPassword2");
const mensajeErrorPassword = document.getElementById("mensajeErrorPassword");

// Validación de coincidencia de contraseñas
formularioCambiarPassword.addEventListener("submit", (evento) => {
    if (passwordInput.value !== passwordInput2.value) {
        evento.preventDefault();

        mensajeErrorPassword.style.display = "block";
        passwordInput2.style.borderColor = "#dc2626";
        passwordInput2.focus();
    } else {
        mensajeErrorPassword.style.display = "none";
        passwordInput2.style.borderColor = "";
    }
});

// LÓGICA DE VISTA PREVIA Y DROPZONE PARA FOTO DE PERFIL
const dropzone = document.getElementById("dropzoneFoto");
const inputFoto = document.getElementById("inputFotoPerfil");
const dropzoneContent = document.getElementById("dropzoneContent");
const previewContainer = document.getElementById("previewContainer");
const imgPreview = document.getElementById("imgPreview");
const fileName = document.getElementById("fileName");

// Abrir el selector de archivos al hacer clic en el contenedor
dropzone.addEventListener("click", () => inputFoto.click());

// Mostrar la vista previa cuando el usuario selecciona una imagen
inputFoto.addEventListener("change", (e) => {
    mostrarVistaPrevia(e.target.files[0]);
});

// Soporte para Arrastrar y Soltar (Drag & Drop)
["dragenter", "dragover"].forEach((eventName) => {
    dropzone.addEventListener(
        eventName,
        (e) => {
            e.preventDefault();
            dropzone.classList.add("dragover");
        },
        false,
    );
});

["dragleave", "drop"].forEach((eventName) => {
    dropzone.addEventListener(
        eventName,
        (e) => {
            e.preventDefault();
            dropzone.classList.remove("dragover");
        },
        false,
    );
});

dropzone.addEventListener("drop", (e) => {
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        inputFoto.files = files; // Asigna el archivo al input
        mostrarVistaPrevia(files[0]);
    }
});

function mostrarVistaPrevia(file) {
    if (file && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imgPreview.src = e.target.result;
            fileName.textContent = file.name;
            dropzoneContent.style.display = "none";
            previewContainer.style.display = "flex";
        };
        reader.readAsDataURL(file);
    }
}
