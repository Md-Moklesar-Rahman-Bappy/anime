export function player(config = {}) {
    return {
        // ── CONFIG ──
        servers:    config.servers    || [],
        isYoutube:  config.isYoutube  || false,
        youtubeId:  config.youtubeId  || null,
        nextUrl:    config.nextUrl    || null,
        prevUrl:    config.prevUrl    || null,
        episodeId:  config.episodeId,
        animeId:    config.animeId,
        isAuth:     config.isAuth     || false,
        loginUrl:   config.loginUrl   || '/login',
        skipTimes:  config.skipTimes  || null,

        // ── STATE ──
        video: null,
        plyr: null,
        playing: false,
        isEmbed: false,
        embedUrl: '',
        currentServerIndex: 0,
        listOpen: false,
        reportOpen: false,
        submitting: false,
        showSkipIntro: false,
        showSkipOutro: false,
        showNextCountdown: false,
        countdownSeconds: 5,
        _countdownInterval: null,
        _historyTimer: null,

        favoriteCategory: window.PLAYER_FAV_CATEGORY ?? null,
        reportType: 'broken_video',
        reportDesc: '',

        config: {
            autoPlay: true,
            autoNext: true,
            autoSkip: true,
            theater: false,
        },

        // ── LIFECYCLE ──
        async init() {
            // Load saved config
            const saved = localStorage.getItem('player_config');
            if (saved) {
                try { this.config = { ...this.config, ...JSON.parse(saved) }; } catch (_) {}
            }

            // Restore theater mode
            if (this.config.theater) document.body.classList.add('theater-mode');

            // Setup first server
            await this.setupPlayer(this.servers[0]);
        },

        // ── PLAYER SETUP ──
        async setupPlayer(server) {
            if (!server) return;

            this.isEmbed = server.type === 'embed';
            this.embedUrl = this.isEmbed ? server.url : '';

            if (this.isEmbed) return;

            await this.$nextTick();
            this.video = this.$refs.video;
            if (!this.video) return;

            // Update source for HLS/MP4
            if (!this.isYoutube && server.url) {
                const source = this.video.querySelector('source');
                if (source) {
                    source.src = server.url;
                    this.video.load();
                }
            }

            // Lazy-load Plyr
            try {
                const Plyr = (await import('plyr')).default;
                this.plyr = new Plyr(this.video, {
                    controls: ['play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'],
                    keyboard: { focused: false, global: false },
                    speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2] },
                    quality: { default: 720, options: [240, 360, 480, 720, 1080] },
                });

                this.plyr.on('play',  () => this.playing = true);
                this.plyr.on('pause', () => this.playing = false);
                this.plyr.on('timeupdate', () => { this.checkSkip(); this.saveHistory(); });
                this.plyr.on('ended', () => this.onVideoEnded());

                if (this.config.autoPlay) this.plyr.play().catch(() => {});
            } catch (e) {
                // Plyr failed → fallback to native controls
                this.video.controls = true;
                this.video.addEventListener('play',  () => this.playing = true);
                this.video.addEventListener('pause', () => this.playing = false);
                this.video.addEventListener('timeupdate', () => { this.checkSkip(); this.saveHistory(); });
                this.video.addEventListener('ended', () => this.onVideoEnded());
                if (this.config.autoPlay) this.video.play().catch(() => {});
            }
        },

        // ── PLAYBACK ──
        togglePlay() {
            if (this.plyr) this.plyr.togglePlay();
            else if (this.video) this.video.paused ? this.video.play() : this.video.pause();
        },

        skip(seconds) {
            const t = this.plyr?.currentTime ?? this.video?.currentTime ?? 0;
            if (this.plyr) this.plyr.currentTime = Math.max(0, t + seconds);
            else if (this.video) this.video.currentTime = Math.max(0, t + seconds);
        },

        toggleMute() {
            if (this.plyr) this.plyr.muted = !this.plyr.muted;
            else if (this.video) this.video.muted = !this.video.muted;
        },

        toggleFullscreen() {
            if (this.plyr) this.plyr.fullscreen.toggle();
            else if (this.video?.requestFullscreen) this.video.requestFullscreen();
        },

        // ── CONFIG TOGGLES ──
        toggle(key) {
            this.config[key] = !this.config[key];
            this.saveConfig();
        },

        toggleTheater() {
            this.config.theater = !this.config.theater;
            document.body.classList.toggle('theater-mode', this.config.theater);
            this.saveConfig();
        },

        saveConfig() {
            localStorage.setItem('player_config', JSON.stringify(this.config));
        },

        // ── SERVERS ──
        switchServer(index) {
            if (!this.servers[index]) return;
            this.currentServerIndex = index;
            this.setupPlayer(this.servers[index]);
        },

        // ── SKIP SEGMENTS ──
        checkSkip() {
            const t = this.plyr?.currentTime ?? this.video?.currentTime ?? 0;
            const s = this.skipTimes || {};

            this.showSkipIntro = false;
            this.showSkipOutro = false;

            if (s.intro_start != null && s.intro_end != null && t >= s.intro_start && t <= s.intro_end) {
                this.showSkipIntro = true;
                if (this.config.autoSkip) this.skipIntro();
            }

            if (s.outro_start != null && s.outro_end != null && t >= s.outro_start && t <= s.outro_end) {
                this.showSkipOutro = true;
                if (this.config.autoSkip) this.skipOutro();
            }
        },

        skipIntro() {
            const end = this.skipTimes?.intro_end;
            if (end == null) return;
            if (this.plyr) this.plyr.currentTime = end;
            else if (this.video) this.video.currentTime = end;
            this.showSkipIntro = false;
        },

        skipOutro() {
            if (this.nextUrl) window.location.href = this.nextUrl;
        },

        // ── NEXT EPISODE COUNTDOWN ──
        onVideoEnded() {
            if (!this.config.autoNext || !this.nextUrl) return;

            this.countdownSeconds = 5;
            this.showNextCountdown = true;

            this._countdownInterval = setInterval(() => {
                this.countdownSeconds--;
                if (this.countdownSeconds <= 0) this.playNextNow();
            }, 1000);
        },

        cancelNextCountdown() {
            clearInterval(this._countdownInterval);
            this.showNextCountdown = false;
        },

        playNextNow() {
            clearInterval(this._countdownInterval);
            window.location.href = this.nextUrl;
        },

        // ── FAVORITES ──
        updateList(category) {
            if (!this.isAuth) { window.location.href = this.loginUrl; return; }

            fetch('/favorites/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrf,
                },
                body: JSON.stringify({ anime_id: this.animeId, category }),
            })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(() => {
                this.favoriteCategory = category;
                this.listOpen = false;
                window.bus?.emit('toast', {
                    type: 'success',
                    message: category ? `Added to ${category.replace('_', ' ')}` : 'Removed from list'
                });
            })
            .catch(() => window.bus?.emit('toast', { type: 'error', message: 'Failed to update list' }));
        },

        // ── REPORTS ──
        submitReport() {
            if (!this.isAuth) { window.location.href = this.loginUrl; return; }

            this.submitting = true;
            fetch('/reports', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrf,
                },
                body: JSON.stringify({
                    episode_id: this.episodeId,
                    issue_type: this.reportType,
                    description: this.reportDesc,
                }),
            })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(() => {
                this.reportOpen = false;
                this.reportDesc = '';
                window.bus?.emit('toast', { type: 'success', message: 'Report submitted. Thank you!' });
            })
            .catch(() => window.bus?.emit('toast', { type: 'error', message: 'Failed to submit report' }))
            .finally(() => this.submitting = false);
        },

        // ── WATCH HISTORY ──
        saveHistory() {
            if (!this.isAuth) return;

            clearTimeout(this._historyTimer);
            this._historyTimer = setTimeout(() => {
                const time = this.plyr?.currentTime ?? this.video?.currentTime ?? 0;
                const dur  = this.plyr?.duration   ?? this.video?.duration   ?? 0;

                fetch('/watch-history', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrf,
                    },
                    body: JSON.stringify({
                        episode_id: this.episodeId,
                        progress: Math.floor(time),
                        completed: dur > 0 && time >= dur - 10,
                    }),
                }).catch(() => {});
            }, 3000);
        },

        // ── WRAPPER for outer Alpine watchPage ──
    };
}

// Outer page state (for kbd shortcuts like T = theater)
export function watchPage() {
    return {
        init() {
            window.addEventListener('keydown', (e) => {
                const tag = e.target.tagName;
                if (tag === 'TEXTAREA' || tag === 'INPUT' || tag === 'SELECT') return;

                if (e.key.toLowerCase() === 't') {
                    // Trigger inner player's theater toggle
                    document.querySelector('[x-data^="player"]')?._x_dataStack?.[0]?.toggleTheater?.();
                }
                if (e.key.toLowerCase() === 'n' && window.PLAYER_NEXT_URL) window.location.href = window.PLAYER_NEXT_URL;
                if (e.key.toLowerCase() === 'b' && window.PLAYER_PREV_URL) window.location.href = window.PLAYER_PREV_URL;
            });
        }
    };
}