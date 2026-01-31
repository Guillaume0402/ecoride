/*
Module: Charts (Dashboard)
Rôle: Instancier les graphiques Chart.js si les canvas cibles sont présents.
Utilisation: Fournir labels/values via data-attributes ou valeurs par défaut.
*/
document.addEventListener("DOMContentLoaded", () => {
    if (typeof Chart === "undefined") return;

    function safeJsonParse(str, fallback) {
        try {
            return JSON.parse(str);
        } catch {
            return fallback;
        }
    }

    function getChartTheme() {
        const isDark = document.documentElement.classList.contains("theme-alt");
        return {
            text: isDark ? "#ffffff" : "#111111",
            grid: isDark ? "rgba(255,255,255,.15)" : "rgba(0,0,0,.12)",
            tooltipBg: isDark ? "rgba(0,0,0,.85)" : "rgba(255,255,255,.95)",
        };
    }

    function makeOptions() {
        const t = getChartTheme();
        return {
            responsive: true,
            maintainAspectRatio: true, // IMPORTANT: stop le “grandissement” infini
            plugins: {
                legend: { labels: { color: t.text } },
                tooltip: {
                    backgroundColor: t.tooltipBg,
                    titleColor: t.text,
                    bodyColor: t.text,
                },
            },
            scales: {
                x: { ticks: { color: t.text }, grid: { color: t.grid } },
                y: { ticks: { color: t.text }, grid: { color: t.grid } },
            },
        };
    }

    function applyThemeToChart(chart) {
        const t = getChartTheme();

        chart.options.plugins.legend.labels.color = t.text;
        chart.options.plugins.tooltip.backgroundColor = t.tooltipBg;
        chart.options.plugins.tooltip.titleColor = t.text;
        chart.options.plugins.tooltip.bodyColor = t.text;

        chart.options.scales.x.ticks.color = t.text;
        chart.options.scales.y.ticks.color = t.text;
        chart.options.scales.x.grid.color = t.grid;
        chart.options.scales.y.grid.color = t.grid;

        chart.update("none"); // sans animation = plus stable
    }

    const chartCovoituragesEl = document.getElementById("chartCovoiturages");
    const chartCreditsEl = document.getElementById("chartCredits");

    let chartCovoituragesInstance = null;
    let chartCreditsInstance = null;

    if (chartCovoituragesEl) {
        const defaultLabels = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
        const defaultValues = [3, 5, 7, 6, 4, 2, 1];

        const labels1 = chartCovoituragesEl.dataset.labels
            ? safeJsonParse(chartCovoituragesEl.dataset.labels, defaultLabels)
            : defaultLabels;

        const values1 = chartCovoituragesEl.dataset.values
            ? safeJsonParse(chartCovoituragesEl.dataset.values, defaultValues)
            : defaultValues;

        // Sécurité si ré-init : détruit l’ancien chart attaché à ce canvas
        Chart.getChart(chartCovoituragesEl)?.destroy();

        chartCovoituragesInstance = new Chart(chartCovoituragesEl, {
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
            options: makeOptions(),
        });
    }

    if (chartCreditsEl) {
        const defaultLabels = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
        const defaultValues = [40, 55, 60, 38, 80, 20, 15];

        const labels2 = chartCreditsEl.dataset.labels
            ? safeJsonParse(chartCreditsEl.dataset.labels, defaultLabels)
            : defaultLabels;

        const values2 = chartCreditsEl.dataset.values
            ? safeJsonParse(chartCreditsEl.dataset.values, defaultValues)
            : defaultValues;

        Chart.getChart(chartCreditsEl)?.destroy();

        chartCreditsInstance = new Chart(chartCreditsEl, {
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
            options: makeOptions(),
        });
    }

    // Expose une fonction à appeler depuis ton toggle
    window.updateChartsTheme = () => {
        if (chartCovoituragesInstance)
            applyThemeToChart(chartCovoituragesInstance);
        if (chartCreditsInstance) applyThemeToChart(chartCreditsInstance);
    };

    // Applique le bon thème au chargement
    window.updateChartsTheme();
});
