<script>
const PROPS = defineProps({
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
</script>
<template>
    <div>
        <h1>Home</h1>
        <p v-if="error">
            {{ PROPS.error }}
        </p>
    </div>
</template>
