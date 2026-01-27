/*
Module: Charts (Dashboard)
Rôle: Instancier les graphiques Chart.js si les canvas cibles sont présents.
Utilisation: Fournir labels/values via data-attributes ou valeurs par défaut.
*/
document.addEventListener("DOMContentLoaded", () => {
    // Si Chart.js n'est pas chargé sur la page, on ne fait rien
    if (typeof Chart === "undefined") return;

    function safeJsonParse(str, fallback) {
        try {
            return JSON.parse(str);
        } catch {
            return fallback;
        }
    }

    const chartCovoiturages = document.getElementById("chartCovoiturages");
    const chartCredits = document.getElementById("chartCredits");

    if (chartCovoiturages) {
        const defaultLabels = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
        const defaultValues = [3, 5, 7, 6, 4, 2, 1];

        const labels1 = chartCovoiturages.dataset.labels
            ? safeJsonParse(chartCovoiturages.dataset.labels, defaultLabels)
            : defaultLabels;

        const values1 = chartCovoiturages.dataset.values
            ? safeJsonParse(chartCovoiturages.dataset.values, defaultValues)
            : defaultValues;

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
        const defaultLabels = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
        const defaultValues = [40, 55, 60, 38, 80, 20, 15];

        const labels2 = chartCredits.dataset.labels
            ? safeJsonParse(chartCredits.dataset.labels, defaultLabels)
            : defaultLabels;

        const values2 = chartCredits.dataset.values
            ? safeJsonParse(chartCredits.dataset.values, defaultValues)
            : defaultValues;

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
