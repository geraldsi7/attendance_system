<script setup>
import { computed } from 'vue';

const props = defineProps(['active']);

const classes = computed(() =>
  props.active
    ? 'text-purple-600 hover:text-purple-600 border-purple-600 transition duration-150 ease-in-out'
    : 'text-gray-500 hover:text-gray-600 border-gray-100 hover:border-gray-300 transition duration-150 ease-in-out'
);
</script>

<template>
    <li class="me-2">
        <button class="inline-block p-4 border-b-2 rounded-t-lg" :class="classes"
            id="dashboard-styled-tab" data-tabs-target="#styled-dashboard" type="button" role="tab"
            aria-controls="dashboard" aria-selected="false">
            <slot />
        </button>
    </li>
</template>
