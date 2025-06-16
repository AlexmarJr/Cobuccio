import $ from 'jquery';  // Importa jQuery
import 'jquery-mask-plugin'; // Plugin precisa do jQuery disponível

import './bootstrap';

import Alpine from 'alpinejs';

window.$ = $;       // Expõe jQuery globalmente
window.jQuery = $;  // Expõe jQuery globalmente (alguns plugins exigem)

window.Alpine = Alpine;

Alpine.start();