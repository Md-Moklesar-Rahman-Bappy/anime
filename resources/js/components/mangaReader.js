export function mangaReader(config = {}) {
    return {
        // ── CONFIG ──
        chapterId: config.chapterId,
        pages: config.pages || [],
        chapterUrl: config.chapterUrl || '',

        // ── STATE ──
        currentPage: config.startPage || 1,
        totalPages: (config.pages || []).length,
        mode: localStorage.getItem('reader_mode') || 'single',          // single | double | vertical
        fit: localStorage.getItem('reader_fit') || 'height',            // width | height | original
        direction: localStorage.getItem('reader_direction') || 'ltr',   // ltr | rtl
        settingsOpen: false,
        jumperOpen: false,
        jumpInput: 1,
        isFullscreen: false,
        saving: false,
        _saveTimer: null,

        // ── LIFECYCLE ──
        init() {
            if (this.currentPage < 1) this.currentPage = 1;
            if (this.currentPage > this.totalPages) this.currentPage = 1;
            this.jumpInput = this.currentPage;

            document.addEventListener('fullscreenchange', () => {
                this.isFullscreen = !!document.fullscreenElement;
            });

            // Vertical mode: scroll to saved page on load
            if (this.mode === 'vertical') {
                this.$nextTick(() => this.scrollToPage(this.currentPage));
            }
        },

        // ── NAVIGATION ──
        next() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.jumpInput = this.currentPage;
                this.afterNav();
            } else {
                window.bus?.emit('toast', { type: 'info', message: 'End of chapter — use Next Chapter button.' });
            }
        },

        prev() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.jumpInput = this.currentPage;
                this.afterNav();
            }
        },

        next2() {
            if (this.currentPage < this.totalPages - 1) {
                this.currentPage += 2;
            } else if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
            this.afterNav();
        },

        prev2() {
            if (this.currentPage > 2) {
                this.currentPage -= 2;
            } else {
                this.currentPage = 1;
            }
            this.afterNav();
        },

        jumpTo(page) {
            const n = parseInt(page, 10);
            if (isNaN(n) || n < 1 || n > this.totalPages) return;
            this.currentPage = n;

            if (this.mode === 'vertical') {
                this.scrollToPage(n);
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            this.afterNav();
        },

        goToChapter(chapter) {
            window.location.href = this.chapterUrl.replace('PLACEHOLDER', chapter);
        },

        afterNav() {
            this.debouncedSave();
            if (this.mode !== 'vertical') {
                window.scrollTo({ top: 0, behavior: 'instant' });
            }
        },

        // ── VERTICAL MODE ──
        scrollToPage(page) {
            const img = this.$refs.verticalContainer?.querySelector(`[data-page="${page}"]`);
            img?.scrollIntoView({ behavior: 'instant', block: 'start' });
        },

        onPageLoad(page) {
            // Update current page when user scrolls in vertical mode
            // (basic — could be enhanced with IntersectionObserver)
        },

        // ── SETTINGS ──
        setMode(m) {
            this.mode = m;
            localStorage.setItem('reader_mode', m);
            if (m === 'vertical') {
                this.$nextTick(() => this.scrollToPage(this.currentPage));
            } else {
                window.scrollTo({ top: 0 });
            }
        },

        setFit(f) {
            this.fit = f;
            localStorage.setItem('reader_fit', f);
        },

        setDirection(d) {
            this.direction = d;
            localStorage.setItem('reader_direction', d);
        },

        // ── FULLSCREEN ──
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen?.();
            } else {
                document.exitFullscreen?.();
            }
        },

        // ── SAVE BOOKMARK ──
        debouncedSave() {
            clearTimeout(this._saveTimer);
            this._saveTimer = setTimeout(() => this.save(), 800);
        },

        save() {
            if (!window.csrf) return;

            this.saving = true;

            fetch('/manga/bookmark', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    chapter_id: this.chapterId,
                    page_number: this.currentPage,
                }),
            })
            .finally(() => {
                setTimeout(() => this.saving = false, 1500);
            });
        },
    };
}