import Chart from 'chart.js/auto';

export const dashboard = (chartData) => ({

    charts: {},

    init() {
        this.$nextTick(() => {
            this.initUserGrowthChart();
            this.initAnimeTypeChart();
            this.initAnimeStatusChart();
        });
    },

    /* =========================
       COMMON OPTIONS
    ========================= */
    baseOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#9ca3af',
                        padding: 12,
                        usePointStyle: true
                    }
                }
            }
        };
    },

    destroyChart(name) {
        if (this.charts[name]) {
            this.charts[name].destroy();
        }
    },

    safeData(labels, data) {
        if (!labels || !data || labels.length !== data.length) {
            return { labels: [], data: [] };
        }
        return { labels, data };
    },

    /* =========================
       USER GROWTH (LINE)
    ========================= */
    initUserGrowthChart() {
        const ctx = document.getElementById('userGrowthChart');

        const safe = this.safeData(
            chartData.userGrowthLabels,
            chartData.userGrowthData
        );

        if (!ctx || safe.labels.length === 0) return;

        this.destroyChart('growth');

        this.charts.growth = new Chart(ctx, {
            type: 'line',
            data: {
                labels: safe.labels,
                datasets: [{
                    label: 'New Users',
                    data: safe.data,
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124,58,237,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#7c3aed',
                    pointRadius: 4,
                }]
            },
            options: {
                ...this.baseOptions(),
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ticks: { color: '#9ca3af' },
                        grid: { color: 'rgba(75,85,99,0.2)' }
                    },
                    y: {
                        ticks: { color: '#9ca3af' },
                        grid: { color: 'rgba(75,85,99,0.2)' },
                        beginAtZero: true
                    }
                }
            }
        });
    },

    /* =========================
       ANIME TYPE
    ========================= */
    initAnimeTypeChart() {
        const ctx = document.getElementById('animeByTypeChart');

        const safe = this.safeData(
            chartData.typeLabels,
            chartData.typeData
        );

        if (!ctx || safe.labels.length === 0) return;

        this.destroyChart('type');

        this.charts.type = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: safe.labels,
                datasets: [{
                    data: safe.data,
                    backgroundColor: [
                        '#7c3aed', '#ec4899', '#f59e0b',
                        '#10b981', '#3b82f6', '#ef4444', '#14b8a6'
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                ...this.baseOptions(),
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#9ca3af',
                            padding: 16,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    },

    /* =========================
       ANIME STATUS
    ========================= */
    initAnimeStatusChart() {
        const ctx = document.getElementById('animeByStatusChart');

        const safe = this.safeData(
            chartData.statusLabels,
            chartData.statusData
        );

        if (!ctx || safe.labels.length === 0) return;

        this.destroyChart('status');

        this.charts.status = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: safe.labels,
                datasets: [{
                    data: safe.data,
                    backgroundColor: [
                        '#10b981', '#f59e0b',
                        '#3b82f6', '#ef4444', '#8b5cf6'
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                ...this.baseOptions(),
                cutout: '70%'
            }
        });
    }
});