document.addEventListener("DOMContentLoaded", () => {
    console.log("JS cargado");

    // ============================
    // FORM CURSOS / LECCIONES
    // ============================

    const formCurso = document.getElementById("formCurso");

    if (formCurso) {

        const titulo = document.getElementById("titulo");
        const descripcion = document.getElementById("descripcion");
        const precio = document.getElementById("precio");
        const imagen = document.getElementById("imagen");
        const video = document.getElementById("video_url");

        const errorTitulo = document.getElementById("errorTitulo");
        const errorDescripcion = document.getElementById("errorDescripcion");
        const errorPrecio = document.getElementById("errorPrecio");
        const errorImagen = document.getElementById("errorImagen");
        const errorVideo = document.getElementById("errorVideo");

        function validarTitulo() {
            if (!titulo) return true;
            const v = titulo.value.trim();
            if (v.length < 5) {
                errorTitulo.textContent = "Mínimo 5 caracteres";
                titulo.classList.add("input-error");
                return false;
            }
            errorTitulo.textContent = "";
            titulo.classList.remove("input-error");
            return true;
        }

        function validarDescripcion() {
            if (!descripcion) return true;
            const v = descripcion.value.trim();
            if (v !== "" && v.length < 10) {
                errorDescripcion.textContent = "Mínimo 10 caracteres";
                descripcion.classList.add("input-error");
                return false;
            }
            errorDescripcion.textContent = "";
            descripcion.classList.remove("input-error");
            return true;
        }

        function validarPrecio() {
            if (!precio) return true;
            const v = precio.value.trim();
            if (v === "" || isNaN(v) || Number(v) < 0) {
                errorPrecio.textContent = "Precio inválido";
                precio.classList.add("input-error");
                return false;
            }
            errorPrecio.textContent = "";
            precio.classList.remove("input-error");
            return true;
        }

        function validarImagen() {
            if (!imagen) return true;

            if (imagen.files.length === 0) return true;

            const file = imagen.files[0];
            const tipos = ["image/jpeg", "image/png", "image/webp"];

            if (!tipos.includes(file.type)) {
                errorImagen.textContent = "Formato no permitido";
                imagen.classList.add("input-error");
                return false;
            }

            if (file.size > 3 * 1024 * 1024) {
                errorImagen.textContent = "Máx 3MB";
                imagen.classList.add("input-error");
                return false;
            }

            errorImagen.textContent = "";
            imagen.classList.remove("input-error");
            return true;
        }

        function validarVideo() {
            if (!video) return true;
            const v = video.value.trim();
            if (v.length < 5 || !v.includes("http")) {
                errorVideo.textContent = "URL inválida";
                video.classList.add("input-error");
                return false;
            }
            errorVideo.textContent = "";
            video.classList.remove("input-error");
            return true;
        }

        // EVENTOS
        if (titulo) titulo.addEventListener("blur", validarTitulo);
        if (descripcion) descripcion.addEventListener("blur", validarDescripcion);
        if (precio) precio.addEventListener("blur", validarPrecio);
        if (imagen) imagen.addEventListener("change", validarImagen);
        if (video) video.addEventListener("blur", validarVideo);

        formCurso.addEventListener("submit", (e) => {
            const ok =
                validarTitulo() &&
                validarDescripcion() &&
                validarPrecio() &&
                validarImagen() &&
                validarVideo();

            if (!ok) e.preventDefault();
        });
    }

    // ============================
    // FORM USUARIO
    // ============================

    const formUsuario = document.getElementById("formUsuario");

    if (formUsuario) {

        const nombre = document.getElementById("nombre");
        const email = document.getElementById("email");

        const errorNombre = document.getElementById("errorNombre");
        const errorEmail = document.getElementById("errorEmail");

        function validarNombre() {
            const v = nombre.value.trim();
            if (v.length < 3) {
                errorNombre.textContent = "Mínimo 3 caracteres";
                nombre.classList.add("input-error");
                return false;
            }
            errorNombre.textContent = "";
            nombre.classList.remove("input-error");
            return true;
        }

        function validarEmail() {
            const v = email.value.trim();
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regex.test(v)) {
                errorEmail.textContent = "Email inválido";
                email.classList.add("input-error");
                return false;
            }
            errorEmail.textContent = "";
            email.classList.remove("input-error");
            return true;
        }

        nombre.addEventListener("blur", validarNombre);
        email.addEventListener("blur", validarEmail);

        formUsuario.addEventListener("submit", (e) => {
            const ok = validarNombre() && validarEmail();
            if (!ok) e.preventDefault();
        });
    }

    // ============================
    // FORM VALORACION
    // ============================

    const formValoracion = document.getElementById("formValoracion");

    if (formValoracion) {

        const comentario = document.getElementById("comentario");
        const grupoEstrellas = document.getElementById("grupoEstrellas");

        const errorPuntuacion = document.getElementById("errorPuntuacion");
        const errorComentario = document.getElementById("errorComentario");

        function validarComentario() {
            const v = comentario.value.trim();
            if (v === "") {
                errorComentario.textContent = "Escribe un comentario";
                comentario.classList.add("input-error");
                return false;
            }
            errorComentario.textContent = "";
            comentario.classList.remove("input-error");
            return true;
        }

        function validarPuntuacion() {
            const puntuacion = document.querySelector('input[name="puntuacion"]:checked');

            if (!puntuacion) {
                errorPuntuacion.textContent = "Selecciona una puntuación";
                grupoEstrellas.classList.add("stars-error");
                return false;
            }

            errorPuntuacion.textContent = "";
            grupoEstrellas.classList.remove("stars-error");
            return true;
        }

        comentario.addEventListener("blur", validarComentario);

        document.querySelectorAll('input[name="puntuacion"]').forEach(radio => {
            radio.addEventListener("change", validarPuntuacion);
        });

        formValoracion.addEventListener("submit", (e) => {
            const ok = validarComentario() && validarPuntuacion();
            if (!ok) e.preventDefault();
        });
    }

});
// ============================
// FORM LOGIN
// ============================

