/*
Module: Toggle Thème
Rôle: Basculer un thème alternatif via une classe sur <body> et stocker la préférence.
Prérequis: Bouton #themeToggleBtn, Bootstrap Icons si utilisé pour l’icône.
Utilisation: Inclure, puis cliquer sur #themeToggleBtn pour changer de thème.
*/
const themeToggleBtn = document.getElementById("themeToggleBtn");
if (themeToggleBtn) {
    // Initialisation: prendre en compte <html> ou <body> (layout applique tôt sur les deux)
    const stored = localStorage.getItem("theme");
    const hasAlt =
        document.documentElement.classList.contains("theme-alt") ||
        document.body.classList.contains("theme-alt") ||
        stored === "alt";

    document.documentElement.classList.toggle("theme-alt", hasAlt);
    document.body.classList.toggle("theme-alt", hasAlt);
    themeToggleBtn.innerHTML = hasAlt
        ? '<i class="bi bi-brightness-high"></i>'
        : '<i class="bi bi-moon-stars"></i>';

    // Au clic: bascule et synchronise html/body + stockage
    themeToggleBtn.addEventListener("click", () => {
        const isAltNow = !document.documentElement.classList.contains(
            "theme-alt"
        );
        document.documentElement.classList.toggle("theme-alt", isAltNow);
        document.body.classList.toggle("theme-alt", isAltNow);
        localStorage.setItem("theme", isAltNow ? "alt" : "default");
        themeToggleBtn.innerHTML = isAltNow
            ? '<i class="bi bi-brightness-high"></i>'
            : '<i class="bi bi-moon-stars"></i>';
    });
}
