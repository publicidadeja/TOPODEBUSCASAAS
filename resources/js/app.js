import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import Swal from 'sweetalert2';
window.Swal = Swal;
import BusinessImage from './components/BusinessImage.vue'

// Se estiver usando Vue 3
createApp({
    components: {
        BusinessImage
    }
}).mount('#app')

// Ou se estiver usando Vue 2
Vue.component('business-image', BusinessImage)