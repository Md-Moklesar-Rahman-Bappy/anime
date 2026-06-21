export function genresManager() {
    return {
        search: '',
        name: '',
        loading: false,
        saving: false,

        init() {
            const params = new URLSearchParams(window.location.search);
            this.search = params.get('search') || '';
        },

        async load(url = null) {
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
                    throw new Error(data.message || 'Failed to load genres.');
                }

                document.getElementById('genres-list').innerHTML = data.html;

                if (data.url) {
                    window.history.pushState({}, '', data.url);
                }

            } catch (error) {
                this.toast(error.message || 'Failed to load genres.', 'error');
            }

            this.loading = false;
        },

        buildUrl() {
            const url = new URL(window.location.href);

            url.searchParams.delete('page');

            if (this.search.trim()) {
                url.searchParams.set('search', this.search.trim());
            } else {
                url.searchParams.delete('search');
            }

            return url.toString();
        },

        async addGenre() {
            const value = this.name.trim();

            if (!value) {
                this.toast('Genre name is required.', 'error');
                return;
            }

            this.saving = true;

            try {
                const response = await fetch(this.routeStore(), {
                    method: 'POST',
                    headers: this.headers(),
                    body: JSON.stringify({
                        name: value,
                    }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to add genre.');
                }

                this.name = '';
                this.toast(data.message || 'Genre added.', 'success');
                await this.load();

            } catch (error) {
                this.toast(error.message || 'Failed to add genre.', 'error');
            }

            this.saving = false;
        },

        async updateGenre(id, value) {
            const name = value.trim();

            if (!name) {
                this.toast('Genre name is required.', 'error');
                return;
            }

            try {
                const response = await fetch(`/admin/genres/${id}`, {
                    method: 'PUT',
                    headers: this.headers(),
                    body: JSON.stringify({
                        name,
                    }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to update genre.');
                }

                this.toast(data.message || 'Genre updated.', 'success');
                await this.load();

            } catch (error) {
                this.toast(error.message || 'Failed to update genre.', 'error');
            }
        },

        async deleteGenre(id) {
            if (!confirm('Delete this genre?')) return;

            try {
                const response = await fetch(`/admin/genres/${id}`, {
                    method: 'DELETE',
                    headers: this.headers(),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to delete genre.');
                }

                this.toast(data.message || 'Genre deleted.', 'success');
                await this.load();

            } catch (error) {
                this.toast(error.message || 'Failed to delete genre.', 'error');
            }
        },

        async importGenres() {
            if (!confirm('Import all genres from MyAnimeList?')) return;

            try {
                const response = await fetch(this.routeImport(), {
                    method: 'POST',
                    headers: this.headers(),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to import genres.');
                }

                this.toast(data.message || 'Genres imported.', 'success');
                await this.load();

            } catch (error) {
                this.toast(error.message || 'Failed to import genres.', 'error');
            }
        },

        handlePagination(event) {
            const link = event.target.closest('a');

            if (!link) return;

            event.preventDefault();

            this.load(link.href);
        },

        headers() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            };
        },

        routeStore() {
            return '/admin/genres';
        },

        routeImport() {
            return '/admin/genres/import-from-mal';
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