<template>
    <div class="container">

        <main>
            <div class="app">
                <PrimaryHeader></PrimaryHeader>
                <router-view/>
            </div>
            <div class="footer"></div>
        </main>
    </div>


</template>

<script>
import {defineComponent} from 'vue';
import PrimaryHeader from "./components/PrimaryHeader.vue";

export default defineComponent({
    components: {
        PrimaryHeader
    }
});
</script>

<style>
body {
    background: url("src/background.jpg") fixed;
    background-size: cover;
}

.app {
    background-color: #f8f8f8;
    width: 960px;
    max-width: 960px;
    border-radius: 5px;
    box-shadow: 0 0 4px rgba(0, 0, 0, 0.15);

    margin: 50px auto 10px;
    padding: 0 30px 5px;
}

.footer {
    margin: 0 auto 50px;
}
</style>
