<template>
    <div class="list">
        <div class="mb-3">
            <input v-model="searchQuery" @input="updateQueryString" type="text" class="form-control"
                   placeholder="Search by file name">
        </div>

        <ul v-if="filteredFiles.length" class="list-group">
            <li v-for="file in filteredFiles" :key="file.id"
                class="list-group-item d-flex align-items-center justify-content-between">
                <div class="me-3 d-flex align-items-center">
                    <template v-if="isImage(file)">
                        <img :src="getFileUrl(file.path)" :alt="file.filename" class="thumbnail"/>
                    </template>
                    <template v-else>
                        <div class="thumbnail extension-thumbnail">
                            <p>FILE</p>
                        </div>
                    </template>
                    <div class="ms-3">
                        <div>
                            <p class="m-0">{{ getFileBaseName(file.filename) }} <span
                                class="badge text-bg-success">{{ getFileExtension(file.filename) }}</span></p>
                            <div>
                                <p class="d-inline">
                                    <router-link
                                        :to="`/files/edit/${file.id}`"
                                        class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">
                                        Edit
                                    </router-link>
                                </p>
                                <p class="mt-1 d-inline ms-2">
                                    <a href="#" @click.prevent="showConfirmationModal(file.id)"
                                       class="link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">
                                        Remove
                                    </a>
                                </p>
                                <p class="mt-1 d-inline ms-2"><a :href="getFileUrl(file.path)" :download="file.filename"
                                                                 class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Download</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <span class="badge text-bg-secondary">{{ formatFileSize(file.size) }}</span>
                </div>
            </li>
        </ul>
        <p v-else class="text-muted">No files yet.</p>

        <nav v-if="totalPages > 1" aria-label="File list pagination" class="mt-3 d-flex justify-content-center">
            <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <button class="page-link" @click="fetchFiles(currentPage - 1)" :disabled="currentPage === 1">
                        Previous
                    </button>
                </li>
                <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }">
                    <button class="page-link" @click="fetchFiles(page)">{{ page }}</button>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <button class="page-link" @click="fetchFiles(currentPage + 1)"
                            :disabled="currentPage === totalPages">Next
                    </button>
                </li>
            </ul>
        </nav>

        <p class="text-center">
            Displaying {{ filteredFiles.length }} of {{ totalFiles }} total files.
        </p>

        <ConfirmationModal
            title="Confirm File Removal"
            message="Are you sure you want to remove this file?"
            @confirm="removeFile(selectedFileId)"
        />
    </div>
</template>

<script setup>
import {ref, computed, onMounted} from 'vue';
import axios from 'axios';
import {useRoute, useRouter} from 'vue-router';
import ConfirmationModal from './ConfirmationModal.vue';

const files = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const totalFiles = ref(0);
const searchQuery = ref('');
const selectedFileId = ref(null); // выбранный файл для удаления
const route = useRoute();
const router = useRouter();

// вычисляемое свойство для фильтрации файлов на основе запроса поиска
const filteredFiles = computed(() => {
    if (!searchQuery.value) {
        return files.value;
    }
    const query = searchQuery.value.toLowerCase();
    return files.value.filter(file => file.filename.toLowerCase().includes(query));
});

// вычисляемое свойство для получения количества файлов на текущей странице
const filesOnPage = computed(() => filteredFiles.value.length);

// проверка на изображение
const isImage = (file) => {
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    const fileExtension = file.filename.split('.').pop().toLowerCase();
    return imageExtensions.includes(fileExtension);
};

// получение полного url для файла
const getFileUrl = (filePath) => {
    return `/storage/${filePath.replace('public/', '')}`;
};

// получение основной части имени файла
const getFileBaseName = (filename) => {
    return filename.split('.').slice(0, -1).join('.');
};

// получение расширения файла
const getFileExtension = (filename) => {
    return filename.split('.').pop().toLowerCase();
};

// форматирование размера файла
const formatFileSize = (size) => {
    return `${(size / 1048576).toFixed(2)} MB`;
};

// обновление query string
const updateQueryString = () => {
    router.push({query: {...route.query, search: searchQuery.value}});
    fetchFiles(1); // файлы с первой страницы
};

// загрузка файлов с учетом параметра поиска
const fetchFiles = async (page) => {
    if (page < 1 || page > totalPages.value) return;

    try {
        const response = await axios.get(`/files/list?page=${page}&search=${searchQuery.value}`);
        files.value = response.data.files;
        currentPage.value = response.data.currentPage;
        totalPages.value = response.data.lastPage;
        totalFiles.value = response.data.totalFiles;
    } catch (error) {
        console.error('Ошибка при загрузке файлов:', error);
    }
};

const showConfirmationModal = (fileId) => {
    selectedFileId.value = fileId;
    const modalElement = document.getElementById('confirmationModal');
    const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    modal.show();
};

const removeFile = async (fileId) => {
    if (!fileId) return;
    try {
        await axios.post('/file/remove', {file_id: fileId});
        fetchFiles(currentPage.value); // перезагрузка файлов после удаления
    } catch (error) {
        console.error('Ошибка при удалении файла:', error);
    }
};

// загрузка файлов при монтировании компонента
onMounted(() => {
    searchQuery.value = route.query.search || '';
    fetchFiles(currentPage.value);
});
</script>

<style>


.thumbnail {
    min-width: 100px;
    max-width: 100px;
    min-height: 100px;
    max-height: 100px;
    object-fit: cover;
    border-radius: 5px;
    border: 1px solid #dcdcdc;
}

.extension-thumbnail {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #efefef;
    min-width: 100px;
    max-width: 100px;
    min-height: 100px;
    max-height: 100px;
    font-size: 14px;
    color: #343a40;
    font-weight: bold;
    text-align: center;
}

.extension-thumbnail p {
    margin: 0;
    text-transform: uppercase;
    line-height: 1.2;
}

.list-group-item {
    min-height: 120px;
}
</style>
