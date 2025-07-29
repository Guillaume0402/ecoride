document.addEventListener('DOMContentLoaded', () => {
    console.log("JS Alert chargé ✅"); // Debug
    const alerts = document.querySelectorAll('.auto-dismiss');
    console.log(`Alertes détectées : ${alerts.length}`);

    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = 0;
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });
});
