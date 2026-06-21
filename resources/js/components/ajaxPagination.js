export function ajaxPagination(config = {}) {
    return {
        target: config.target || 'ajax-content',
        url: config.url || window.location.href,
        loading: false,

        init() {
            this.bindPagination();

            window.addEventListener('popstate', () => {
                this.loadUrl(window.location.href, false);
            });
        },

        bindPagination() {
            const container = this.$el;

            container.addEventListener('click', (event) => {
                const button = event.target.closest('.paginate-link');

                if (!button) return;

                event.preventDefault();

                const page = button.dataset.page;

                if (!page) return;

                this.goToPage(page);
            });
        },

        goToPage(page) {
            const currentUrl = new URL(window.location.href);

            currentUrl.searchParams.set('page', page);

            this.loadUrl(currentUrl.toString(), true);
        },

        filter(event) {
            const form = event.target;
            const formData = new FormData(form);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value !== null && value !== '') {
                    params.set(key, value);
                }
            }

            params.delete('page');

            const baseUrl = form.action || this.url;
            const nextUrl = `${baseUrl}?${params.toString()}`;

            this.loadUrl(nextUrl, true);
        },

        async loadUrl(url, pushState = true) {
            this.loading = true;

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to load data.');
                }

                const target = document.getElementById(this.target);

                if (target && data.html) {
                    target.innerHTML = data.html;
                }

                if (pushState) {
                    window.history.pushState({}, '', data.url || url);
                }

            } catch (error) {
                console.error('[AJAX Pagination Error]', error);

                window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: {
                            message: 'Could not load page.',
                            type: 'error',
                        },
                    })
                );
            }

            this.loading = false;
        },
    };
}