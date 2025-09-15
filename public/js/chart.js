// Instancie les graphiques si les canvas sont présents dans le DOM
document.addEventListener("DOMContentLoaded", () => {
    const chartCovoiturages = document.getElementById("chartCovoiturages");
    const chartCredits = document.getElementById("chartCredits");

    if (chartCovoiturages) {
        new Chart(chartCovoiturages, {
            type: "bar",
            data: {
                labels: ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"],
                datasets: [
                    {
                        label: "Covoiturages",
                        data: [3, 5, 7, 6, 4, 2, 1],
                        backgroundColor: "rgba(75, 192, 192, 0.6)",
                        borderRadius: 6,
                    },
                ],
            },
        });
    }
    if (chartCredits) {
        new Chart(chartCredits, {
            type: "line",
            data: {
                labels: ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"],
                datasets: [
                    {
                        label: "Crédits / jour",
                        data: [40, 55, 60, 38, 80, 20, 15],
                        borderColor: "rgba(255, 99, 132, 0.8)",
                        fill: false,
                        tension: 0.3,
                    },
                ],
            },
        });
    }
});
