import './bootstrap';

import 'plyr/dist/plyr.css';
import Alpine from 'alpinejs';
import { player } from './components/player';
import { dashboard } from './admin/dashboard';
window.Alpine = Alpine;

Alpine.data('player', player);
Alpine.data('dashboard', dashboard);

Alpine.start();
