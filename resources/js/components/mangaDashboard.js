import Chart from 'chart.js/auto';

export function mangaDashboard(config = {}) {
    return {
        // ----- STATE -----
        typeLabels:   config.typeLabels   || [],
        typeData:     config.typeData     || [],
        statusLabels: config.statusLabels || [],
        statusData:   config.statusData   || [],

        charts: {},

        // ----- LIFECYCLE -----
        init() {
            this.$nextTick(() => {
                this.renderTypeChart();
                this.renderStatusChart();
            });

            window.addEventListener('resize', () => this.handleResize());
        },

        destroy() {
            Object.values(this.charts).forEach(c => c?.destroy());
        },

        handleResize() {
            Object.values(this.charts).forEach(c => c?.resize());
        },

        // ----- COMMON OPTIONS -----
        doughnutOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#9ca3af',
                            padding: 14,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 11 },
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0a0a0f',
                        titleColor: '#fff',
                        bodyColor: '#d1d5db',
                        borderColor: '#374151',
                        borderWidth: 1,
                        padding: 10,
                    }
                }
            };
        },

        // ----- CHARTS -----
        renderTypeChart() {
            const ctx = document.getElementById('mangaByTypeChart');
            if (!ctx || !this.typeLabels.length) return;

            this.charts.type = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: this.typeLabels,
                    datasets: [{
                        data: this.typeData,
                        backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899'],
                        borderWidth: 2,
                        borderColor: '#111827',
                    }]
                },
                options: this.doughnutOptions(),
            });
        },

        renderStatusChart() {
            const ctx = document.getElementById('mangaByStatusChart');
            if (!ctx || !this.statusLabels.length) return;

            this.charts.status = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: this.statusLabels,
                    datasets: [{
                        data: this.statusData,
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderWidth: 2,
                        borderColor: '#111827',
                    }]
                },
                options: this.doughnutOptions(),
            });
        },
    };
}