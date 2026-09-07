<template>
    <RouterView />
</template>
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { RouterView } from 'vue-router';

const router = useRouter();

</script>