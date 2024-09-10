<script setup>

</script>

<template>
    <header class="d-flex justify-content-center py-3">
        <ul class="nav nav-pills gap-2">
            <li class="nav-item"><router-link to="/" class="nav-link" aria-current="page">Upload file</router-link></li>
            <li class="nav-item">
                <router-link
                    to="/files"
                    class="nav-link"
                    :class="{ active: $route.path.startsWith('/files') }">Your files</router-link>
            </li>
        </ul>
    </header>
</template>

<style scoped>

</style>
