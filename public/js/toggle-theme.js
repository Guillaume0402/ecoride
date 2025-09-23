/*
Module: Toggle Thème
Rôle: Basculer un thème alternatif via une classe sur <body> et stocker la préférence.
Prérequis: Bouton #themeToggleBtn, Bootstrap Icons si utilisé pour l’icône.
Utilisation: Inclure, puis cliquer sur #themeToggleBtn pour changer de thème.
*/
const themeToggleBtn = document.getElementById("themeToggleBtn");
if (themeToggleBtn) {
    // Initialisation à partir du stockage local, ou état déjà posé par le layout
    const stored = localStorage.getItem("theme");
    const isAlt =
        document.body.classList.contains("theme-alt") || stored === "alt";
    if (isAlt) {
        document.body.classList.add("theme-alt");
        themeToggleBtn.innerHTML = '<i class="bi bi-brightness-high"></i>';
    } else {
        document.body.classList.remove("theme-alt");
        themeToggleBtn.innerHTML = '<i class="bi bi-moon-stars"></i>';
    }

    // Au clic: bascule le thème et met à jour le label + stockage
    themeToggleBtn.addEventListener("click", () => {
        document.body.classList.toggle("theme-alt");
        const isAlt = document.body.classList.contains("theme-alt");
        localStorage.setItem("theme", isAlt ? "alt" : "default");
        themeToggleBtn.innerHTML = isAlt
            ? '<i class="bi bi-brightness-high"></i>'
            : '<i class="bi bi-moon-stars"></i>';
    });
}
