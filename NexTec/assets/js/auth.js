function mostrarCampoEmpresa() {
    var selector = document.getElementById("selector_rol");
    var divEmpresa = document.getElementById("campo_empresa");
    var inputEmpresa = document.getElementById("input_empresa");

    if (selector && divEmpresa && inputEmpresa) {
        if (selector.value === "empresa") {
            divEmpresa.style.display = "block";
            inputEmpresa.required = true;
        } else {
            divEmpresa.style.display = "none";
            inputEmpresa.required = false;
            inputEmpresa.value = "";
        }
    }
}