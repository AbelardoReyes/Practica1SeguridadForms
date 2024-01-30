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
const script = document.createElement("script");
script.src = "https://www.google.com/recaptcha/api.js?render=explicit";
script.async = true;
document.head.appendChild(script);

script.onload = () => {
    // Configuración de reCAPTCHA
    window.grecaptcha.ready(() => {
        window.grecaptcha.render("contenedor-recaptcha", {
            sitekey: "6Lelul4pAAAAADN78UT9yavMvEfNwZm-kS0jvzrB",
            callback: (response) => {
                form.gRecaptchaResponse = response;
            },
        });
    });
};
const form = reactive({
    email: "",
    password: "",
    gRecaptchaResponse: "",
    remember: false,
});

function submit() {
    router.post(route("login"), form);
}
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form
            @submit.prevent="submit"
            action="javascript:alert(grecaptcha.getResponse(widgetId1));"
        >
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    autofocus
                    required
                    maxlength="255"
                    autocomplete="username"
                />

                <div
                    v-if="errors"
                    class="mb-4 font-medium text-sm text-red-600"
                >
                    {{ errors.email }}
                </div>
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    maxlength="200"
                    required
                    autocomplete="current-password"
                />

                <div
                    v-if="errors"
                    class="mb-4 font-medium text-sm text-red-600"
                >
                    {{ errors.password }}
                </div>
            </div>
            <div
                style="margin-left: 13%; margin-top: 5%"
                id="contenedor-recaptcha"
                class="g-recaptcha"
                data-sitekey="6Lelul4pAAAAADN78UT9yavMvEfNwZm-kS0jvzrB"
            ></div>
            <p class="mb-4 font-medium text-sm text-red-600" style="margin-left: 30%; margin-top: 2%;" v-if="errors" >
                {{ errors.gRecaptchaResponse }}
            </p>

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
                    Iniciar Sesión
                </PrimaryButton>
            </div>
        </form>
        <p class="mb-4 font-medium text-sm text-red-500" v-if="errors">{{ errors.PDO }}</p>
        <p class="mb-4 font-medium text-sm text-red-500" v-if="error">{{ error.QueryE }}</p>
        <p class="mb-4 font-medium text-sm text-red-500" v-if="error">{{ error.ValidationE }}</p>
        <p class="mb-4 font-medium text-sm text-red-500" v-if="error">{{ error.Exception }}</p>
    </GuestLayout>
</template>
