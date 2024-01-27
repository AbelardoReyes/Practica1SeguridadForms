<script setup>
import { computed } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    user: Object,
});
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />
        <h1>
            Te llegara un sms con un codigo, el cual debes ingresar para
            terminar de activar tu cuenta
        </h1>
        {{ user }}
    </GuestLayout>
</template>
