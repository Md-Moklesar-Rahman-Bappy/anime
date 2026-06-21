import './bootstrap';

// ✅ Styles
import 'plyr/dist/plyr.css';

// ✅ Alpine
import Alpine from 'alpinejs';

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

// ✅ Global Alpine
window.Alpine = Alpine;

// ✅ Register components
Alpine.data('player', player);
Alpine.data('dashboard', dashboard);
Alpine.data('adminForm', adminForm);
Alpine.data('toastCenter', toastCenter);
Alpine.data('smartDropzone', smartDropzone);
Alpine.data('ajaxPagination', ajaxPagination);
Alpine.data('liveComments', liveComments);
Alpine.data('genresManager', genresManager);
Alpine.data('liveReports', liveReports);
Alpine.data('liveRequests', liveRequests);
Alpine.data('settingsPreview', settingsPreview);

// ✅ Debug mode
if (import.meta.env.DEV) {
    console.log('[AniKoto] Alpine initialized');
}

// ✅ Global error handler
window.addEventListener('error', (e) => {
    console.error('[JS Error]', e.error || e.message);
});

// ✅ Start Alpine
Alpine.start();
