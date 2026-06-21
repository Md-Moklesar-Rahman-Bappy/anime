import './bootstrap';

// ✅ Styles
import 'plyr/dist/plyr.css';

// ✅ Alpine
import Alpine from 'alpinejs';

// ✅ Components
import { player } from './components/player';
import { dashboard } from './admin/dashboard';
import { adminForm, toastCenter } from './components/adminForm';

// ✅ Global Alpine
window.Alpine = Alpine;

// ✅ Register Alpine components
Alpine.data('player', player);
Alpine.data('dashboard', dashboard);
Alpine.data('adminForm', adminForm);
Alpine.data('toastCenter', toastCenter);

// ✅ Debug mode (optional)
if (import.meta.env.DEV) {
    console.log('[AniKoto] Alpine initialized');
}

// ✅ Global error handler
window.addEventListener('error', (e) => {
    console.error('[JS Error]', e.error || e.message);
});

// ✅ Start Alpine
Alpine.start();