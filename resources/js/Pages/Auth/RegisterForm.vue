<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";

const PROPS = defineProps({
    errors: {
        type: Object,
    },
    error: {
        type: Object,
    },
    widgetId1: {
        type: String,
    },
});
function loadRecaptcha() {
    const script = document.createElement("script");
    script.src = "https://www.google.com/recaptcha/api.js?render=explicit";
    script.async = true;
    document.head.appendChild(script);

    script.onload = () => {
        // Configuración de reCAPTCHA
        window.grecaptcha.ready(() => {
            PROPS.widgetId1 = window.grecaptcha.render("contenedor-recaptcha", {
                sitekey: "6Lelul4pAAAAADN78UT9yavMvEfNwZm-kS0jvzrB",
                callback: (response) => {
                    form.gRecaptchaResponse = response;
                },
            });
        });
    };
}
onMounted(() => {
    loadRecaptcha();
});
const form = useForm({
    name: "",
    last_name: "",
    phone: "",
    email: "",
    gRecaptchaResponse: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    if (form.gRecaptchaResponse == "") {
        alert("Por favor Complete el Captcha");
        window.grecaptcha.reset(PROPS.widgetId1);
        return;
    }
    if (!router.post(route("register"), form)) {
        window.grecaptcha.reset(PROPS.widgetId1);
    }
};
</script>
<template>
    <GuestLayout>
        <Head title="Register" />
        <form @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-4">
                <div class="mt-1">
                    <InputLabel for="name" value="Nombres" />

                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        maxlength="100"
                    />

                    <p
                        class="mb-4 font-medium text-sm text-red-500"
                        v-if="errors"
                    >
                        {{ errors.name }}
                    </p>
                </div>
                <div class="mt-1">
                    <InputLabel for="last_name" value="Apellidos" />

                    <TextInput
                        id="last_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.last_name"
                        autofocus
                        maxlength="100"
                        required
                        autocomplete="family-name"
                    />

                    <p
                        class="mb-4 font-medium text-sm text-red-500"
                        v-if="errors"
                    >
                        {{ errors.last_name }}
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4">
                <div class="mt-1">
                    <InputLabel for="email" value="Correo" />

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                    />

                    <p
                        class="mb-4 font-medium text-sm text-red-500"
                        v-if="errors"
                    >
                        {{ errors.email }}
                    </p>
                </div>

            <div class="mt-1">
                <InputLabel for="password" value="Contraseña" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <p class="mb-4 font-medium text-sm text-red-500" v-if="errors">
                    {{ errors.password }}
                </p>
            </div>

            <div class="mt-1">
                <InputLabel
                    for="password_confirmation"
                    value="Confirma la contraseña"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <p class="mb-4 font-medium text-sm text-red-500" v-if="errors">
                    {{ errors.password_confirmation }}
                </p>
            </div>
        </div>

            <div
                style="margin-left: 13%; margin-top: 5%"
                id="contenedor-recaptcha"
                class="g-recaptcha"
                data-sitekey="6Lelul4pAAAAADN78UT9yavMvEfNwZm-kS0jvzrB"
            ></div>
            <p
                style="margin-left: 30%; margin-top: 2%"
                class="mb-4 font-medium text-sm text-red-500"
                v-if="errors"
            >
                {{ errors.gRecaptchaResponse }}
            </p>

            <div class="flex items-center justify-end mt-4">
                <!-- <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Already registered?
                </Link> -->

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
