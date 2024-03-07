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
});

const FORM = reactive({
    code_phone: "",
    id: PROPS.user.id,
});
function submit() {
    router.post(PROPS.url, FORM);
}
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />
        <h4 style="color: white">
            Recibiras un correo con un codigo, el cual debes
            ingresar aqui
        </h4>
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="code_phone" value="Codigo" />

                <TextInput
                    id="code_phone"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="FORM.code_phone"
                    autofocus
                    required
                    maxlength="4"
                    autocomplete="code_phone"
                />
            </div>
            <div v-if="status">
                <TextInput
                    id="id"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="FORM.id"
                    required
                    autocomplete="id"
                />
            </div>
            <div v-if="errors" class="mb-4 font-medium text-sm text-red-600">
                {{ errors.code_phone }}
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
        <!--Show Error-->
    </GuestLayout>
</template>
