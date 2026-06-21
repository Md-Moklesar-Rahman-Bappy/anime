import './bootstrap';

// ✅ Styles
import 'plyr/dist/plyr.css';

// ✅ Alpine core
import Alpine from 'alpinejs';

// ✅ Alpine plugins
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import persist from '@alpinejs/persist';
import intersect from '@alpinejs/intersect';

// ✅ Components
import { player } from './components/player';
import { dashboard } from './admin/dashboard';
import { adminForm, toastCenter, smartDropzone } from './components/adminForm';
import { ajaxPagination } from './components/ajaxPagination';
import { liveComments } from './components/liveComments';
import { genresManager } from './components/genresManager';
import { liveReports } from './components/liveReports';
import { liveRequests } from './components/liveRequests';
import { settingsPreview } from './components/settingsPreview';
import { mangaDashboard } from './admin/mangaDashboard';
import { passwordForm } from './components/passwordForm';
import { mangaReader } from './components/mangaReader';
import { player, watchPage } from './components/watchPlayer';


// ✅ Register Alpine plugins  (⚠️ this was missing)
Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(persist);
Alpine.plugin(intersect);

// ✅ Global helpers
window.Alpine = Alpine;
window.csrf   = document.querySelector('meta[name="csrf-token"]')?.content;

window.bus = {
    emit(event, payload) {
        window.dispatchEvent(new CustomEvent(event, { detail: payload }));
    }
};

// ✅ Register components
Alpine.data('player',          player);
Alpine.data('dashboard',       dashboard);
Alpine.data('adminForm',       adminForm);
Alpine.data('toastCenter',     toastCenter);
Alpine.data('smartDropzone',   smartDropzone);
Alpine.data('ajaxPagination',  ajaxPagination);
Alpine.data('liveComments',    liveComments);
Alpine.data('genresManager',   genresManager);
Alpine.data('liveReports',     liveReports);
Alpine.data('liveRequests',    liveRequests);
Alpine.data('settingsPreview', settingsPreview);
Alpine.data('mangaDashboard',  mangaDashboard);
Alpine.data('passwordForm',    passwordForm);
Alpine.data('mangaReader',     mangaReader);
Alpine.data('player',          player);
Alpine.data('watchPage',       watchPage);

// ✅ Debug mode
if (import.meta.env.DEV) {
    document.addEventListener('alpine:init', () => {
        console.log('[AniKoto] Alpine initialized ✅');
    });
}

// ✅ Global error handlers
window.addEventListener('error', (e) => {
    console.error('[JS Error]', e.error || e.message);
});

window.addEventListener('unhandledrejection', (e) => {
    console.error('[Promise Error]', e.reason);
});

// ✅ Start Alpine
Alpine.start();