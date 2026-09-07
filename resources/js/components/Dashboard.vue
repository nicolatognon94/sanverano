<template>
    <div class="container mt-5">
        <h1>Sanverano Smart Lighting</h1>

        <div v-if="dashboard">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <span class="text-muted">Online</span>
                            <h2>{{ dashboard.kpis.online }}</h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <span class="text-muted">Offline</span>
                            <h2>{{ dashboard.kpis.offline }}</h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <span class="text-muted">Potenza</span>
                            <h2>{{ dashboard.kpis.total_power_w }} W</h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <span class="text-muted">Allarmi attivi</span>
                            <h2>{{ dashboard.kpis.active_alarms }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="dashboard.cabinets" class="mt-2">
                <h2>Cabinets</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Stato</th>
                            <th>Potenza</th>
                            <th>Allarmi</th>
                            <th>Dettaglio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cabinet in dashboard.cabinets" :key="cabinet.id">
                            <td>{{ cabinet.id }}</td>
                            <td>{{ cabinet.name }}</td>
                            <td>
                                {{ cabinet.currentState.status }}
                            </td>
                            <td>{{ cabinet.currentState.power_w }} W</td>
                            <td>{{ cabinet.alarms }}</td>
                            <td><button class="btn btn-primary" @click="showCabinetDetail(cabinet)">Dettaglio</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </div>

        <p v-else>Caricamento...</p>

    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { RouterView } from 'vue-router';
const dashboard = ref(null);
const router = useRouter();
onMounted(async () => {
    const response = await axios.get('/api/dashboard');

    dashboard.value = response.data;

    console.log(dashboard.value);
});

function showCabinetDetail(cabinet) {
    router.push(`/cabinet/${cabinet.id}`);
}   
</script>