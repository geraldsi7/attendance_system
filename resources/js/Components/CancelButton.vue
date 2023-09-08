<script>
export default {
    emits: ['close'],
    props: {
        type: {
            type: String,
            default: 'submit',
        }
    }
}
</script>

<template>
    <button class="btn btn-lg btn-muted" @click="$emit('close')">
        <slot />
    </button>
</template>
