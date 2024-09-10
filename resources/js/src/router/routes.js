import Files from "../pages/FilesPage.vue";
import FileHandler from "../components/FileHandler.vue";

const routes = [
    { path: '/', component: FileHandler },
    { path: '/files', component: Files },
    { path: '/files/edit/:id', name: 'file-edit', component: FileHandler },
]


export default routes;
