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

const PROPS = defineProps({
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
onMounted(() => {});
const form = reactive({
    email: "",
});

function submit() {
    if (form.gRecaptchaResponse == "") {
        alert("Por favor Complete el Captcha");
        window.grecaptcha.reset(PROPS.widgetId1);
        return;
    }
    if (!router.post(route("activeUser"), form)) {
        window.grecaptcha.reset(PROPS.widgetId1);
    }
}
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>
        <p style="color: white">Activar cuenta</p>
        <form @submit.prevent="submit">
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
            <div
                style="margin-left: 12%; margin-top: 5%"
                id="contenedor-recaptcha"
                class="g-recaptcha"
                data-sitekey="6Lelul4pAAAAADN78UT9yavMvEfNwZm-kS0jvzrB"
            ></div>
            <p
                class="mb-4 font-medium text-sm text-red-600"
                style="margin-left: 30%; margin-top: 2%"
                v-if="errors"
            >
                {{ errors.gRecaptchaResponse }}
            </p>
            <PrimaryButton
                class="ms-4"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Enviar Codigo
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
