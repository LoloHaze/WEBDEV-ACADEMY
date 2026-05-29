const frases = [
    "¿Listo para seguir aprendiendo hoy? 🚀",
    "¿Qué quieres aprender hoy? 📚",
    "Un nuevo día, un nuevo reto 💪",
    "Cada lección te hace más fuerte 🌟",
    "Hoy es un buen día para mejorar 😎",
    "Tu progreso empieza con un pequeño paso 🎯",
    "Nunca dejes de aprender 💡",
    "Vamos a por otro logro 🏆",
    "El conocimiento abre puertas 🔥",
    "Sigue avanzando, lo estás haciendo genial 🚀"
];

let indice = localStorage.getItem("indiceFrase");

if (indice === null) {
    indice = 0;
} else {
    indice = parseInt(indice);
}

document.getElementById("frase-bienvenida").textContent = frases[indice];

indice++;

if (indice >= frases.length) {
    indice = 0;
}

localStorage.setItem("indiceFrase", indice);