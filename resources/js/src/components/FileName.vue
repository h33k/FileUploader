<template>
    <div>
        <input class="form-control mb-3" type="text" v-model="fileName" placeholder="Enter file name (optional)"/>
    </div>
</template>

<script setup>
import {defineProps, defineEmits, computed} from 'vue';


const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
});


const emit = defineEmits(['update:modelValue']);

// ожидание изменений чтобы передать их родительскому компоненту
const fileName = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});
</script>

<style scoped>

</style>
