<script setup lang="ts">
import Form from './Form.vue'
interface Department { id: number; name: string; description: string | null; is_active: boolean }
defineProps<{ department: Department }>()
</script>
<template><Form :department="department" /></template>
