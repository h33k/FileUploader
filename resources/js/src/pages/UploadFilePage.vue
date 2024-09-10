<template>
    <div>
        <FileHandlerComponent :isEditing="isEditing" />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import FileHandlerComponent from '../components/FileHandler.vue';

const route = useRoute();
const isEditing = computed(() => route.name === 'file-edit');
</script>

<style scoped>

</style>
