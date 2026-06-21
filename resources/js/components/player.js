import Plyr from 'plyr';
import Hls from 'hls.js';

const STORAGE_KEY = 'aniwaves_player_config';
const PROGRESS_PREFIX = 'aniwaves_progress_';

function defaultConfig() {
    return {
        autoPlay: false,
        autoNext: true,
        autoSkip: false,
        skipSeconds: 10,
        isLight: false,
        rememberProgress: true,
    };
}

function loadConfig() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);

        if (raw) {
            return {
                ...defaultConfig(),
                ...JSON.parse(raw),
            };
        }
    } catch (_) {}

    return defaultConfig();
}

function saveConfig(config) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(config));
    } catch (_) {}
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function parseJsonResponse(response) {
    const data = await response.json().catch(() => null);

    if (!response.ok) {
        throw new Error(data?.message || 'Request failed.');
    }

    return data;
}

export function player() {
    return {
        config: loadConfig(),

        player: null,
        hls: null,

        servers: [],
        languages: [],
        currentLanguage: null,
        currentServers: [],
        currentIndex: 0,

        isEmbed: false,
        embedUrl: null,

        isFavorited: false,
        favoriteCategory: null,

        playing: false,

        listOpen: false,
        reportOpen: false,
        reportType: 'broken',
        reportDesc: '',
        submitting: false,

        showSkipIntro: false,
        showSkipOutro: false,
        skipTimes: null,

        _keyboardHandler: null,
        _progressTimer: null,
        _loadId: 0,

        /*
        |--------------------------------------------------------------------------
        | INIT
        |--------------------------------------------------------------------------
        */
        init() {
            this.servers = Array.isArray(window.PLAYER_SERVERS)
                ? window.PLAYER_SERVERS
                : [];

            this.languages = Array.isArray(window.PLAYER_LANGUAGES)
                ? window.PLAYER_LANGUAGES
                : [];

            this.isFavorited = Boolean(window.PLAYER_IS_FAVORITED);
            this.favoriteCategory = window.PLAYER_FAV_CATEGORY || null;
            this.skipTimes = window.PLAYER_SKIP_TIMES || null;

            const initialServer =
                window.PLAYER_INITIAL_SERVER ||
                this.servers[0] ||
                null;

            this.currentLanguage =
                initialServer?.language ||
                this.languages[0] ||
                this.servers[0]?.language ||
                null;

            this.currentServers = this.currentLanguage
                ? this.servers.filter((server) => server.language === this.currentLanguage)
                : this.servers;

            this.currentIndex = Math.max(
                0,
                this.currentServers.findIndex((server) => {
                    return initialServer && server.server_id === initialServer.server_id;
                })
            );

            const selectedServer =
                this.currentServers[this.currentIndex] ||
                initialServer ||
                null;

            if (selectedServer && this.isEmbedType(selectedServer.type)) {
                this.isEmbed = true;
                this.embedUrl = this.embedUrlFor(selectedServer);
            }

            this.$nextTick(() => {
                if (!this.isEmbed) {
                    this.initPlyr();

                    if (selectedServer) {
                        this.loadServer(this.currentIndex);
                    }
                }

                this.setupKeyboard();
                this.applyTheme();
            });
        },

        /*
        |--------------------------------------------------------------------------
        | DESTROY
        |--------------------------------------------------------------------------
        */
        destroy() {
            this._loadId++;

            this.destroyProgressTimer();
            this.destroyKeyboard();
            this.destroyHls();
            this.destroyPlyr();
        },

        /*
        |--------------------------------------------------------------------------
        | PLYR
        |--------------------------------------------------------------------------
        */
        initPlyr() {
            if (this.isEmbed) return;

            const video = this.$el.querySelector('video');

            if (!video) return;

            if (this.player) {
                this.destroyPlyr();
            }

            this.player = new Plyr(video, {
                controls: [
                    'play-large',
                    'play',
                    'progress',
                    'current-time',
                    'duration',
                    'mute',
                    'volume',
                    'settings',
                    'fullscreen',
                ],
                settings: ['speed'],
                keyboard: {
                    focused: true,
                    global: false,
                },
                tooltips: {
                    controls: false,
                    seek: true,
                },
                seekTime: this.config.skipSeconds,
                autoplay: false,
            });

            this.player.on('ready', () => {
                this.restoreProgress();

                if (this.config.autoPlay) {
                    this.player.play().catch(() => {});
                }
            });

            this.player.on('play', () => {
                this.playing = true;
                this.startProgressTimer();
            });

            this.player.on('pause', () => {
                this.playing = false;
                this.saveProgress();
            });

            this.player.on('ended', () => {
                this.playing = false;
                this.clearProgress();
                this.handleEnded();
            });

            this.player.on('timeupdate', () => {
                this.checkSkip();
            });
        },

        destroyPlyr() {
            if (this.player) {
                this.player.destroy();
                this.player = null;
            }
        },

        /*
        |--------------------------------------------------------------------------
        | SERVER SWITCHING
        |--------------------------------------------------------------------------
        */
        switchLanguage(language) {
            this.currentLanguage = language;
            this.currentServers = this.servers.filter((server) => server.language === language);
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
            this._loadId++;

            const loadId = this._loadId;
            const server = this.currentServers[index];

            if (!server || !server.url) return;

            this.saveProgress();

            if (this.isEmbedType(server.type)) {
                this.destroyHls();
                this.destroyPlyr();

                this.isEmbed = true;
                this.embedUrl = this.embedUrlFor(server);

                return;
            }

            if (this.isEmbed) {
                this.isEmbed = false;
                this.embedUrl = null;

                this.$nextTick(() => {
                    if (loadId !== this._loadId) return;

                    this.initPlyr();
                    this.loadHtml5Source(server);
                });

                return;
            }

            this.loadHtml5Source(server);
        },

        loadHtml5Source(server) {
            const video = this.$el.querySelector('video');

            if (!video) return;

            this.destroyHls();

            if (!this.player) {
                this.initPlyr();
            }

            if (server.type === 'm3u8') {
                this.loadHlsSource(video, server.url);
                return;
            }

            const mimeType = this.getMimeType(server.type);

            if (this.player) {
                this.player.source = {
                    type: 'video',
                    sources: [
                        {
                            src: server.url,
                            type: mimeType,
                        },
                    ],
                };
            }

            if (this.config.autoPlay && this.player) {
                this.player.play().catch(() => {});
            }
        },

        loadHlsSource(video, url) {
            if (Hls.isSupported()) {
                this.hls = new Hls({
                    enableWorker: true,
                    lowLatencyMode: true,
                });

                this.hls.loadSource(url);
                this.hls.attachMedia(video);

                this.hls.on(Hls.Events.MANIFEST_PARSED, () => {
                    if (this.config.autoPlay && this.player) {
                        this.player.play().catch(() => {});
                    }
                });

                this.hls.on(Hls.Events.ERROR, (_, data) => {
                    if (data?.fatal) {
                        console.error('HLS fatal error:', data);
                    }
                });

                return;
            }

            if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = url;

                video.addEventListener(
                    'loadedmetadata',
                    () => {
                        if (this.config.autoPlay && this.player) {
                            this.player.play().catch(() => {});
                        }
                    },
                    { once: true }
                );
            }
        },

        destroyHls() {
            if (this.hls) {
                this.hls.destroy();
                this.hls = null;
            }
        },

        /*
        |--------------------------------------------------------------------------
        | EMBED / YOUTUBE
        |--------------------------------------------------------------------------
        */
        isEmbedType(type) {
            return ['embed', 'youtube'].includes(type);
        },

        embedUrlFor(server) {
            if (server.type === 'youtube') {
                const id = this.extractYoutubeId(server.url);

                return id
                    ? `https://www.youtube.com/embed/${id}?autoplay=${this.config.autoPlay ? 1 : 0}&rel=0`
                    : server.url;
            }

            return server.url;
        },

        extractYoutubeId(url) {
            const patterns = [
                /youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/,
                /youtu\.be\/([a-zA-Z0-9_-]{11})/,
                /youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/,
                /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/,
            ];

            for (const pattern of patterns) {
                const match = String(url).match(pattern);

                if (match) {
                    return match[1];
                }
            }

            return null;
        },

        /*
        |--------------------------------------------------------------------------
        | PLAYER ACTIONS
        |--------------------------------------------------------------------------
        */
        togglePlay() {
            if (this.player) {
                this.player.togglePlay();
            }
        },

        skip(seconds) {
            if (this.player) {
                this.player.currentTime = Math.max(
                    0,
                    this.player.currentTime + seconds
                );
            }
        },

        handleEnded() {
            if (this.config.autoNext && window.PLAYER_NEXT_URL) {
                window.location.href = window.PLAYER_NEXT_URL;
            }
        },

        /*
        |--------------------------------------------------------------------------
        | SKIP INTRO / OUTRO
        |--------------------------------------------------------------------------
        */
        checkSkip() {
            if (!this.skipTimes || !this.player) return;

            const time = this.player.currentTime;
            const skip = this.skipTimes;

            this.showSkipIntro = Boolean(
                skip.intro_start &&
                skip.intro_end &&
                time >= skip.intro_start - 3 &&
                time < skip.intro_end
            );

            this.showSkipOutro = Boolean(
                skip.outro_start &&
                skip.outro_end &&
                time >= skip.outro_start - 3 &&
                time < skip.outro_end
            );

            if (!this.config.autoSkip) return;

            if (
                skip.intro_start &&
                skip.intro_end &&
                time >= skip.intro_start &&
                time < skip.intro_end
            ) {
                this.player.currentTime = skip.intro_end;
            }

            if (
                skip.outro_start &&
                skip.outro_end &&
                time >= skip.outro_start &&
                time < skip.outro_end
            ) {
                this.player.currentTime = skip.outro_end;
            }
        },

        skipIntro() {
            if (this.player && this.skipTimes?.intro_end) {
                this.player.currentTime = this.skipTimes.intro_end;
                this.showSkipIntro = false;
            }
        },

        skipOutro() {
            if (this.player && this.skipTimes?.outro_end) {
                this.player.currentTime = this.skipTimes.outro_end;
                this.showSkipOutro = false;
            }
        },

        /*
        |--------------------------------------------------------------------------
        | CONFIG
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | KEYBOARD
        |--------------------------------------------------------------------------
        */
        setupKeyboard() {
            this.destroyKeyboard();

            this._keyboardHandler = (event) => {
                if (
                    ['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName)
                ) {
                    return;
                }

                switch (event.key.toLowerCase()) {
                    case ' ':
                        event.preventDefault();
                        this.togglePlay();
                        break;

                    case 'arrowleft':
                    case 'j':
                        event.preventDefault();
                        this.skip(-this.config.skipSeconds);
                        break;

                    case 'arrowright':
                    case 'l':
                        event.preventDefault();
                        this.skip(this.config.skipSeconds);
                        break;

                    case 'f':
                        event.preventDefault();
                        this.player?.fullscreen?.toggle();
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

        destroyKeyboard() {
            if (this._keyboardHandler) {
                document.removeEventListener('keydown', this._keyboardHandler);
                this._keyboardHandler = null;
            }
        },

        /*
        |--------------------------------------------------------------------------
        | PROGRESS
        |--------------------------------------------------------------------------
        */
        progressKey() {
            const episodeId = window.PLAYER_EPISODE_ID || 'unknown';

            return `${PROGRESS_PREFIX}${episodeId}`;
        },

        startProgressTimer() {
            this.destroyProgressTimer();

            this._progressTimer = setInterval(() => {
                this.saveProgress();
            }, 5000);
        },

        destroyProgressTimer() {
            if (this._progressTimer) {
                clearInterval(this._progressTimer);
                this._progressTimer = null;
            }
        },

        saveProgress() {
            if (!this.config.rememberProgress || !this.player) return;

            try {
                localStorage.setItem(
                    this.progressKey(),
                    JSON.stringify({
                        time: this.player.currentTime,
                        duration: this.player.duration || 0,
                    })
                );
            } catch (_) {}
        },

        restoreProgress() {
            if (!this.config.rememberProgress || !this.player) return;

            try {
                const raw = localStorage.getItem(this.progressKey());

                if (!raw) return;

                const data = JSON.parse(raw);

                if (
                    data?.time &&
                    data?.duration &&
                    data.time < data.duration - 20
                ) {
                    this.player.currentTime = data.time;
                }
            } catch (_) {}
        },

        clearProgress() {
            try {
                localStorage.removeItem(this.progressKey());
            } catch (_) {}
        },

        /*
        |--------------------------------------------------------------------------
        | FAVORITES
        |--------------------------------------------------------------------------
        */
        async updateList(category) {
            if (!window.PLAYER_IS_AUTH) {
                window.location.href = window.PLAYER_LOGIN_URL;
                return;
            }

            try {
                const response = await fetch('/favorites/list', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        anime_id: window.PLAYER_ANIME_ID,
                        category,
                    }),
                });

                const data = await parseJsonResponse(response);

                if (data.status === 'ok') {
                    this.isFavorited = Boolean(category);
                    this.favoriteCategory = category || null;
                }
            } catch (error) {
                console.error('Failed to update list:', error);
            }

            this.listOpen = false;
        },

        /*
        |--------------------------------------------------------------------------
        | REPORT
        |--------------------------------------------------------------------------
        */
        async submitReport() {
            if (!window.PLAYER_IS_AUTH) {
                window.location.href = window.PLAYER_LOGIN_URL;
                return;
            }

            this.submitting = true;

            try {
                const response = await fetch('/reports', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        episode_id: window.PLAYER_EPISODE_ID,
                        issue_type: this.reportType,
                        description: this.reportDesc,
                    }),
                });

                const data = await parseJsonResponse(response);

                if (data.status === 'ok') {
                    this.reportOpen = false;
                    this.reportDesc = '';
                    this.reportType = 'broken';
                }
            } catch (error) {
                console.error('Failed to submit report:', error);
            }

            this.submitting = false;
        },

        /*
        |--------------------------------------------------------------------------
        | UI TOGGLES
        |--------------------------------------------------------------------------
        */
        toggleList() {
            this.listOpen = !this.listOpen;
            this.reportOpen = false;
        },

        toggleReport() {
            this.reportOpen = !this.reportOpen;
            this.listOpen = false;
        },

        /*
        |--------------------------------------------------------------------------
        | HELPERS
        |--------------------------------------------------------------------------
        */
        getMimeType(type) {
            const map = {
                mp4: 'video/mp4',
                webm: 'video/webm',
                m3u8: 'application/x-mpegURL',
            };

            return map[type] || 'video/mp4';
        },

        /*
        |--------------------------------------------------------------------------
        | STATIC DATA
        |--------------------------------------------------------------------------
        */
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
