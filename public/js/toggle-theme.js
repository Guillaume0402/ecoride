// Bascule un thème alternatif en ajoutant une classe sur <body>
// Utilise des icônes Bootstrap via innerHTML (sans emoji dans les commentaires)
const themeToggleBtn = document.getElementById("themeToggleBtn");
if (themeToggleBtn) {
    // Initialisation à partir du stockage local
    if (localStorage.getItem("theme") === "alt") {
        document.body.classList.add("theme-alt");
        themeToggleBtn.innerHTML = '<i class="bi bi-brightness-high"></i>';
    } else {
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
