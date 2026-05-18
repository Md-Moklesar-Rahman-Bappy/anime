import Plyr from 'plyr';

const STORAGE_KEY = 'aniwaves_player_config';

function loadConfig() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (raw) return { ...defaultConfig(), ...JSON.parse(raw) };
    } catch (e) {}
    return defaultConfig();
}

function saveConfig(config) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(config));
    } catch (e) {}
}

function defaultConfig() {
    return {
        autoPlay: false,
        autoNext: true,
        autoSkip: false,
        skipSeconds: 10,
        isLight: false,
    };
}

export function player() {
    return {
        config: loadConfig(),
        player: null,
        servers: [],
        languages: [],
        currentLanguage: null,
        currentServers: [],
        currentIndex: 0,
        isFavorited: false,
        favoriteCategory: null,
        isYoutube: false,
        listOpen: false,
        reportOpen: false,
        reportType: 'broken',
        reportDesc: '',
        submitting: false,
        showSkipIntro: false,
        showSkipOutro: false,
        skipTimes: null,

        _keyboardHandler: null,

        init() {
            this.servers = window.PLAYER_SERVERS || [];
            this.languages = window.PLAYER_LANGUAGES || [];
            this.currentLanguage = this.languages[0] || null;
            this.currentServers = this.servers.filter(s => s.language === this.currentLanguage);
            this.currentIndex = 0;
            this.isYoutube = window.PLAYER_IS_YOUTUBE || false;
            this.isFavorited = window.PLAYER_IS_FAVORITED || false;
            this.favoriteCategory = window.PLAYER_FAV_CATEGORY || null;
            this.skipTimes = window.PLAYER_SKIP_TIMES || null;

            this.$nextTick(() => {
                this.initPlyr();
                this.setupKeyboard();
                this.applyTheme();
            });
        },

        destroy() {
            if (this._keyboardHandler) {
                document.removeEventListener('keydown', this._keyboardHandler);
                this._keyboardHandler = null;
            }
            if (this.player) {
                this.player.destroy();
                this.player = null;
            }
        },

        initPlyr() {
            const video = this.$el.querySelector('video');
            if (!video) return;

            this.player = new Plyr(video, {
                controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'volume', 'fullscreen'],
                keyboard: { focused: true, global: false },
                tooltips: { controls: false, seek: true },
                seekTime: this.config.skipSeconds,
                muted: false,
                autoplay: this.config.autoPlay,
            });

            this.player.on('ready', () => {
                if (this.isYoutube && this.currentServers[0]) {
                    const ytId = this.extractYoutubeId(this.currentServers[0].url);
                    if (ytId) {
                        this.player.source = {
                            type: 'video',
                            sources: [{ src: ytId, provider: 'youtube' }],
                        };
                    }
                }
                if (this.config.autoPlay) {
                    this.player.play();
                }
            });

            this.player.on('timeupdate', () => {
                this.checkSkip();
            });

            this.player.on('ended', () => {
                this.handleEnded();
            });
        },

        extractYoutubeId(url) {
            const patterns = [
                /youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/,
                /youtu\.be\/([a-zA-Z0-9_-]+)/,
                /youtube\.com\/embed\/([a-zA-Z0-9_-]+)/,
            ];
            for (const p of patterns) {
                const m = url.match(p);
                if (m) return m[1];
            }
            return null;
        },

        checkSkip() {
            if (!this.config.autoSkip || !this.skipTimes || !this.player) return;
            const t = this.player.currentTime;
            const st = this.skipTimes;

            if (st.intro_start && st.intro_end && t >= st.intro_start && t < st.intro_end) {
                this.player.currentTime = st.intro_end;
            }
            if (st.outro_start && st.outro_end && t >= st.outro_start && t < st.outro_end) {
                this.player.currentTime = st.outro_end;
            }

            this.showSkipIntro = !!(st.intro_start && st.intro_end && t >= st.intro_start - 3 && t < st.intro_end);
            this.showSkipOutro = !!(st.outro_start && st.outro_end && t >= st.outro_start - 3 && t < st.outro_end);
        },

        skipIntro() {
            if (this.skipTimes?.intro_end) {
                this.player.currentTime = this.skipTimes.intro_end;
                this.showSkipIntro = false;
            }
        },

        skipOutro() {
            if (this.skipTimes?.outro_end) {
                this.player.currentTime = this.skipTimes.outro_end;
                this.showSkipOutro = false;
            }
        },

        handleEnded() {
            if (this.config.autoNext && window.PLAYER_NEXT_URL) {
                window.location.href = window.PLAYER_NEXT_URL;
            }
        },

        togglePlay() {
            if (this.player) {
                this.player.togglePlay();
            }
        },

        skip(seconds) {
            if (this.player) {
                this.player.currentTime += seconds;
            }
        },

        toggleAutoPlay() {
            this.config.autoPlay = !this.config.autoPlay;
            saveConfig(this.config);
        },

        toggleAutoNext() {
            this.config.autoNext = !this.config.autoNext;
            saveConfig(this.config);
        },

        toggleAutoSkip() {
            this.config.autoSkip = !this.config.autoSkip;
            saveConfig(this.config);
        },

        toggleLight() {
            this.config.isLight = !this.config.isLight;
            saveConfig(this.config);
            this.applyTheme();
        },

        applyTheme() {
            const wrapper = this.$el.querySelector('.plyr-wrapper');
            if (wrapper) {
                wrapper.classList.toggle('light', this.config.isLight);
            }
        },

        setupKeyboard() {
            this._keyboardHandler = (e) => {
                if (!this.player) return;
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;

                switch (e.key.toLowerCase()) {
                    case 'j':
                        e.preventDefault();
                        this.skip(-this.config.skipSeconds);
                        break;
                    case 'l':
                        e.preventDefault();
                        this.skip(this.config.skipSeconds);
                        break;
                    case 'n':
                        if (window.PLAYER_NEXT_URL) {
                            window.location.href = window.PLAYER_NEXT_URL;
                        }
                        break;
                    case 'b':
                        if (window.PLAYER_PREV_URL) {
                            window.location.href = window.PLAYER_PREV_URL;
                        }
                        break;
                }
            };
            document.addEventListener('keydown', this._keyboardHandler);
        },

        switchLanguage(lang) {
            this.currentLanguage = lang;
            this.currentServers = this.servers.filter(s => s.language === lang);
            this.currentIndex = 0;
            if (this.currentServers.length > 0) {
                this.loadServer(0);
            }
        },

        switchServer(index) {
            this.currentIndex = index;
            this.loadServer(index);
        },

        loadServer(index) {
            const server = this.currentServers[index];
            if (!server) return;

            if (server.type === 'youtube') {
                const ytId = this.extractYoutubeId(server.url);
                if (ytId && this.player) {
                    this.player.source = {
                        type: 'video',
                        sources: [{ src: ytId, provider: 'youtube' }],
                    };
                }
                return;
            }

            if (this.player) {
                const mimeType = server.type === 'm3u8' ? 'application/x-mpegURL' : 'video/mp4';
                this.player.source = {
                    type: 'video',
                    sources: [{ src: server.url, type: mimeType }],
                };
            }
        },

        async updateList(category) {
            if (!window.PLAYER_IS_AUTH) {
                window.location.href = '/login';
                return;
            }

            try {
                const res = await fetch('/favorites/list', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: JSON.stringify({
                        anime_id: window.PLAYER_ANIME_ID,
                        category: category,
                    }),
                });
                const data = await res.json();
                if (data.status === 'ok') {
                    this.isFavorited = !!category;
                    this.favoriteCategory = category || null;
                }
            } catch (e) {
                console.error('Failed to update list:', e);
            }
            this.listOpen = false;
        },

        async submitReport() {
            if (!this.reportDesc.trim() && this.reportType !== 'skip_time_wrong') return;
            this.submitting = true;

            try {
                const res = await fetch('/reports/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                    body: JSON.stringify({
                        episode_id: window.PLAYER_EPISODE_ID,
                        issue_type: this.reportType,
                        description: this.reportDesc,
                    }),
                });
                const data = await res.json();
                if (data.status === 'ok') {
                    this.reportOpen = false;
                    this.reportDesc = '';
                    this.reportType = 'broken';
                }
            } catch (e) {
                console.error('Failed to submit report:', e);
            }
            this.submitting = false;
        },

        toggleList() {
            this.listOpen = !this.listOpen;
            this.reportOpen = false;
        },

        toggleReport() {
            this.reportOpen = !this.reportOpen;
            this.listOpen = false;
        },

        categories: [
            { value: 'watching', label: 'Watching' },
            { value: 'completed', label: 'Completed' },
            { value: 'plan_to_watch', label: 'Plan to Watch' },
            { value: 'on_hold', label: 'On Hold' },
            { value: 'dropped', label: 'Dropped' },
        ],

        issueTypes: [
            { value: 'broken', label: 'Broken' },
            { value: 'audio_not_synced', label: 'Audio not synced' },
            { value: 'sub_not_synced', label: 'Subs not synced' },
            { value: 'skip_time_wrong', label: 'Skip time wrong' },
            { value: 'other', label: 'Other' },
        ],
    };
}
