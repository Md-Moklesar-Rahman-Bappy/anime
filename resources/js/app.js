import './bootstrap';

// ✅ Styles
import 'plyr/dist/plyr.css';

// ✅ Alpine
import Alpine from 'alpinejs';

// ✅ Components
import { player } from './components/player';
import { dashboard } from './admin/dashboard';

// ✅ Global Alpine
window.Alpine = Alpine;

// ✅ Register components
Alpine.data('player', player);
Alpine.data('dashboard', dashboard);

// ✅ Debug mode (optional in dev)
if (import.meta.env.DEV) {
    window.Alpine = Alpine;
    console.log('[AniKoto] Alpine initialized');
}

// ✅ Global error handler (important)
window.addEventListener('error', (e) => {
    console.error('[JS Error]', e.error || e.message);
});

// ✅ Start Alpine
Alpine.start();
