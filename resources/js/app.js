import './bootstrap';
import { createApp } from 'vue';
import PosApp from './pos/PosApp.vue';

const mount = document.getElementById('pos-app');

if (mount) {
    const user = JSON.parse(mount.dataset.user);

    createApp(PosApp, { user }).mount('#pos-app');
}
