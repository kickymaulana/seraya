<script setup lang="ts">
import Form from './Form.vue'

defineProps<{ categories: { id: number; name: string }[] }>()
</script>

<template><Form :categories="categories" /></template>
