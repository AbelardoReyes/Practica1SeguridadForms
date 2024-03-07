<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";
import { defineProps } from "vue";
import { reactive } from "vue";

const PROPS = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    user: {
        type: Object,
    },
    url: {
        type: String,
    },
    errors: {
        type: Object,
    },
    password: {
        type: String,
    },
});
const FORM = reactive({
    code_phone: "",
    email: PROPS.user.email,
    password: "",
    id: PROPS.user.id,
});
function submit() {
    router.post(PROPS.url, FORM);
}
</script>

<template>
    {{ PROPS.password }}
    <GuestLayout>
        <Head title="Log in" />
        <h4 style="color: white">
            Recibiras un correo con un codigo, el cual debes
            ingresar aqui para iniciar Sesion
        </h4>
        <br />
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="code_phone" value="Codigo" />

                <TextInput
                    id="code_phone"
                    type="text"
                    maxlength="4"
                    class="mt-1 block w-full"
                    v-model="FORM.code_phone"
                    autofocus
                    autocomplete="code_phone"
                />
            </div>
            <div v-if="errors" class="mb-4 font-medium text-sm text-red-600">
                {{ errors.code_phone }}
            </div>
            <div hidden="true">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="FORM.email"
                    autofocus
                    autocomplete="email"
                />

                <div v-if="error" class="mb-4 font-medium text-sm text-red-600">
                    {{ error.email }}
                </div>
            </div>
            <div>
                <InputLabel
                    for="password"
                    value="Ingresa tu contraseña nuevamente"
                />

                <TextInput
                    id="password"
                    type="password"
                    maxlength="200"
                    class="mt-1 block w-full"
                    v-model="FORM.password"
                    autocomplete="current-password"
                />

                <div
                    v-if="errors"
                    class="mb-4 font-medium text-sm text-red-600"
                >
                    {{ errors.password }}
                </div>
            </div>
            <div v-if="status">
                <TextInput
                    id="id"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="FORM.id"
                    autofocus
                    autocomplete="id"
                />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': FORM.processing }"
                    :disabled="FORM.processing"
                    >Enviar
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