const formLogin = document.getElementById("formLogin");

if (formLogin) {

    const email = document.getElementById("emailLogin");
    const password = document.getElementById("passwordLogin");

    const errorEmail = document.getElementById("errorEmailLogin");
    const errorPassword = document.getElementById("errorPasswordLogin");

    function validarEmailLogin() {
        const v = email.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!regex.test(v)) {
            errorEmail.textContent = "Email inválido";
            email.classList.add("input-error");
            return false;
        }

        errorEmail.textContent = "";
        email.classList.remove("input-error");
        return true;
    }

    function validarPasswordLogin() {
        const v = password.value.trim();

        if (v.length < 6) {
            errorPassword.textContent = "Mínimo 6 caracteres";
            password.classList.add("input-error");
            return false;
        }

        errorPassword.textContent = "";
        password.classList.remove("input-error");
        return true;
    }

    // BLUR
    email.addEventListener("blur", validarEmailLogin);
    password.addEventListener("blur", validarPasswordLogin);

    // SUBMIT
    formLogin.addEventListener("submit", (e) => {

        const ok =
            validarEmailLogin() &&
            validarPasswordLogin();

        if (!ok) e.preventDefault();

    });

}

// ============================
// FORM REGISTRO
// ============================

const formRegistro = document.getElementById("formRegistro");

if (formRegistro) {

    const nombre = document.getElementById("nombreRegistro");
    const email = document.getElementById("emailRegistro");
    const password = document.getElementById("passwordRegistro");

    const errorNombre = document.getElementById("errorNombreRegistro");
    const errorEmail = document.getElementById("errorEmailRegistro");
    const errorPassword = document.getElementById("errorPasswordRegistro");

    function validarNombreRegistro() {
        const v = nombre.value.trim();

        if (v.length < 3) {
            errorNombre.textContent = "Mínimo 3 caracteres";
            nombre.classList.add("input-error");
            return false;
        }

        errorNombre.textContent = "";
        nombre.classList.remove("input-error");
        return true;
    }

    function validarEmailRegistro() {
        const v = email.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!regex.test(v)) {
            errorEmail.textContent = "Email inválido";
            email.classList.add("input-error");
            return false;
        }

        errorEmail.textContent = "";
        email.classList.remove("input-error");
        return true;
    }

    function validarPasswordRegistro() {
        const v = password.value.trim();

        if (v.length < 6) {
            errorPassword.textContent = "Mínimo 6 caracteres";
            password.classList.add("input-error");
            return false;
        }

        errorPassword.textContent = "";
        password.classList.remove("input-error");
        return true;
    }

    // BLUR
    nombre.addEventListener("blur", validarNombreRegistro);
    email.addEventListener("blur", validarEmailRegistro);
    password.addEventListener("blur", validarPasswordRegistro);

    // SUBMIT
    formRegistro.addEventListener("submit", (e) => {

        const ok =
            validarNombreRegistro() &&
            validarEmailRegistro() &&
            validarPasswordRegistro();

        if (!ok) e.preventDefault();

    });
}