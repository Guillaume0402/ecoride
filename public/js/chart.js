// Instancie les graphiques si les canvas sont présents dans le DOM
document.addEventListener("DOMContentLoaded", () => {
    const chartCovoiturages = document.getElementById("chartCovoiturages");
    const chartCredits = document.getElementById("chartCredits");

    if (chartCovoiturages) {
        const labels1 = chartCovoiturages.dataset.labels
            ? JSON.parse(chartCovoiturages.dataset.labels)
            : ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
        const values1 = chartCovoiturages.dataset.values
            ? JSON.parse(chartCovoiturages.dataset.values)
            : [3, 5, 7, 6, 4, 2, 1];
        new Chart(chartCovoiturages, {
            type: "bar",
            data: {
                labels: labels1,
                datasets: [
                    {
                        label: "Covoiturages",
                        data: values1,
                        backgroundColor: "rgba(75, 192, 192, 0.6)",
                        borderRadius: 6,
                    },
                ],
            },
        });
    }
    if (chartCredits) {
        const labels2 = chartCredits.dataset.labels
            ? JSON.parse(chartCredits.dataset.labels)
            : ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
        const values2 = chartCredits.dataset.values
            ? JSON.parse(chartCredits.dataset.values)
            : [40, 55, 60, 38, 80, 20, 15];
        new Chart(chartCredits, {
            type: "line",
            data: {
                labels: labels2,
                datasets: [
                    {
                        label: "Crédits / jour",
                        data: values2,
                        borderColor: "rgba(255, 99, 132, 0.8)",
                        fill: false,
                        tension: 0.3,
                    },
                ],
            },
        });
    }
});
