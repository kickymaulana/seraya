<script setup lang="ts">
import SimpleMasterForm from '@/components/SimpleMasterForm.vue'
interface Position { id: number; name: string; is_active: boolean }
defineProps<{ position: Position }>()
</script>
<template><SimpleMasterForm :item="position" resource="positions" title="Jabatan" /></template>
