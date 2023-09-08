<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/inertia-vue3";

const props = defineProps(["href", "active", "icon"]);

const classes = computed(() => (props.active ? "active" : ""));
</script>

<template>
  <li :class="classes">
    <a :href="href">
      <i :class="icon"></i>
      <p><slot /></p>
    </a>
  </li>
</template>
