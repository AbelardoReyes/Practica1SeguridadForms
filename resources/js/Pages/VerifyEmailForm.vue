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
    status: false,
    error: {
        type: Object,
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

const form = reactive({
    code_phone: "",
    id: PROPS.user.id,
});
function submit() {
    router.post(PROPS.url, form);
}
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />
        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>
        <h4 style="color: white">
            Recibiras un mensaje a tu whatsApp con un codigo, el cual debes ingresar aqui
        </h4>
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="code_phone" value="Codigo" />

                <TextInput
                    id="code_phone"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.code_phone"
                    required
                    autofocus
                    max="9999"
                    autocomplete="code_phone"
                />
            </div>
            <div v-if="status">
                <TextInput
                    id="id"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.id"
                    autocomplete="id"
                />
            </div>
            <p v-if="PROPS.errors" style="color: brown">
                {{ PROPS.errors[0] }}
            </p>
            <div class="flex items-center justify-end mt-4">
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    >Enviar
                </PrimaryButton>
            </div>
        </form>
        <!--Show Error-->
    </GuestLayout>
</template>
