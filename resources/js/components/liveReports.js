export function liveReports() {
    return {
        loading: false,
        search: '',
        status: '',
        interval: null,

        init() {
            const params = new URLSearchParams(window.location.search);

            this.search = params.get('search') || '';
            this.status = params.get('status') || '';

            this.fetch(false);

            this.interval = setInterval(() => {
                this.fetch(false);
            }, 7000);

            window.addEventListener('popstate', () => {
                this.fetch(false);
            });
        },

        buildUrl() {
            const url = new URL(window.location.href);

            url.searchParams.delete('page');

            if (this.search.trim()) {
                url.searchParams.set('search', this.search.trim());
            } else {
                url.searchParams.delete('search');
            }

            if (this.status) {
                url.searchParams.set('status', this.status);
            } else {
                url.searchParams.delete('status');
            }

            return url.toString();
        },

        async fetch(pushState = true, url = null) {
            this.loading = true;

            try {
                const targetUrl = url || this.buildUrl();

                const response = await fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to load reports.');
                }

                document.getElementById('reports-container').innerHTML = data.html;

                if (pushState && data.url) {
                    window.history.pushState({}, '', data.url);
                }

            } catch (error) {
                this.toast(error.message || 'Failed to load reports.', 'error');
            }

            this.loading = false;
        },

        async updateStatus(id, status) {
            try {
                const response = await fetch(`/admin/reports/${id}`, {
                    method: 'PUT',
                    headers: this.headers(),
                    body: JSON.stringify({ status }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to update report.');
                }

                this.toast(data.message || 'Report updated.', 'success');
                await this.fetch(false);

            } catch (error) {
                this.toast(error.message || 'Failed to update report.', 'error');
            }
        },

        async bulkResolve() {
            const ids = Array.from(document.querySelectorAll('[data-report-id][data-status="pending"]'))
                .map((el) => el.dataset.reportId);

            if (!ids.length) {
                this.toast('No pending reports visible.', 'error');
                return;
            }

            if (!confirm('Mark all visible pending reports as resolved?')) return;

            try {
                const response = await fetch('/admin/reports/bulk-resolve', {
                    method: 'POST',
                    headers: this.headers(),
                    body: JSON.stringify({ ids }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Bulk resolve failed.');
                }

                this.toast(data.message || 'Reports resolved.', 'success');
                await this.fetch(false);

            } catch (error) {
                this.toast(error.message || 'Bulk resolve failed.', 'error');
            }
        },

        handlePagination(event) {
            const link = event.target.closest('a');

            if (!link) return;

            event.preventDefault();

            this.fetch(true, link.href);
        },

        headers() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            };
        },

        toast(message, type = 'success') {
            window.dispatchEvent(
                new CustomEvent('toast', {
                    detail: { message, type },
                })
            );
        },
    };
}