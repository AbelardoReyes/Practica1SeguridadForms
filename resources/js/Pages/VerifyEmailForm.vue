<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";

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
    user: {
        type: Object,
    },
    url: {
        type: String,
    },
});

const form = useForm({
    code_phone: "",
    remember: false,
});

const submit = () => {
    form.get(url, {
        onFinish: () => form.reset("code_phone"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />
{{ user }}
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
                    v-model="form.code_phone"
                    required
                    autofocus
                    autocomplete="username"
                />

                <div v-if="error" class="mb-4 font-medium text-sm text-red-600">
                    {{ error.code_phone }}
                </div>
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
        <!--Show Error-->
        <div v-if="errors" class="mb-4 font-medium text-sm text-red-600">
            {{ errors }}
        </div>
    </GuestLayout>
</template>
