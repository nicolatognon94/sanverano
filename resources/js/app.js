import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
// importo bootstrap
import '../css/app.css';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap/dist/js/bootstrap.js';

createApp(App).use(router).mount('#app');