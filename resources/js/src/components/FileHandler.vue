<template>
    <div class="pb-4">
        <h1 class="fw-medium text-center py-2">{{ isEditing ? 'Edit your file' : 'Upload your file' }}</h1>

        <FileName v-model="fileName"/>

        <div
            class="drop-zone border rounded p-3 text-center mb-3"
            @drop="onFileDrop"
            @dragover.prevent
            @dragenter.prevent
            @click="triggerFileInput"
        >
            <svg
                class="drop-zone-icon"
                width="64px"
                height="64px"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path d="M12 17L12 10M12 10L15 13M12 10L9 13" stroke="#8e8c84" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"/>
                <path d="M16 7H12H8" stroke="#8e8c84" stroke-width="1.5" stroke-linecap="round"/>
                <path
                    d="M2 12C2 7.28595 2 4.92893 3.46447 3.46447C4.92893 2 7.28595 2 12 2C16.714 2 19.0711 2 20.5355 3.46447C22 4.92893 22 7.28595 22 12C22 16.714 22 19.0711 20.5355 20.5355C19.0711 22 16.714 22 12 22C7.28595 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.714 2 12Z"
                    stroke="#8e8c84" stroke-width="1.5"/>
            </svg>
            <p v-if="!file" class="text-muted">Drag & drop your file here or click to select one</p>
            <p v-else class="text-muted">File ready to upload: {{ file.name }}</p>
        </div>

        <input
            type="file"
            @change="onFileSelected"
            :disabled="isEditing && !fileId"
            class="d-none"
            ref="fileInput"
        />

        <div v-if="uploadProgress !== null" class="progress my-3">
            <div
                class="progress-bar"
                role="progressbar"
                :style="{ width: uploadProgress + '%' }"
                :aria-valuenow="uploadProgress"
                aria-valuemin="0"
                aria-valuemax="100"
            >
                <span class="progress-text">{{ Math.round(uploadProgress) }}%</span>
            </div>
        </div>

        <div v-if="successMessage" class="alert alert-success mt-3">
            {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="alert alert-danger mt-3">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script setup>
import {ref, computed, watch} from 'vue';
import axios from 'axios';
import FileName from './FileName.vue';
import {useRoute} from 'vue-router';

const route = useRoute();
const file = ref(null);
const fileName = ref('');
const successMessage = ref('');
const errorMessage = ref('');
const uploadProgress = ref(null);
const fileId = computed(() => route.params.id);
const isEditing = computed(() => !!fileId.value);

const fileInput = ref(null); // Ссылка на элемент input

watch(fileId, (newId) => {
    if (isEditing.value) {
        fetchFileDetails(newId);
    }
});

const fetchFileDetails = async (id) => {
    try {
        const response = await axios.get(`/files/${id}`);
        fileName.value = response.data.filename;
    } catch (error) {
        console.error('Failed to fetch file details', error);
        errorMessage.value = 'Failed to fetch file details';
    }
};

const onFileSelected = (event) => {
    file.value = event.target.files[0];
    uploadFile();
};

const onFileDrop = (event) => {
    event.preventDefault();
    file.value = event.dataTransfer.files[0];
    uploadFile();
};

const onDragOver = (event) => {
    event.preventDefault();
};

const triggerFileInput = () => {
    if (fileInput.value) {
        fileInput.value.click(); // программно вызвать клик по элементу input
    }
};

const uploadFile = async () => {
    if (file.value) {
        const formData = new FormData();
        formData.append('file', file.value);
        formData.append('filename', fileName.value || file.value.name);

        try {
            const url = isEditing.value ? `/files/edit/${fileId.value}` : '/files/upload';
            await axios.post(url, formData, {
                headers: {'Content-Type': 'multipart/form-data'},
                onUploadProgress: (progressEvent) => {
                    uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                }
            });

            successMessage.value = isEditing.value
                ? "File updated successfully! It will be visible in your file list."
                : "File uploaded successfully! It will be visible in your file list.";
            file.value = null;
            fileName.value = '';
        } catch (error) {
            console.error('Failed to upload file', error);
            errorMessage.value = 'Failed to upload file: Max size 8MB, filename max 110 characters';
        } finally {
            if (uploadProgress.value < 100) {
                uploadProgress.value = 100;
            }
            setTimeout(() => {
                uploadProgress.value = null;
            }, 3000);
        }
    }
};
</script>

<style scoped>
.drop-zone {
    min-height: 200px;
    background-color: #f8f9fa;
    border: 2px dashed #ced4da;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.drop-zone svg {
    opacity: 0.8;
}

.drop-zone-icon {
    margin-bottom: 10px; /* Отступ между иконкой и текстом */
    fill: none;
    stroke: #8e8c84;
}

.drop-zone:hover {
    background-color: #e9ecef;
}

.progress-bar {
    transition: width 0.2s;
}

.progress-text {
    color: #f8f8f8;
}
</style>
