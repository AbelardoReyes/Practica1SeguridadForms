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
</script>
<template>
    <div class="nav">
        <h1>Bienvenido {{ PROPS.user.name }}</h1>
        <form @submit.prevent="logout">
            <button type="submit" class="btnLogout">Cerrar Sesion</button>
        </form>
    </div>
    <div></div>
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
    background-color:darkcyan;
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
</style>
