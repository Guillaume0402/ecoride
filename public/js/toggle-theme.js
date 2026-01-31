const themeToggleBtn = document.getElementById("themeToggleBtn");
if (themeToggleBtn) {
    function setIcon(isAlt) {
        const icon = document.createElement("i");
        icon.className = isAlt ? "bi bi-brightness-high" : "bi bi-moon-stars";
        themeToggleBtn.replaceChildren(icon);
    }
    function applyTheme(isAlt) {
        document.documentElement.classList.toggle("theme-alt", isAlt);
        document.body.classList.toggle("theme-alt", isAlt);
        localStorage.setItem("theme", isAlt ? "alt" : "default");
        setIcon(isAlt);
        window.updateChartsTheme?.();
    }

    const stored = localStorage.getItem("theme");
    const isAltInitial =
        stored === "alt"
            ? true
            : stored === "default"
              ? false
              : document.documentElement.classList.contains("theme-alt") ||
                document.body.classList.contains("theme-alt");

    applyTheme(isAltInitial);

    themeToggleBtn.addEventListener("click", () => {
        const isAltNow =
            !document.documentElement.classList.contains("theme-alt");
        applyTheme(isAltNow);
    });
}
