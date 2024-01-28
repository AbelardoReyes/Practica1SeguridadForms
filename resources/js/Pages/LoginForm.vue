<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { onMounted } from "vue";
import { reactive } from "vue";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    error: {
        type: Object,
    },
    errors: {
        type: Object,
    },
});

onMounted(() => {});
const form = reactive({
    email: "",
    password: "",
    gRecaptchaResponse: "",
    remember: false,
});

function submit() {
    router.post(route("login"), form);
}
function validCaptcha() {
    router.post(route("login"), form);
}

const script = document.createElement("script");
script.src = "https://www.google.com/recaptcha/api.js?render=explicit";
script.async = true;
document.head.appendChild(script);

script.onload = () => {
    // Configuración de reCAPTCHA
    window.grecaptcha.ready(() => {
        window.grecaptcha.render("contenedor-recaptcha", {
            sitekey: "6LdQ7F0pAAAAAMb2vICxr89p1srjijesx1HKl73A",
            callback: (response) => {
                form.gRecaptchaResponse = response;
            },
        });
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <div v-if="error" class="mb-4 font-medium text-sm text-red-600">
                    {{ error.email }}
                </div>
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <div v-if="error" class="mb-4 font-medium text-sm text-red-600">
                    {{ error.password }}
                </div>
            </div>
            <div
                id="contenedor-recaptcha"
                class="g-recaptcha"
                data-sitekey="6Lelul4pAAAAADN78UT9yavMvEfNwZm-kS0jvzrB"
            ></div>
            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('registerView')"
                    class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    ¿No tienes cuenta? Registrate
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
        <form @submit.prevent="validCaptcha">
            <br />
            <input type="submit" value="Submit" />
        </form>
        <p v-if="errors">{{ errors }}</p>
    </GuestLayout>
</template>
