import Chart from 'chart.js/auto';

export const dashboard = (chartData) => ({
    init() {
        this.$nextTick(() => {
            this.initUserGrowthChart();
            this.initAnimeTypeChart();
            this.initAnimeStatusChart();
        });
    },
    initUserGrowthChart() {
        const ctx = document.getElementById('userGrowthChart');
        if (!ctx || !chartData.userGrowthLabels?.length) return;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.userGrowthLabels,
                datasets: [{
                    label: 'New Users',
                    data: chartData.userGrowthData,
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#7c3aed',
                    pointBorderColor: '#1f2937',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ticks: { color: '#9ca3af', maxTicksLimit: 8 },
                        grid: { color: 'rgba(75, 85, 99, 0.2)' }
                    },
                    y: {
                        ticks: { color: '#9ca3af' },
                        grid: { color: 'rgba(75, 85, 99, 0.2)' },
                        beginAtZero: true
                    }
                }
            }
        });
    },
    initAnimeTypeChart() {
        const ctx = document.getElementById('animeByTypeChart');
        if (!ctx || !chartData.typeLabels?.length) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.typeLabels,
                datasets: [{
                    data: chartData.typeData,
                    backgroundColor: ['#7c3aed', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#14b8a6'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#9ca3af', padding: 16, usePointStyle: true, pointStyle: 'circle' }
                    }
                }
            }
        });
    },
    initAnimeStatusChart() {
        const ctx = document.getElementById('animeByStatusChart');
        if (!ctx || !chartData.statusLabels?.length) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.statusLabels,
                datasets: [{
                    data: chartData.statusData,
                    backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#9ca3af', padding: 16, usePointStyle: true, pointStyle: 'circle' }
                    }
                }
            }
        });
    }
});
