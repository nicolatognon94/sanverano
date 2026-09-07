import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
    history: createWebHistory(),

    routes: [
        {
            path: '/',
            component: () => import('./components/Dashboard.vue'),
        },
        {
            path: '/cabinet/:id',
            component: () => import('./components/CabinetDetail.vue'),
        },
    ],
});

export default router;