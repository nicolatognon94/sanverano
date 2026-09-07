<template>
    <div class="container mt-5" >
        <div v-if="loading">
            Caricamento...
        </div>

        <div v-else-if="error" class="alert alert-danger">
            {{ error }}
        </div>

        <div v-else>
            <h1>Cabinet Detail</h1>
             <div class="row" v-if="cabinet?.anagraficaCabinet">
                    <div class="col-3">
                        <div class="card">
                            <div class="card-body">
                                <span class="text-muted">Codice esterno</span>
                                <h2>{{ cabinet.anagraficaCabinet.code }}</h2>
                            </div>
                        </div> 
                        
                    </div>
                    <div class="col-3">
                        <div class="card">
                            <div class="card-body">
                                <span class="text-muted">Nome</span>
                                <h2>{{ cabinet.anagraficaCabinet.name }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card">
                            <div class="card-body">
                                <span class="text-muted">Lotto</span>
                                <h2>{{ cabinet.anagraficaCabinet.lot }}</h2>
                            </div>
                        </div>
                    </div> 
                    <div class="col-3">
                        <div class="card">
                            <div class="card-body">
                                <span class="text-muted">Stato</span>
                                <h2>{{ statusCabinet }}</h2>
                            </div>
                        </div>
                    </div> 
                    <button
                            class="btn btn-success me-2"
                            @click="sendCommand('on')"
                        >
                            Accendi
                        </button>

                        <button
                            class="btn btn-danger"
                            @click="sendCommand('off')"
                        >
                            Spegni
                        </button>

                      <div class="mt-3">
                    <label for="dimming" class="form-label">
                        Luminosità: {{ dimming }}%
                    </label>

                    <input
                        id="dimming"
                        type="range"
                        class="form-range"
                        min="0"
                        max="100"
                        v-model="dimming"
                    >
                    <button
    class="btn btn-primary mt-2"
    @click="sendCommand('dim', dimming)"
>
    Applica luminosità
</button>
                </div>  
                </div>
                <div class="row mt-4" v-if="cabinet?.cabinetStatusInfo">
                    <div class="col-3">
                        <div class="card">
                            <div class="card-body">
                                <span class="text-muted">Potenza</span>
                                <h2>{{ cabinet.cabinetStatusInfo.power_w }} W</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card">
                            <div class="card-body">
                                <span class="text-muted">Allarmi</span>
                                <h2>{{ cabinet.cabinetStatusInfo.alarms }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card">
                            <div class="card-body">
                                <span class="text-muted">Ultimo Seen</span>
                                <h2>{{ cabinet.cabinetStatusInfo.last_seen_at }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

       <div class="card mt-4" style="height: 400px;">
            <div class="card-body">
                <Line
                    :data="chartData"
                    :options="chartOptions"
                />
            </div>
        </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
const cabinet = ref(null);
const route = useRoute();
const loading = ref(true);
const error = ref(null);
const dimming = ref(70);
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

const chartData = ref({
    labels: [],
    datasets: [
        {
            label: 'Potenza',
            data: [],
        },
    ],
});
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: {
            ticks: {
                autoSkip: false,
            },
        },
    },
};
const statusCabinet = computed(() => {
    if(cabinet.value?.cabinetStatusInfo){
        return cabinet.value.cabinetStatusInfo.status;
    }
    return 'unknown';
});
onMounted(async () => {
    try {
        const response = await axios.get('/api/cabinets/' + route.params.id);
        cabinet.value = response.data;

        await loadPower();

        setInterval(loadPower, 10000);
    } catch (e) {
        console.error(e);
        error.value = 'Impossibile caricare il cabinet';
    } finally {
        loading.value = false;
    }
});
const loadPower = async () => {
    const response = await axios.get('/api/cabinets/' + route.params.id + '/power');

    chartData.value.labels = Object.keys(response.data).map(timestamp => {
        return timestamp.substring(11, 16);
    });

    chartData.value.datasets[0].data = Object.values(response.data);
};
const sendCommand = async (action, targetValue = null) => {
    const response = await axios.post('/api/commands', {
        asset_type: 'point',
        asset_id: route.params.id,
        action: action,
        target_value: targetValue,
    });

    console.log(response.data);
};
</script>