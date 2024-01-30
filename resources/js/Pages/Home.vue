<script setup>
import NavLink from "@/Components/NavLink.vue";
import { defineProps } from "vue";
import { reactive } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
const PROPS = defineProps({
    error: {
        type: Object,
    },
    user: {
        type: Object,
    },
});
const form = reactive({
    user: PROPS.user,
});
function logout() {
    router.get(route("logout"), form);
}
const getYouTubeEmbedUrl = (videoCode) => {
    // Construye la URL de la incrustación de YouTube con el código del video
    return `https://www.youtube.com/embed/${videoCode}`;
};
</script>
<template>
    <div class="nav">
        <h1>Bienvenido {{ PROPS.user.name }}</h1>
        <form @submit.prevent="logout">
            <button type="submit" class="btnLogout">Cerrar Sesion</button>
        </form>
    </div>
    <div>
        <div class="dashboard role-1" v-if="PROPS.user.role_id == 1">
            <h2>Bienvenido al Panel de Control</h2>
            <p>¡Hola, Administrador!</p>

            <a href="#" class="admin-button">Panel de Administrador</a>
        </div>

        <div class="dashboard role-2" v-if="PROPS.user.role_id == 2">
            <h2>Bienvenido al Panel de Control</h2>
            <p>¡Hola, Usuario!</p>

            <a href="#" class="user-button">Panel de Usuario</a>
        </div>
        <div class="mt-4" v-if="PROPS.user.role_id == 1">
            <iframe
            style="margin: auto;"
                width="560"
                on-play="onPlay"
                height="315"
                src="https://www.youtube.com/embed/GtL1huin9EE?si=Vyq8GQBZlHvxWyS8"
                title="YouTube video player"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
            ></iframe>
        </div>
    </div>
</template>

<style scoped>
.nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 1rem;
    background-color: cadetblue;
    height: 4rem;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
h1 {
    color: black;
    font-size: 30px;
}
body {
    margin: 0;
    padding: 0;
    font-family: sans-serif;
}
.btnLogout {
    background-color: darkcyan;
    border: none;
    border-radius: 10px;
    color: white;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
}
.btnLogout:hover {
    background-color: darkturquoise;
    color: black;
}

.dashboard {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.admin-button,
.user-button {
    display: none;
    padding: 10px;
    background-color: #3498db;
    color: #fff;
    text-decoration: none;
    margin-right: 10px;
}

.role-1 .admin-button {
    display: inline-block;
}

.role-2 .user-button {
    display: inline-block;
}
</style>
