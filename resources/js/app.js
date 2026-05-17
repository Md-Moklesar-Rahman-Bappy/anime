import './bootstrap';

import 'plyr/dist/plyr.css';
import Alpine from 'alpinejs';
import { player } from './components/player';

window.Alpine = Alpine;

Alpine.data('player', player);

Alpine.start();
