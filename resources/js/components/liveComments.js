export function liveComments() {
    return {
        interval: null,

        init() {
            this.fetch();
            this.interval = setInterval(() => this.fetch(), 5000);
        },

        async fetch() {
            try {
                const res = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await res.json();

                document.getElementById('comments-wrapper').innerHTML = data.html;

            } catch (e) {
                console.error('Live update failed');
            }
        },

        async deleteComment(id, type) {
            if (!confirm('Delete this comment?')) return;

            const url = type === 'anime'
                ? `/admin/comments/anime/${id}`
                : `/admin/comments/manga/${id}`;

            try {
                await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                this.fetch();

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: 'Deleted successfully', type: 'success'}
                }));

            } catch {
                alert('Delete failed');
            }
        }
    };
}