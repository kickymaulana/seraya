<script setup lang="ts">
import Form from './Form.vue'

defineProps<{ itemType: { id: number; name: string; category_id: number; unit: string; is_active: boolean }; categories: { id: number; name: string }[] }>()
</script>

<template><Form :item-type="itemType" :categories="categories" /></template>
